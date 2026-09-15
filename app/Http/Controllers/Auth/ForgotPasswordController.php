<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // Deliberately the same response whether or not the email exists - avoids
        // confirming to a stranger whether a given email has an account here.
        Password::sendResetLink($request->only('email'));

        return back()->with('success', 'If that email is linked to a Mwana account, a password reset link is on its way.');
    }
}
