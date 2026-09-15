<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherNoticeRequest;
use App\Models\Notice;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $notices = Notice::where('school_id', $request->user()->school_id)
            ->where('posted_by', $request->user()->id)
            ->with(['schoolClass', 'student'])
            ->latest()
            ->paginate(20);

        return view('teacher.notices.index', compact('notices'));
    }

    public function create(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();
        $students = Student::where('school_id', $schoolId)->where('status', 'active')->orderBy('name')->get();

        return view('teacher.notices.create', compact('classes', 'students'));
    }

    public function store(StoreTeacherNoticeRequest $request)
    {
        Notice::create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'posted_by' => $request->user()->id,
        ]);

        return redirect()->route('teacher.notices.index')->with('success', 'Notice posted.');
    }

    public function destroy(Request $request, Notice $notice)
    {
        // A teacher can only remove notices they posted themselves - not a principal's,
        // and not another teacher's.
        abort_unless($notice->school_id === $request->user()->school_id && $notice->posted_by === $request->user()->id, 404);

        $notice->delete();

        return back()->with('success', 'Notice removed.');
    }
}
