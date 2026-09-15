<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\UploadMarksRequest;
use App\Models\ClassSubjectTeacher;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Student;
use App\Support\GradeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class MarksController extends Controller
{
    /**
     * Landing page: pick which of your class+subject assignments, and which
     * exam window for that class, you want to upload/review marks for.
     */
    public function index(Request $request)
    {
        $teacher = $request->user();

        $assignments = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->with(['schoolClass', 'subject'])
            ->get();

        $classIds = $assignments->pluck('school_class_id')->unique();

        $exams = Exam::whereIn('school_class_id', $classIds)
            ->with('schoolClass')
            ->latest()
            ->get();

        return view('teacher.marks.index', compact('assignments', 'exams'));
    }

    /**
     * Only students actually enrolled in this subject (see App\Support\SubjectEnrollment) -
     * for a core subject that's effectively everyone active in the class, for an
     * elective it's only whoever the teacher deliberately enrolled.
     */
    protected function enrolledStudents(ClassSubjectTeacher $assignment)
    {
        return $assignment->schoolClass->students()
            ->where('status', 'active')
            ->whereHas('subjects', fn ($q) => $q->where('subjects.id', $assignment->subject_id))
            ->orderBy('name')
            ->get();
    }

    /**
     * Upload form + current marks table for one assignment + exam combination.
     */
    public function show(Request $request, ClassSubjectTeacher $assignment, Exam $exam)
    {
        $this->authorizeAssignment($request, $assignment, $exam);

        $students = $this->enrolledStudents($assignment);

        $existingMarks = Mark::where('exam_id', $exam->id)
            ->where('subject_id', $assignment->subject_id)
            ->get()
            ->keyBy('student_id');

        return view('teacher.marks.show', compact('assignment', 'exam', 'students', 'existingMarks'));
    }

    /**
     * Direct single-student edit - an alternative to the Excel upload, for
     * fixing one score without re-uploading the whole sheet.
     */
    public function editMark(Request $request, ClassSubjectTeacher $assignment, Exam $exam, Student $student)
    {
        $this->authorizeAssignment($request, $assignment, $exam);
        $this->authorizeStudentEnrolled($assignment, $student);

        $mark = Mark::where('exam_id', $exam->id)
            ->where('subject_id', $assignment->subject_id)
            ->where('student_id', $student->id)
            ->first();

        return view('teacher.marks.edit', compact('assignment', 'exam', 'student', 'mark'));
    }

    public function updateMark(Request $request, ClassSubjectTeacher $assignment, Exam $exam, Student $student)
    {
        $this->authorizeAssignment($request, $assignment, $exam);
        $this->authorizeStudentEnrolled($assignment, $student);

        if (in_array($exam->status, ['approved', 'submitted'], true)) {
            return back()->withErrors(['score' => 'This exam is currently '.$exam->status.' and cannot be edited right now.']);
        }

        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        Mark::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'subject_id' => $assignment->subject_id,
            ],
            [
                'entered_by' => $request->user()->id,
                'score' => $validated['score'],
                'grade' => GradeCalculator::forScore($validated['score']),
                'remarks' => $validated['remarks'] ?? null,
            ]
        );

        return redirect()->route('teacher.marks.show', [$assignment, $exam])
            ->with('success', "{$student->name}'s mark has been updated.");
    }

    protected function authorizeStudentEnrolled(ClassSubjectTeacher $assignment, Student $student): void
    {
        abort_unless($student->school_class_id === $assignment->school_class_id, 404);
        abort_unless(
            $student->subjects()->where('subjects.id', $assignment->subject_id)->exists(),
            404,
            'This student isn\'t enrolled in this subject.'
        );
    }

    /**
     * Downloads a pre-filled .xlsx template: one row per student actually
     * enrolled in this subject, admission number and name locked in, score
     * column left blank. A student who doesn't take this subject never even
     * appears here - there's nothing for a teacher to mark "missing" for them.
     */
    public function template(Request $request, ClassSubjectTeacher $assignment, Exam $exam)
    {
        $this->authorizeAssignment($request, $assignment, $exam);

        $students = $this->enrolledStudents($assignment);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Marks');

        $sheet->fromArray(['Admission No.', 'Student Name', 'Score (out of 100)'], null, 'A1');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        $row = 2;
        foreach ($students as $student) {
            $sheet->setCellValue("A{$row}", $student->admission_no);
            $sheet->setCellValue("B{$row}", $student->name);
            $row++;
        }

        foreach (['A', 'B', 'C'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = str($assignment->subject->name.'-'.$assignment->schoolClass->displayName().'-'.$exam->name)
            ->slug()
            ->append('.xlsx');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, (string) $filename);
    }

    /**
     * Processes an uploaded .xlsx file: matches each row's admission number
     * against students in this class, and upserts a Mark row per match - but
     * only for students actually enrolled in this subject. A row for a real
     * class member who simply doesn't take this subject is reported with a
     * specific "not enrolled" message rather than a generic "not found",
     * and no mark is ever created for them - so a parent never sees a
     * "missing" mark for a subject their child doesn't take.
     */
    public function store(UploadMarksRequest $request, ClassSubjectTeacher $assignment, Exam $exam)
    {
        $this->authorizeAssignment($request, $assignment, $exam);

        if (in_array($exam->status, ['approved', 'submitted'], true)) {
            return back()->withErrors(['file' => 'This exam is currently '.$exam->status.' and cannot be edited right now.']);
        }

        $allClassStudentsByAdmission = $assignment->schoolClass->students()
            ->where('status', 'active')
            ->get()
            ->keyBy('admission_no');

        $enrolledStudentIds = $this->enrolledStudents($assignment)->pluck('id')->flip();

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        $imported = 0;
        $skipped = [];

        DB::transaction(function () use ($sheet, $allClassStudentsByAdmission, $enrolledStudentIds, $assignment, $exam, $request, &$imported, &$skipped) {
            foreach ($sheet->getRowIterator(2) as $row) {
                $admissionNo = trim((string) $sheet->getCell('A'.$row->getRowIndex())->getValue());
                $score = $sheet->getCell('C'.$row->getRowIndex())->getValue();

                if ($admissionNo === '') {
                    continue; // blank row, ignore
                }

                $student = $allClassStudentsByAdmission->get($admissionNo);

                if (! $student) {
                    $skipped[] = "Row {$row->getRowIndex()}: admission number \"{$admissionNo}\" not found in this class.";
                    continue;
                }

                if (! isset($enrolledStudentIds[$student->id])) {
                    $skipped[] = "Row {$row->getRowIndex()}: {$student->name} isn't enrolled in {$assignment->subject->name} - skipped. Manage enrollment from the Elective Enrollment page if this is wrong.";
                    continue;
                }

                if ($score === null || $score === '') {
                    continue; // no score entered for this student yet, skip rather than overwrite with blank
                }

                if (! is_numeric($score) || $score < 0 || $score > 100) {
                    $skipped[] = "Row {$row->getRowIndex()}: score \"{$score}\" for {$student->name} is not a valid number between 0 and 100.";
                    continue;
                }

                Mark::updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                        'subject_id' => $assignment->subject_id,
                    ],
                    [
                        'entered_by' => $request->user()->id,
                        'score' => $score,
                        'grade' => GradeCalculator::forScore($score),
                    ]
                );

                $imported++;
            }
        });

        $message = "{$imported} mark(s) saved.";

        return back()
            ->with('success', $message)
            ->with('skipped_rows', $skipped);
    }

    protected function authorizeAssignment(Request $request, ClassSubjectTeacher $assignment, Exam $exam): void
    {
        abort_unless($assignment->teacher_id === $request->user()->id, 404);
        abort_unless($exam->school_class_id === $assignment->school_class_id, 404);
        abort_unless($exam->school_id === $request->user()->school_id, 404);
    }
}
