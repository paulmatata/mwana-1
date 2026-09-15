<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Principal\RejectExamRequest;
use App\Http\Requests\Principal\StoreExamRequest;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Term;
use App\Support\GradeCalculator;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $exams = Exam::where('school_id', $schoolId)
            ->with(['schoolClass', 'academicTerm'])
            ->latest()
            ->get();

        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();
        $terms = Term::where('school_id', $schoolId)->orderByDesc('year')->orderBy('name')->get();

        return view('principal.exams.index', compact('exams', 'classes', 'terms'));
    }

    public function store(StoreExamRequest $request)
    {
        $schoolId = $request->user()->school_id;

        $classes = $request->boolean('whole_school')
            ? SchoolClass::where('school_id', $schoolId)->get()
            : SchoolClass::where('school_id', $schoolId)->whereIn('id', $request->validated('school_class_ids', []))->get();

        $created = 0;
        $skipped = [];

        foreach ($classes as $class) {
            // A class teacher is required to submit an exam for review (see ClassExamController),
            // so an exam created for a class with nobody assigned to that role would have no
            // path to ever leave "draft" status. Block it here rather than let it become a dead end.
            if (! $class->class_teacher_id) {
                $skipped[] = "{$class->displayName()} (no class teacher assigned yet)";
                continue;
            }

            Exam::create([
                'school_id' => $schoolId,
                'school_class_id' => $class->id,
                'term_id' => $request->validated('term_id'),
                'name' => $request->validated('name'),
                'exam_date' => $request->validated('exam_date'),
                'status' => 'draft',
            ]);

            $created++;
        }

        $message = $created === 1
            ? 'Exam window created. The class teacher can now oversee marks upload.'
            : "{$created} exam window(s) created, one per class.";

        return back()
            ->with('success', $message)
            ->with('skipped_classes', $skipped);
    }

    public function show(Request $request, Exam $exam)
    {
        $this->authorizeSchool($request, $exam);

        $exam->load(['marks.student', 'marks.subject', 'schoolClass', 'academicTerm']);

        // Marks grouped by student, for a readable review table.
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

        // Ranked by performance - highest average first, matching how a school
        // actually determines class position (1st, 2nd, ... last).
        $byStudent = $byStudent->sortByDesc(fn ($marks, $studentId) => $studentSummaries[$studentId]['average']);

        $classAverage = $studentSummaries->isNotEmpty() ? round($studentSummaries->avg('average'), 1) : null;

        $subjectAverages = $exam->marks->groupBy('subject_id')->map(fn ($marks) => round($marks->avg('score'), 1));

        return view('principal.exams.show', compact('exam', 'byStudent', 'studentSummaries', 'classAverage', 'subjectAverages'));
    }

    /**
     * Normally the class teacher submits an exam for review once every subject's
     * marks are in (see Teacher\ClassExamController::submit). This stays available
     * as a principal-side override - useful if a class teacher is unavailable -
     * and isn't restricted to a class-teacher check since the principal is already
     * the one who approves it afterward anyway.
     */
    public function submit(Request $request, Exam $exam)
    {
        $this->authorizeSchool($request, $exam);

        if (! $exam->marks()->exists()) {
            return back()->withErrors(['exam' => 'This exam has no marks uploaded yet.']);
        }

        $exam->submit($request->user());

        return back()->with('success', 'Exam submitted for principal review.');
    }

    public function approve(Request $request, Exam $exam)
    {
        $this->authorizeSchool($request, $exam);
        abort_unless($exam->status === 'submitted', 422, 'Only submitted exams can be approved.');

        $exam->approve($request->user());

        return back()->with('success', 'Results approved. Parents can now view them.');
    }

    public function reject(RejectExamRequest $request, Exam $exam)
    {
        $this->authorizeSchool($request, $exam);
        abort_unless($exam->status === 'submitted', 422, 'Only submitted exams can be rejected.');

        $exam->reject($request->user(), $request->validated('rejection_reason'));

        return back()->with('success', 'Exam sent back to the teacher with your feedback.');
    }

    protected function authorizeSchool(Request $request, Exam $exam): void
    {
        abort_unless($exam->school_id === $request->user()->school_id, 404);
    }
}
