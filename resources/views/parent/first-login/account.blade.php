@extends('layouts.app')

@section('title', 'Create Your Account - Mwana')

@push('styles')
<style>
    .wizard-wrap { max-width: 480px; margin: 56px auto; background: #fff; border: 1px solid #dde3ea; border-radius: 12px; padding: 36px; }
    .steps-indicator { display: flex; gap: 6px; margin-bottom: 24px; }
    .steps-indicator span { flex: 1; height: 4px; border-radius: 2px; background: #dde3ea; }
    .steps-indicator span.done { background: var(--savanna-green); }
    .wizard-wrap h1 { font-size: 1.4rem; margin: 0 0 6px; }
    .wizard-wrap p.sub { color: #52697d; margin: 0 0 24px; font-size: 0.92rem; }
    .match-card { background: #e6f4ea; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px; font-size: 0.9rem; }
    .match-card strong { font-family: 'Poppins', sans-serif; font-size: 1.05rem; }
    label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; }
    input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 11px 14px; border: 1px solid #dde3ea; border-radius: 8px; font-size: 0.95rem; margin-bottom: 18px; font-family: inherit; }
    .btn-primary { width: 100%; background: var(--savanna-green); color: #fff; border: none; padding: 13px; border-radius: 8px; font-weight: 700; font-size: 0.98rem; cursor: pointer; }
    .errors { background: #fbe7e9; border-left: 4px solid var(--laterite); padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; color: #7a1620; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="wizard-wrap">
        <div class="steps-indicator">
            <span class="done"></span><span class="done"></span><span class="done"></span><span class="done"></span>
        </div>
        <h1>Step 4 of 4: Create your account</h1>
        <p class="sub">Set a password so future logins are instant &mdash; no need to repeat these steps.</p>

        <div class="match-card">
            Match found: <strong>{{ $student->name }}</strong> (Adm. No. {{ $student->admission_no }})
        </div>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('parent.first-login.account.store') }}">
            @csrf
            <label for="name">Your full name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>

            <label for="email">Email (optional if you provide a phone number)</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">

            <label for="phone">Phone number (optional if you provide an email)</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8">

            <label for="password_confirmation">Confirm password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">

            <button type="submit" class="btn-primary">Create my account</button>
        </form>
    </div>
</div>
@endsection
