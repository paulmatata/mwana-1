<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Principal\StoreTermRequest;
use App\Models\Term;
use Illuminate\Http\Request;

class TermController extends Controller
{
    public function index(Request $request)
    {
        $terms = Term::where('school_id', $request->user()->school_id)
            ->orderByDesc('year')
            ->orderBy('name')
            ->get();

        return view('principal.terms.index', compact('terms'));
    }

    public function store(StoreTermRequest $request)
    {
        $term = Term::create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        if ($request->boolean('make_current')) {
            $term->makeCurrent();
        }

        return back()->with('success', "{$term->label()} added.");
    }

    public function makeCurrent(Request $request, Term $term)
    {
        abort_unless($term->school_id === $request->user()->school_id, 404);

        $term->makeCurrent();

        return back()->with('success', "{$term->label()} is now the current term.");
    }

    public function destroy(Request $request, Term $term)
    {
        abort_unless($term->school_id === $request->user()->school_id, 404);

        if ($term->exams()->exists() || $term->timetables()->exists() || $term->feeTransactions()->exists()) {
            return back()->withErrors(['term' => 'This term has exams, timetable sessions, or fee transactions tied to it, so it can\'t be deleted. It can still be left as-is for historical reference.']);
        }

        $term->delete();

        return back()->with('success', 'Term removed.');
    }
}
