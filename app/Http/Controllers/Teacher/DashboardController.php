<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;
use App\Models\Timetable;
use App\Support\SchoolInsights;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();

        $assignments = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->with(['schoolClass', 'subject'])
            ->get();

        $todaysClasses = Timetable::where('teacher_id', $teacher->id)
            ->where('status', 'final')
            ->where('day_of_week', now()->format('l'))
            ->with(['schoolClass', 'subject'])
            ->orderBy('start_time')
            ->get();

        $classTeacherOf = SchoolClass::where('class_teacher_id', $teacher->id)->orderBy('name')->get();

        $insights = $classTeacherOf->flatMap(fn ($class) => SchoolInsights::forClass($class));

        return view('teacher.dashboard.index', compact('assignments', 'todaysClasses', 'classTeacherOf', 'insights'));
    }
}
