<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use Illuminate\Http\Request;

class ElectiveEnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $assignments = ClassSubjectTeacher::where('teacher_id', $request->user()->id)
            ->whereHas('subject', fn ($q) => $q->where('is_core', false))
            ->with(['schoolClass', 'subject'])
            ->get();

        return view('teacher.electives.index', compact('assignments'));
    }

    public function show(Request $request, ClassSubjectTeacher $assignment)
    {
        $this->authorizeAssignment($request, $assignment);

        $students = $assignment->schoolClass->students()->where('status', 'active')->orderBy('name')->get();
        $enrolledIds = $assignment->subject->enrolledStudents()
            ->whereIn('students.id', $students->pluck('id'))
            ->pluck('students.id');

        return view('teacher.electives.show', [
            'assignment' => $assignment,
            'students' => $students,
            'enrolledIds' => $enrolledIds,
        ]);
    }

    public function update(Request $request, ClassSubjectTeacher $assignment)
    {
        $this->authorizeAssignment($request, $assignment);

        $classStudentIds = $assignment->schoolClass->students()->where('status', 'active')->pluck('id');

        $selected = collect($request->input('students', []))
            ->map(fn ($id) => (int) $id)
            ->intersect($classStudentIds); // only ever this class's active students, regardless of what was posted

        // syncWithoutDetaching would leave stale enrollments from other classes/years in place,
        // which is fine (see SubjectEnrollment doc comment) - but within THIS class's roster we
        // want the checklist to be the source of truth, so detach anyone in this class who was
        // unchecked before re-attaching the current selection.
        $assignment->subject->enrolledStudents()->detach($classStudentIds->diff($selected));
        $assignment->subject->enrolledStudents()->syncWithoutDetaching($selected);

        return redirect()->route('teacher.electives.index')
            ->with('success', "Enrollment updated for {$assignment->subject->name} — {$assignment->schoolClass->displayName()}.");
    }

    protected function authorizeAssignment(Request $request, ClassSubjectTeacher $assignment): void
    {
        abort_unless($assignment->teacher_id === $request->user()->id, 404);
        abort_if($assignment->subject->is_core, 404, 'This subject is core - every student is already enrolled automatically.');
    }
}
