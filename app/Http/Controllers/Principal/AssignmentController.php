<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Principal\StoreAssignmentRequest;
use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Support\SubjectEnrollment;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $assignments = ClassSubjectTeacher::whereHas('schoolClass', fn ($q) => $q->where('school_id', $schoolId))
            ->with(['schoolClass', 'subject', 'teacher'])
            ->get();

        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();
        $subjects = Subject::where('school_id', $schoolId)->orderBy('name')->get();
        $teachers = User::where('school_id', $schoolId)->where('role', 'teacher')->where('status', 'active')
            ->with('subjectsQualifiedFor')
            ->orderBy('name')
            ->get();

        return view('principal.assignments.index', compact('assignments', 'classes', 'subjects', 'teachers'));
    }

    public function store(StoreAssignmentRequest $request)
    {
        // Validated request already confirms the class/subject/teacher all belong to this principal's school.
        $assignment = ClassSubjectTeacher::firstOrCreate($request->validated());
        $assignment->load(['schoolClass', 'subject']);

        // Core subjects auto-enroll every current student in the class - electives
        // are left for the assigned teacher to enroll deliberately.
        SubjectEnrollment::enrollClassIfCore($assignment->schoolClass, $assignment->subject);

        $message = 'Teacher assigned to subject and class.';
        if (! $assignment->subject->is_core) {
            $message .= ' Since this is an elective, ask the teacher to set up enrollment before marks are due.';
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, ClassSubjectTeacher $assignment)
    {
        abort_unless($assignment->schoolClass->school_id === $request->user()->school_id, 404);

        $assignment->delete();

        return back()->with('success', 'Assignment removed.');
    }
}
