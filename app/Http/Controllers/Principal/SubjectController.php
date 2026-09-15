<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Principal\StoreSubjectRequest;
use App\Http\Requests\Principal\UpdateSubjectRequest;
use App\Models\Subject;
use App\Support\SubjectEnrollment;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::where('school_id', $request->user()->school_id)
            ->orderBy('name')
            ->get();

        return view('principal.subjects.index', compact('subjects'));
    }

    public function store(StoreSubjectRequest $request)
    {
        Subject::create([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'is_core' => $request->boolean('is_core'),
            'school_id' => $request->user()->school_id,
        ]);

        return back()->with('success', 'Subject added.');
    }

    public function edit(Request $request, Subject $subject)
    {
        $this->authorizeSchool($request, $subject);

        return view('principal.subjects.edit', compact('subject'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        $this->authorizeSchool($request, $subject);

        $wasElective = ! $subject->is_core;

        $subject->update([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'is_core' => $request->boolean('is_core'),
        ]);

        // Switching elective -> core should immediately catch up enrollment for
        // every class the subject is already assigned to, not just future ones.
        if ($wasElective && $subject->is_core) {
            $subject->classAssignments()->with('schoolClass')->get()
                ->pluck('schoolClass')
                ->unique('id')
                ->each(fn ($class) => SubjectEnrollment::enrollClassIfCore($class, $subject));
        }

        return redirect()->route('principal.subjects.index')->with('success', 'Subject updated.');
    }

    public function destroy(Request $request, Subject $subject)
    {
        $this->authorizeSchool($request, $subject);

        if ($subject->marks()->exists() || $subject->classAssignments()->exists()) {
            return back()->withErrors(['subject' => 'Cannot delete a subject that has marks or teacher assignments tied to it.']);
        }

        $subject->delete();

        return back()->with('success', 'Subject removed.');
    }

    protected function authorizeSchool(Request $request, Subject $subject): void
    {
        abort_unless($subject->school_id === $request->user()->school_id, 404);
    }
}
