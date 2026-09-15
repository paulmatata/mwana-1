<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Support\GradeCalculator;
use Illuminate\Http\Request;

/**
 * Submitting an exam for principal review is restricted to the class's own
 * class teacher - not just anyone who teaches a subject in that class. This
 * exists so one subject teacher can't submit before everyone else has
 * finished uploading their marks. The same page gives the class teacher a
 * subject-by-subject completion table so they can see exactly what's still
 * missing before they decide to submit.
 */
class ClassExamController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::where('class_teacher_id', $request->user()->id)->orderBy('name')->get();

        $exams = Exam::whereIn('school_class_id', $classes->pluck('id'))
            ->with('schoolClass')
            ->latest()
            ->get();

        return view('teacher.class-exams.index', compact('classes', 'exams'));
    }

    public function show(Request $request, Exam $exam)
    {
        $this->authorizeClassTeacher($request, $exam);

        $exam->load(['schoolClass', 'academicTerm', 'marks.student', 'marks.subject']);

        $assignments = ClassSubjectTeacher::where('school_class_id', $exam->school_class_id)
            ->with(['subject', 'teacher'])
            ->get();

        $marksBySubject = $exam->marks->groupBy('subject_id');

        // Expected total is per-subject enrollment, not just "everyone in the class" -
        // a core subject's total will naturally equal the class roster, while an
        // elective's total only counts whoever the teacher actually enrolled.
        $subjectRows = $assignments->map(function ($assignment) use ($marksBySubject) {
            $enrolledCount = $assignment->subject->enrolledStudents()
                ->where('students.school_class_id', $assignment->school_class_id)
                ->where('students.status', 'active')
                ->count();

            $subjectMarks = $marksBySubject->get($assignment->subject_id, collect());

            return [
                'subject' => $assignment->subject,
                'teacher' => $assignment->teacher,
                'entered' => $subjectMarks->count(),
                'total' => $enrolledCount,
                'complete' => $enrolledCount > 0 && $subjectMarks->count() >= $enrolledCount,
                'average' => $subjectMarks->isNotEmpty() ? round($subjectMarks->avg('score'), 1) : null,
            ];
        });

        $allComplete = $subjectRows->isNotEmpty() && $subjectRows->every(fn ($row) => $row['complete']);

        $totalStudents = $exam->schoolClass->students()->where('status', 'active')->count();

        // Per-student performance, ranked highest average first - a class teacher
        // needs to see this to know who's 1st, who's struggling, and so on.
        $byStudent = $exam->marks->groupBy('student_id');

        $studentSummaries = $byStudent->map(function ($marks) {
            $total = $marks->sum('score');
            $count = $marks->count();
            $average = $count > 0 ? round($total / $count, 1) : 0;

            return [
                'total' => $total,
                'average' => $average,
                'mean_grade' => GradeCalculator::forScore($average),
            ];
        });

        $byStudent = $byStudent->sortByDesc(fn ($marks, $studentId) => $studentSummaries[$studentId]['average']);

        $classAverage = $studentSummaries->isNotEmpty()
            ? round($studentSummaries->avg('average'), 1)
            : null;

        return view('teacher.class-exams.show', compact(
            'exam', 'subjectRows', 'allComplete', 'totalStudents',
            'byStudent', 'studentSummaries', 'classAverage'
        ));
    }

    public function submit(Request $request, Exam $exam)
    {
        $this->authorizeClassTeacher($request, $exam);

        if (! in_array($exam->status, ['draft', 'rejected'], true)) {
            return back()->withErrors(['exam' => 'This exam is not in a state that can be submitted right now.']);
        }

        if (! $exam->marks()->exists()) {
            return back()->withErrors(['exam' => 'No marks have been uploaded for this exam yet.']);
        }

        $exam->submit($request->user());

        return redirect()->route('teacher.class-exams.index')->with('success', 'Submitted for principal review.');
    }

    protected function authorizeClassTeacher(Request $request, Exam $exam): void
    {
        abort_unless($exam->school_id === $request->user()->school_id, 404);

        $exam->loadMissing('schoolClass');

        abort_unless(
            $exam->schoolClass->class_teacher_id === $request->user()->id,
            403,
            'Only this class\'s class teacher can review and submit its exams.'
        );
    }
}
