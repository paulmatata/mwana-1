<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParentPortal\StoreParentAccountRequest;
use App\Http\Requests\ParentPortal\VerifyStudentRequest;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * The guided lookup exists for one reason: to make sure a parent only ever
 * lands on their own child's record, never anyone else's. Every step narrows
 * the search (school -> class -> admission number + name) before anything is
 * shown, and the final match is exact - no partial name matching, no fuzzy logic.
 *
 * Same flow serves two situations:
 *  - a brand new parent, who ends up creating a password-based account
 *  - an already-logged-in parent linking a second (or third) child, who
 *    skips account creation and is sent straight back to their dashboard
 */
class FirstLoginController extends Controller
{
    protected function guardAccess(Request $request): void
    {
        // Anyone can walk through the lookup (guests, or a parent adding another child).
        // Staff roles have no business here.
        if (Auth::check() && ! $request->user()->isParent()) {
            abort(403);
        }
    }

    public function chooseSchool(Request $request)
    {
        $this->guardAccess($request);

        $schools = School::where('status', 'active')->orderBy('name')->get();

        return view('parent.first-login.school', compact('schools'));
    }

    public function storeSchool(Request $request)
    {
        $this->guardAccess($request);

        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
        ]);

        $school = School::where('status', 'active')->findOrFail($validated['school_id']);

        $request->session()->put('first_login.school_id', $school->id);
        $request->session()->forget(['first_login.class_id', 'first_login.student_id']);

        return redirect()->route('parent.first-login.class');
    }

    public function chooseClass(Request $request)
    {
        $this->guardAccess($request);

        $schoolId = $request->session()->get('first_login.school_id');
        abort_unless($schoolId, 400, 'Please select a school first.');

        $school = School::findOrFail($schoolId);
        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();

        return view('parent.first-login.class', compact('school', 'classes'));
    }

    public function storeClass(Request $request)
    {
        $this->guardAccess($request);

        $schoolId = $request->session()->get('first_login.school_id');
        abort_unless($schoolId, 400, 'Please select a school first.');

        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
        ]);

        $class = SchoolClass::where('school_id', $schoolId)->findOrFail($validated['school_class_id']);

        $request->session()->put('first_login.class_id', $class->id);
        $request->session()->forget('first_login.student_id');

        return redirect()->route('parent.first-login.verify');
    }

    public function verify(Request $request)
    {
        $this->guardAccess($request);

        $schoolId = $request->session()->get('first_login.school_id');
        $classId = $request->session()->get('first_login.class_id');
        abort_unless($schoolId && $classId, 400, 'Please select a school and class first.');

        $school = School::findOrFail($schoolId);
        $class = SchoolClass::findOrFail($classId);

        return view('parent.first-login.verify', compact('school', 'class'));
    }

    public function storeVerify(VerifyStudentRequest $request)
    {
        $this->guardAccess($request);

        $schoolId = $request->session()->get('first_login.school_id');
        $classId = $request->session()->get('first_login.class_id');
        abort_unless($schoolId && $classId, 400, 'Please select a school and class first.');

        // Exact match only, scoped to the school AND class already chosen.
        // admission_no is unique per school, so this is unambiguous.
        $student = Student::where('school_id', $schoolId)
            ->where('school_class_id', $classId)
            ->where('admission_no', $request->validated('admission_no'))
            ->whereRaw('LOWER(name) = ?', [strtolower($request->validated('name'))])
            ->where('status', 'active')
            ->first();

        if (! $student) {
            return back()
                ->withErrors(['admission_no' => 'We couldn\'t find a matching student. Double-check the admission number and full name exactly as registered by the school.'])
                ->onlyInput('admission_no', 'name');
        }

        $request->session()->put('first_login.student_id', $student->id);

        // Already-logged-in parent adding another child: link immediately, no account step needed.
        if (Auth::check() && Auth::user()->isParent()) {
            $parent = Auth::user();

            if ($parent->studentsAsParent()->where('student_id', $student->id)->exists()) {
                $request->session()->forget(['first_login.school_id', 'first_login.class_id', 'first_login.student_id']);
                return redirect()->route('parent.dashboard')->with('success', "{$student->name} is already linked to your account.");
            }

            $parent->studentsAsParent()->attach($student->id, ['linked_at' => now()]);
            $request->session()->forget(['first_login.school_id', 'first_login.class_id', 'first_login.student_id']);

            return redirect()->route('parent.dashboard')->with('success', "{$student->name} has been added to your account.");
        }

        return redirect()->route('parent.first-login.account');
    }

    public function account(Request $request)
    {
        $this->guardAccess($request);

        if (Auth::check()) {
            return redirect()->route('parent.dashboard');
        }

        $studentId = $request->session()->get('first_login.student_id');
        abort_unless($studentId, 400, 'Please complete the previous steps first.');

        $student = Student::findOrFail($studentId);

        return view('parent.first-login.account', compact('student'));
    }

    public function storeAccount(StoreParentAccountRequest $request)
    {
        if (Auth::check()) {
            // An account already exists for this session - nothing to create.
            return redirect()->route('parent.dashboard');
        }

        $studentId = $request->session()->get('first_login.student_id');
        abort_unless($studentId, 400, 'Please complete the previous steps first.');

        $student = Student::findOrFail($studentId);

        $parent = User::create([
            'role' => 'parent',
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'password' => Hash::make($request->validated('password')),
            'school_id' => null, // a parent isn't tied to one school - they may have children at several
            'status' => 'active',
        ]);

        $parent->studentsAsParent()->attach($student->id, ['linked_at' => now()]);

        $request->session()->forget(['first_login.school_id', 'first_login.class_id', 'first_login.student_id']);

        Auth::login($parent);
        $request->session()->regenerate();

        return redirect()->route('parent.dashboard')->with('success', "Welcome to Mwana! You're now linked to {$student->name}.");
    }
}
