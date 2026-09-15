<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\GradeCalculator;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function index(Request $request, Student $student)
    {
        $this->authorizeChild($request, $student);

        $student->load('schoolClass', 'school');

        $marks = $student->approvedMarks()->with(['exam', 'subject'])->get();

        $byExam = $marks->groupBy('exam_id')->map(function ($examMarks) {
            $total = $examMarks->sum('score');
            $count = $examMarks->count();
            $average = $count > 0 ? round($total / $count, 1) : 0;

            return [
                'exam' => $examMarks->first()->exam,
                'marks' => $examMarks,
                'total' => $total,
                'average' => $average,
                'mean_grade' => GradeCalculator::forScore($average),
            ];
        });

        return view('parent.results.index', compact('student', 'byExam'));
    }

    protected function authorizeChild(Request $request, Student $student): void
    {
        abort_unless(
            $request->user()->studentsAsParent()->where('student_id', $student->id)->exists(),
            404
        );
    }
}
