<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Principal\StoreTimetableSlotRequest;
use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;
use App\Models\Term;
use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    protected function days(): array
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }

    /**
     * Grid builder: every day at once (switched between client-side, no reload),
     * every class as a row within each day, existing sessions shown as removable
     * chips, plus a small "add session" form per class row. This replaces the old
     * flat multi-row bulk form - filling in one clearly-labelled session at a time
     * is both easier to get right and easier to fix when something's wrong, since
     * errors land on a specific small form instead of a numbered row in a big batch.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();

        $slots = Timetable::where('school_id', $schoolId)
            ->with(['subject', 'teacher', 'schoolClass'])
            ->orderBy('start_time')
            ->get()
            ->groupBy(['day_of_week', 'school_class_id']);

        // Assignments (who can teach what, in which class) grouped by class, so each
        // class row's "add session" dropdown only ever offers that class's own teachers.
        $assignmentsByClass = ClassSubjectTeacher::whereHas('schoolClass', fn ($q) => $q->where('school_id', $schoolId))
            ->with(['subject', 'teacher'])
            ->get()
            ->groupBy('school_class_id');

        // Every teacher's currently-booked (day, start, end) slots, keyed by teacher id,
        // handed to the frontend as JSON so it can warn about a clash before the form
        // is even submitted, rather than only finding out after a round trip.
        $teacherBookings = Timetable::where('school_id', $schoolId)
            ->get(['teacher_id', 'day_of_week', 'start_time', 'end_time'])
            ->groupBy('teacher_id')
            ->map(fn ($rows) => $rows->map(fn ($r) => [
                'day' => $r->day_of_week,
                'start' => substr($r->start_time, 0, 5),
                'end' => substr($r->end_time, 0, 5),
            ])->values());

        // Every class's currently-booked (day, start, end, is_core) slots, keyed by
        // class id, so the frontend can warn about a class-level clash - two subjects
        // at once, unless every one of them is an elective - before submitting.
        $classBookings = Timetable::where('school_id', $schoolId)
            ->with('subject')
            ->get(['id', 'school_class_id', 'day_of_week', 'start_time', 'end_time', 'subject_id', 'room'])
            ->groupBy('school_class_id')
            ->map(fn ($rows) => $rows->map(fn ($r) => [
                'day' => $r->day_of_week,
                'start' => substr($r->start_time, 0, 5),
                'end' => substr($r->end_time, 0, 5),
                'isCore' => $r->subject->is_core,
                'room' => $r->room,
            ])->values());

        $terms = Term::where('school_id', $schoolId)->orderByDesc('year')->orderBy('name')->get();

        return view('principal.timetable.index', [
            'days' => $this->days(),
            'classes' => $classes,
            'slots' => $slots,
            'assignmentsByClass' => $assignmentsByClass,
            'teacherBookings' => $teacherBookings,
            'classBookings' => $classBookings,
            'terms' => $terms,
        ]);
    }

    public function store(StoreTimetableSlotRequest $request)
    {
        $assignment = ClassSubjectTeacher::findOrFail($request->validated('assignment_id'));

        Timetable::create([
            'school_id' => $request->user()->school_id,
            'school_class_id' => $assignment->school_class_id,
            'subject_id' => $assignment->subject_id,
            'teacher_id' => $assignment->teacher_id,
            'day_of_week' => $request->validated('day_of_week'),
            'start_time' => $request->validated('start_time'),
            'end_time' => $request->validated('end_time'),
            'room' => $request->validated('room'),
            'term_id' => $request->validated('term_id'),
            'status' => 'draft',
        ]);

        return back()->with('success', 'Session added.')->with('active_day', $request->validated('day_of_week'));
    }

    public function destroy(Request $request, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $request->user()->school_id, 404);

        $day = $timetable->day_of_week;
        $timetable->delete();

        return back()->with('success', 'Session removed.')->with('active_day', $day);
    }

    /**
     * Flips every draft session for a class to final, all at once - this is the
     * "review then publish" step. Nothing a teacher sees ever includes a draft
     * session, so a principal can freely add/remove/rearrange before committing.
     */
    public function publish(Request $request, SchoolClass $class)
    {
        abort_unless($class->school_id === $request->user()->school_id, 404);

        $count = Timetable::where('school_class_id', $class->id)->where('status', 'draft')->update(['status' => 'final']);

        $message = $count > 0
            ? "{$class->displayName()}'s timetable published - {$count} session(s) are now visible to teachers."
            : "{$class->displayName()} has no draft sessions to publish.";

        return back()->with('success', $message);
    }
}
