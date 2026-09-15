<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $children = $request->user()
            ->studentsAsParent()
            ->with(['school', 'schoolClass'])
            ->get()
            ->map(function ($student) {
                $latestApprovedExam = $student->approvedMarks()
                    ->with('exam')
                    ->get()
                    ->pluck('exam')
                    ->unique('id')
                    ->sortByDesc('approved_at')
                    ->first();

                $feeBalance = $student->currentFeeBalance();

                return [
                    'student' => $student,
                    'latest_exam' => $latestApprovedExam,
                    'fee_balance' => $feeBalance,
                ];
            });

        return view('parent.dashboard.index', compact('children'));
    }
}
