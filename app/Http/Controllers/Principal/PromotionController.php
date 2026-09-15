<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Support\SubjectEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    /**
     * Review screen: every active student in the class, pre-checked to be
     * promoted. The principal can uncheck anyone who should be held back
     * (repeat the class) before committing anything.
     */
    public function show(Request $request, SchoolClass $class)
    {
        $this->authorizeSchool($request, $class);

        $students = $class->students()->where('status', 'active')->orderBy('name')->get();

        return view('principal.promotion.show', compact('class', 'students'));
    }

    /**
     * Executes the promotion for whichever students were left checked.
     * Anyone unchecked simply stays in this class (held back) - nothing
     * happens to them. If this class has no promotion destination set,
     * promoted students are marked graduated instead of moved.
     */
    public function store(Request $request, SchoolClass $class)
    {
        $this->authorizeSchool($request, $class);

        $request->validate([
            'promote' => ['nullable', 'array'],
            'promote.*' => ['integer'],
        ]);

        $selectedIds = collect($request->input('promote', []))->map(fn ($id) => (int) $id);

        $students = $class->students()->where('status', 'active')->whereIn('id', $selectedIds)->get();

        $destination = $class->promotesTo;

        DB::transaction(function () use ($students, $destination) {
            foreach ($students as $student) {
                if ($destination) {
                    $student->update(['school_class_id' => $destination->id]);
                    SubjectEnrollment::syncCoreSubjectsForStudent($student);
                } else {
                    $student->update(['status' => 'graduated']);
                }
            }
        });

        $heldBack = $class->students()->where('status', 'active')->count();

        $message = $destination
            ? "{$students->count()} student(s) promoted to {$destination->displayName()}."
            : "{$students->count()} student(s) marked as graduated.";

        if ($heldBack > 0) {
            $message .= " {$heldBack} student(s) held back in {$class->displayName()}.";
        }

        return redirect()->route('principal.classes.index')->with('success', $message);
    }

    protected function authorizeSchool(Request $request, SchoolClass $class): void
    {
        abort_unless($class->school_id === $request->user()->school_id, 404);
    }
}
