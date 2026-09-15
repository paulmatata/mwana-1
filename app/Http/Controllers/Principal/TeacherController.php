<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Principal\BulkImportTeachersRequest;
use App\Http\Requests\Principal\StoreTeacherRequest;
use App\Http\Requests\Principal\UpdateTeacherRequest;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = User::where('school_id', $request->user()->school_id)
            ->where('role', 'teacher')
            ->with('subjectsQualifiedFor')
            ->orderBy('name')
            ->get();

        $subjects = Subject::where('school_id', $request->user()->school_id)->orderBy('name')->get();

        return view('principal.teachers.index', compact('teachers', 'subjects'));
    }

    public function store(StoreTeacherRequest $request)
    {
        $tempPassword = Str::password(12, symbols: false);

        $teacher = User::create([
            'role' => 'teacher',
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => Hash::make($tempPassword),
            'school_id' => $request->user()->school_id,
            'status' => 'active',
        ]);

        $teacher->subjectsQualifiedFor()->sync($request->validated('subject_ids', []));

        return back()
            ->with('success', "Teacher account created for {$teacher->name}.")
            ->with('generated_password', $tempPassword)
            ->with('generated_for', $teacher->name);
    }

    public function edit(Request $request, User $teacher)
    {
        $this->authorizeSchool($request, $teacher);

        $subjects = Subject::where('school_id', $request->user()->school_id)->orderBy('name')->get();
        $qualifiedIds = $teacher->subjectsQualifiedFor->pluck('id');

        return view('principal.teachers.edit', compact('teacher', 'subjects', 'qualifiedIds'));
    }

    public function update(UpdateTeacherRequest $request, User $teacher)
    {
        $this->authorizeSchool($request, $teacher);

        $teacher->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
        ]);

        $teacher->subjectsQualifiedFor()->sync($request->validated('subject_ids', []));

        return redirect()->route('principal.teachers.index')->with('success', "{$teacher->name}'s details updated.");
    }

    public function toggleStatus(Request $request, User $teacher)
    {
        $this->authorizeSchool($request, $teacher);

        $teacher->update(['status' => $teacher->status === 'active' ? 'suspended' : 'active']);

        $message = $teacher->status === 'active'
            ? "{$teacher->name} has been reactivated."
            : "{$teacher->name}'s access has been suspended.";

        return back()->with('success', $message);
    }

    public function resetPassword(Request $request, User $teacher)
    {
        $this->authorizeSchool($request, $teacher);

        $tempPassword = Str::password(12, symbols: false);
        $teacher->update(['password' => Hash::make($tempPassword)]);

        return back()
            ->with('success', "Password reset for {$teacher->name}.")
            ->with('generated_password', $tempPassword)
            ->with('generated_for', $teacher->name);
    }

    protected function authorizeSchool(Request $request, User $teacher): void
    {
        abort_unless($teacher->school_id === $request->user()->school_id && $teacher->role === 'teacher', 404);
    }

    /**
     * Bulk teacher creation: download a blank template, fill in name + email/phone
     * per row, upload back. Every created account gets an auto-generated password,
     * shown together in one table at the end - handing them out one popup at a
     * time doesn't scale past a handful of teachers.
     */
    public function bulkForm()
    {
        return view('principal.teachers.bulk');
    }

    public function bulkTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Teachers');

        $sheet->fromArray(['Full Name', 'Email (optional if phone given)', 'Phone (optional if email given)'], null, 'A1');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        $sheet->fromArray(['Jane Wanjiru', 'jane.wanjiru@example.com', '0712345678'], null, 'A2');
        $sheet->getStyle('A2:C2')->getFont()->setItalic(true);

        foreach (['A', 'B', 'C'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'teacher-import-template.xlsx');
    }

    public function bulkImport(BulkImportTeachersRequest $request)
    {
        $schoolId = $request->user()->school_id;

        $existingEmails = User::whereNotNull('email')->pluck('email', 'email');
        $existingPhones = User::whereNotNull('phone')->pluck('phone', 'phone');

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        $createdAccounts = [];
        $skipped = [];
        $seenInFile = [];

        DB::transaction(function () use ($sheet, $schoolId, $existingEmails, $existingPhones, &$createdAccounts, &$skipped, &$seenInFile) {
            foreach ($sheet->getRowIterator(2) as $row) {
                $rowIndex = $row->getRowIndex();

                $name = trim((string) $sheet->getCell('A'.$rowIndex)->getValue());
                $email = trim((string) $sheet->getCell('B'.$rowIndex)->getValue()) ?: null;
                $phone = trim((string) $sheet->getCell('C'.$rowIndex)->getValue()) ?: null;

                if ($name === '' && ! $email && ! $phone) {
                    continue; // blank row
                }

                if ($name === 'Jane Wanjiru' && $email === 'jane.wanjiru@example.com') {
                    continue; // example row left in by mistake
                }

                if ($name === '') {
                    $whatWeHave = $email ?: ($phone ?: 'a blank row');
                    $skipped[] = "Row {$rowIndex} ({$whatWeHave}): missing name.";
                    continue;
                }

                if (! $email && ! $phone) {
                    $skipped[] = "Row {$rowIndex}: {$name} needs at least an email or a phone number.";
                    continue;
                }

                if ($email && (isset($existingEmails[$email]) || isset($seenInFile['email:'.$email]))) {
                    $skipped[] = "Row {$rowIndex}: email \"{$email}\" is already in use - skipped.";
                    continue;
                }

                if ($phone && (isset($existingPhones[$phone]) || isset($seenInFile['phone:'.$phone]))) {
                    $skipped[] = "Row {$rowIndex}: phone \"{$phone}\" is already in use - skipped.";
                    continue;
                }

                $tempPassword = Str::password(12, symbols: false);

                $teacher = User::create([
                    'role' => 'teacher',
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make($tempPassword),
                    'school_id' => $schoolId,
                    'status' => 'active',
                ]);

                if ($email) {
                    $seenInFile['email:'.$email] = true;
                }
                if ($phone) {
                    $seenInFile['phone:'.$phone] = true;
                }

                $createdAccounts[] = [
                    'name' => $teacher->name,
                    'login' => $teacher->email ?? $teacher->phone,
                    'password' => $tempPassword,
                ];
            }
        });

        return redirect()->route('principal.teachers.index')
            ->with('success', count($createdAccounts).' teacher account(s) created.')
            ->with('bulk_created_accounts', $createdAccounts)
            ->with('skipped_rows', $skipped);
    }
}
