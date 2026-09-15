<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'], // email OR phone
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (! Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()->withErrors([
                'login' => 'Those details don\'t match a Mwana account.',
            ])->onlyInput('login');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->status === 'suspended') {
            Auth::logout();
            return back()->withErrors(['login' => 'Your account has been suspended. Contact your school or the Mwana administrator.']);
        }

        if ($user->school_id && $user->school?->status === 'suspended') {
            Auth::logout();
            return back()->withErrors(['login' => 'This school\'s access to Mwana is currently suspended. Contact the Mwana administrator.']);
        }

        return redirect()->intended($this->dashboardFor($user->role));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function dashboardFor(string $role): string
    {
        return match ($role) {
            'super_admin' => route('super-admin.dashboard'),
            'principal' => route('principal.dashboard'),
            'teacher' => route('teacher.dashboard'),
            'parent' => route('parent.dashboard'),
            default => '/',
        };
    }
}
