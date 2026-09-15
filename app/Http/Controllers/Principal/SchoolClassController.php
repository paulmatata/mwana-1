<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Principal\StoreSchoolClassRequest;
use App\Http\Requests\Principal\UpdateSchoolClassRequest;
use App\Models\ClassSubjectTeacher;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Term;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $classes = SchoolClass::where('school_id', $schoolId)
            ->withCount('students')
            ->with(['classTeacher', 'promotesTo'])
            ->orderBy('name')
            ->get();

        $teachers = User::where('school_id', $schoolId)->where('role', 'teacher')->where('status', 'active')->get();

        return view('principal.classes.index', compact('classes', 'teachers'));
    }

    /**
     * The full picture for one class: who teaches it, how many students, the
     * current term's exam status and average, timetable, and fee position -
     * everything scoped to the current term where that distinction matters.
     */
    public function show(Request $request, SchoolClass $class)
    {
        $this->authorizeSchool($request, $class);

        $class->load('classTeacher', 'promotesTo');

        $currentTerm = Term::where('school_id', $class->school_id)->where('is_current', true)->first();

        $assignments = ClassSubjectTeacher::where('school_class_id', $class->id)
            ->with(['subject', 'teacher'])
            ->get();

        $students = $class->students()->where('status', 'active')->orderBy('admission_no')->get();

        $examsQuery = Exam::where('school_class_id', $class->id)->with('academicTerm')->latest();
        if ($currentTerm) {
            $examsQuery->where('term_id', $currentTerm->id);
        }
        $exams = $examsQuery->get()->map(function ($exam) {
            $avg = $exam->marks()->avg('score');
            return [
                'exam' => $exam,
                'average' => $avg !== null ? round($avg, 1) : null,
            ];
        });

        $timetable = Timetable::where('school_class_id', $class->id)
            ->where('status', 'final')
            ->with(['subject', 'teacher'])
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $totalOwed = $students->sum(fn ($student) => $student->currentFeeBalance());

        return view('principal.classes.show', compact(
            'class', 'currentTerm', 'assignments', 'students', 'exams', 'timetable', 'totalOwed'
        ));
    }

    public function store(StoreSchoolClassRequest $request)
    {
        SchoolClass::create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return back()->with('success', 'Class added.');
    }

    public function edit(Request $request, SchoolClass $class)
    {
        $this->authorizeSchool($request, $class);

        $teachers = User::where('school_id', $request->user()->school_id)->where('role', 'teacher')->get();

        $otherClasses = SchoolClass::where('school_id', $request->user()->school_id)
            ->where('id', '!=', $class->id)
            ->orderBy('name')
            ->get();

        return view('principal.classes.edit', ['class' => $class, 'teachers' => $teachers, 'otherClasses' => $otherClasses]);
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $class)
    {
        $this->authorizeSchool($request, $class);

        $class->update($request->validated());

        return redirect()->route('principal.classes.index')->with('success', 'Class updated.');
    }

    public function destroy(Request $request, SchoolClass $class)
    {
        $this->authorizeSchool($request, $class);

        if ($class->students()->exists()) {
            return back()->withErrors(['class' => 'Cannot delete a class that still has students in it. Move or remove students first.']);
        }

        $class->delete();

        return back()->with('success', 'Class removed.');
    }

    protected function authorizeSchool(Request $request, SchoolClass $class): void
    {
        abort_unless($class->school_id === $request->user()->school_id, 404);
    }
}
