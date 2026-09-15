<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Student;
use Illuminate\Http\Request;

class NoticesController extends Controller
{
    public function index(Request $request, Student $student)
    {
        $this->authorizeChild($request, $student);

        $student->load('school', 'schoolClass');

        $notices = Notice::where('school_id', $student->school_id)
            ->where(function ($q) use ($student) {
                $q->where(function ($schoolWide) {
                    $schoolWide->whereNull('school_class_id')->whereNull('student_id');
                })
                ->orWhere('school_class_id', $student->school_class_id)
                ->orWhere('student_id', $student->id);
            })
            ->with('postedBy')
            ->latest()
            ->get();

        return view('parent.notices.index', compact('student', 'notices'));
    }

    protected function authorizeChild(Request $request, Student $student): void
    {
        abort_unless(
            $request->user()->studentsAsParent()->where('student_id', $student->id)->exists(),
            404
        );
    }
}
