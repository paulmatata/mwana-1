<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreSchoolRequest;
use App\Http\Requests\SuperAdmin\UpdateSchoolRequest;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::query()
            ->withCount(['students', 'teachers', 'classes'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($w) use ($term) {
                    $w->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhere('county', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.schools.index', compact('schools'));
    }

    public function create()
    {
        return view('super-admin.schools.create');
    }

    public function store(StoreSchoolRequest $request)
    {
        $school = School::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('super-admin.schools.show', $school)
            ->with('success', "{$school->name} has been added to Mwana. Next, create a principal account for it.");
    }

    public function show(School $school)
    {
        $school->load(['principals', 'teachers']);
        $school->loadCount(['students', 'classes', 'subjects']);

        return view('super-admin.schools.show', compact('school'));
    }

    public function edit(School $school)
    {
        return view('super-admin.schools.edit', compact('school'));
    }

    public function update(UpdateSchoolRequest $request, School $school)
    {
        $school->update($request->validated());

        return redirect()
            ->route('super-admin.schools.show', $school)
            ->with('success', 'School details updated.');
    }

    /**
     * Schools are never hard-deleted from here - too much depends on them (classes,
     * students, results). Instead the super admin can suspend/reactivate access.
     */
    public function toggleStatus(School $school)
    {
        $school->update([
            'status' => $school->status === 'active' ? 'suspended' : 'active',
        ]);

        $message = $school->status === 'active'
            ? "{$school->name} has been reactivated."
            : "{$school->name} has been suspended. Its staff and parents will be unable to log in until reactivated.";

        return back()->with('success', $message);
    }
}
