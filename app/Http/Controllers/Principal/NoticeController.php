<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Principal\StoreNoticeRequest;
use App\Models\Notice;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $notices = Notice::where('school_id', $request->user()->school_id)
            ->with(['schoolClass', 'student', 'postedBy'])
            ->latest()
            ->paginate(20);

        return view('principal.notices.index', compact('notices'));
    }

    public function create(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();
        $students = Student::where('school_id', $schoolId)->where('status', 'active')->orderBy('name')->get();

        return view('principal.notices.create', compact('classes', 'students'));
    }

    public function store(StoreNoticeRequest $request)
    {
        Notice::create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'posted_by' => $request->user()->id,
        ]);

        return redirect()->route('principal.notices.index')->with('success', 'Notice posted.');
    }

    public function destroy(Request $request, Notice $notice)
    {
        abort_unless($notice->school_id === $request->user()->school_id, 404);

        $notice->delete();

        return back()->with('success', 'Notice removed.');
    }
}
