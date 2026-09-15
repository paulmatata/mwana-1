<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Support\SchoolInsights;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $stats = [
            'classes' => SchoolClass::where('school_id', $schoolId)->count(),
            'subjects' => Subject::where('school_id', $schoolId)->count(),
            'teachers' => User::where('school_id', $schoolId)->where('role', 'teacher')->count(),
            'pending_exams' => Exam::where('school_id', $schoolId)->where('status', 'submitted')->count(),
        ];

        $pendingExams = Exam::where('school_id', $schoolId)
            ->where('status', 'submitted')
            ->with('schoolClass')
            ->latest('submitted_at')
            ->take(5)
            ->get();

        $insights = SchoolInsights::forSchool($schoolId);

        // Latest approved exam average per class, for the class-averages chart.
        $classAverages = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get()
            ->map(function ($class) {
                $latestExam = Exam::where('school_class_id', $class->id)->where('status', 'approved')->latest('approved_at')->first();
                $average = $latestExam?->marks()->avg('score');

                return [
                    'label' => $class->displayName(),
                    'average' => $average !== null ? round($average, 1) : null,
                ];
            })
            ->filter(fn ($row) => $row['average'] !== null)
            ->values();

        return view('principal.dashboard.index', compact('stats', 'pendingExams', 'insights', 'classAverages'));
    }
}
