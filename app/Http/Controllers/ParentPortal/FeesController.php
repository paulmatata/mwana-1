<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class FeesController extends Controller
{
    public function index(Request $request, Student $student)
    {
        $this->authorizeChild($request, $student);

        $student->load('school', 'schoolClass');

        $transactions = $student->feeTransactions()->with('academicTerm')->get();

        return view('parent.fees.index', compact('student', 'transactions'));
    }

    protected function authorizeChild(Request $request, Student $student): void
    {
        abort_unless(
            $request->user()->studentsAsParent()->where('student_id', $student->id)->exists(),
            404
        );
    }
}
