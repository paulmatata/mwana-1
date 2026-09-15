<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $slots = Timetable::where('teacher_id', $request->user()->id)
            ->where('status', 'final')
            ->with(['schoolClass', 'subject'])
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return view('teacher.timetable.index', compact('slots', 'days'));
    }
}
