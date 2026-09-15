<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\BulkImportStudentsRequest;
use App\Http\Requests\Teacher\StoreStudentRequest;
use App\Http\Requests\Teacher\UpdateStudentRequest;
use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Support\SubjectEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $teacherId = $request->user()->id;

        $classTeacherOfIds = SchoolClass::where('class_teacher_id', $teacherId)->pluck('id')->all();
        $assignedClassIds = ClassSubjectTeacher::where('teacher_id', $teacherId)->pluck('school_class_id')->unique()->all();

        $query = Student::where('school_id', $schoolId)
            ->with('schoolClass')
            ->when($request->filled('class_id'), fn ($q) => $q->where('school_class_id', $request->integer('class_id')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($w) use ($term) {
                    $w->where('name', 'like', "%{$term}%")->orWhere('admission_no', 'like', "%{$term}%");
                });
            });

        // Students the teacher actually teaches come first (their own class-teacher
        // class ranks above classes they merely teach a subject in), then everyone
        // else - each group ordered by admission number, not by name.
        if ($classTeacherOfIds || $assignedClassIds) {
            $classTeacherPlaceholders = $classTeacherOfIds ? implode(',', array_fill(0, count($classTeacherOfIds), '?')) : '-1';
            $assignedPlaceholders = $assignedClassIds ? implode(',', array_fill(0, count($assignedClassIds), '?')) : '-1';
            $bindings = array_merge($classTeacherOfIds, $assignedClassIds);

            $query->orderByRaw(
                "CASE WHEN school_class_id IN ({$classTeacherPlaceholders}) THEN 0 WHEN school_class_id IN ({$assignedPlaceholders}) THEN 1 ELSE 2 END",
                $bindings
            );
        }

        $students = $query->orderBy('admission_no')
            ->paginate(20)
            ->withQueryString();

        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();

        return view('teacher.students.index', compact('students', 'classes'));
    }

    public function create(Request $request)
    {
        $classes = SchoolClass::where('school_id', $request->user()->school_id)->orderBy('name')->get();

        return view('teacher.students.create', compact('classes'));
    }

    public function store(StoreStudentRequest $request)
    {
        $student = Student::create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        SubjectEnrollment::syncCoreSubjectsForStudent($student);

        return redirect()->route('teacher.students.index')->with('success', 'Student added.');
    }

    public function edit(Request $request, Student $student)
    {
        $this->authorizeSchool($request, $student);

        $classes = SchoolClass::where('school_id', $request->user()->school_id)->orderBy('name')->get();

        return view('teacher.students.edit', compact('student', 'classes'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $this->authorizeSchool($request, $student);

        $classChanged = (int) $request->validated('school_class_id') !== $student->school_class_id;

        $student->update($request->validated());

        if ($classChanged) {
            SubjectEnrollment::syncCoreSubjectsForStudent($student);
        }

        return redirect()->route('teacher.students.index')->with('success', 'Student details updated.');
    }

    protected function authorizeSchool(Request $request, Student $student): void
    {
        abort_unless($student->school_id === $request->user()->school_id, 404);
    }

    /**
     * Bulk registration: pick a class, download a blank template, fill it in
     * for however many students, upload it back. Solves the "class of 90"
     * problem where registering one-by-one isn't realistic.
     */
    public function bulkForm(Request $request)
    {
        $classes = SchoolClass::where('school_id', $request->user()->school_id)->orderBy('name')->get();

        return view('teacher.students.bulk', compact('classes'));
    }

    public function bulkTemplate(Request $request)
    {
        $class = SchoolClass::where('school_id', $request->user()->school_id)
            ->findOrFail($request->integer('class_id'));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Students');

        $headers = ['Admission No.', 'Full Name', 'Gender (male/female, optional)', 'Date of Birth (YYYY-MM-DD, optional)', 'Guardian Phone (optional)'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        // One example row so the format is obvious, easy for a teacher to delete before filling in real data.
        $sheet->fromArray(['ADM-EXAMPLE', 'Jane Wanjiru', 'female', '2015-03-14', '0712345678'], null, 'A2');
        $sheet->getStyle('A2:E2')->getFont()->setItalic(true);

        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = str('student-import-'.$class->displayName())->slug()->append('.xlsx');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, (string) $filename);
    }

    public function bulkImport(BulkImportStudentsRequest $request)
    {
        $schoolId = $request->user()->school_id;

        $class = SchoolClass::where('school_id', $schoolId)->findOrFail($request->validated('school_class_id'));

        $existingAdmissionNos = Student::where('school_id', $schoolId)->pluck('admission_no')->flip();

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        $created = 0;
        $skipped = [];
        $seenInFile = [];

        DB::transaction(function () use ($sheet, $class, $schoolId, $existingAdmissionNos, &$created, &$skipped, &$seenInFile) {
            foreach ($sheet->getRowIterator(2) as $row) {
                $rowIndex = $row->getRowIndex();

                $admissionNo = trim((string) $sheet->getCell('A'.$rowIndex)->getValue());
                $name = trim((string) $sheet->getCell('B'.$rowIndex)->getValue());
                $gender = strtolower(trim((string) $sheet->getCell('C'.$rowIndex)->getValue()));
                $dob = trim((string) $sheet->getCell('D'.$rowIndex)->getValue());
                $guardianPhone = trim((string) $sheet->getCell('E'.$rowIndex)->getValue());

                if ($admissionNo === '' && $name === '') {
                    continue; // blank row
                }

                if ($admissionNo === 'ADM-EXAMPLE') {
                    continue; // the example row wasn't removed - skip it quietly
                }

                if ($admissionNo === '' || $name === '') {
                    $whatWeHave = $name !== '' ? "\"{$name}\"" : ($admissionNo !== '' ? "admission no. \"{$admissionNo}\"" : 'a blank row');
                    $skipped[] = "Row {$rowIndex} ({$whatWeHave}): missing admission number or name.";
                    continue;
                }

                if (isset($existingAdmissionNos[$admissionNo]) || isset($seenInFile[$admissionNo])) {
                    $skipped[] = "Row {$rowIndex}: admission number \"{$admissionNo}\" already exists - skipped.";
                    continue;
                }

                if (! in_array($gender, ['male', 'female', ''], true)) {
                    $skipped[] = "Row {$rowIndex}: gender \"{$gender}\" not recognised (use male, female, or leave blank).";
                    continue;
                }

                $parsedDob = null;
                if ($dob !== '') {
                    try {
                        $parsedDob = \Illuminate\Support\Carbon::parse($dob)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $skipped[] = "Row {$rowIndex}: date of birth \"{$dob}\" isn't a valid date - left blank.";
                    }
                }

                $newStudent = Student::create([
                    'school_id' => $schoolId,
                    'school_class_id' => $class->id,
                    'name' => $name,
                    'admission_no' => $admissionNo,
                    'gender' => $gender ?: null,
                    'date_of_birth' => $parsedDob,
                    'guardian_phone' => $guardianPhone ?: null,
                ]);
                SubjectEnrollment::syncCoreSubjectsForStudent($newStudent);

                $seenInFile[$admissionNo] = true;
                $created++;
            }
        });

        return redirect()->route('teacher.students.index')
            ->with('success', "{$created} student(s) added to {$class->displayName()}.")
            ->with('skipped_rows', $skipped);
    }
}
