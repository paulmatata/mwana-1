@extends('layouts.app')

@section('title', 'Forgot Password - Mwana')

@push('styles')
<style>
    .login-wrap { max-width: 420px; margin: 64px auto; background: #fff; border: 1px solid #dde3ea; border-radius: 12px; padding: 36px; }
    .login-wrap h1 { font-size: 1.5rem; margin: 0 0 6px; }
    .login-wrap p.sub { color: #52697d; margin: 0 0 28px; font-size: 0.95rem; }
    label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; }
    input[type="email"] { width: 100%; padding: 11px 14px; border: 1px solid #dde3ea; border-radius: 8px; font-size: 0.95rem; margin-bottom: 18px; font-family: inherit; }
    .btn-primary { width: 100%; background: var(--savanna-green); color: #fff; border: none; padding: 13px; border-radius: 8px; font-weight: 700; font-size: 0.98rem; cursor: pointer; }
    .errors { background: #fbe7e9; border-left: 4px solid var(--laterite); padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; color: #7a1620; }
    .flash { background: #e6f4ea; border-left: 4px solid var(--savanna-green); padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
    .back-link { display: inline-block; margin-top: 18px; font-size: 0.85rem; color: #52697d; text-decoration: none; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="login-wrap">
        <h1>Reset your password</h1>
        <p class="sub">Enter the email on your Mwana account and we'll send you a reset link.</p>

        @if (session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            <button type="submit" class="btn-primary">Send reset link</button>
        </form>

        <p style="font-size:0.85rem; color:#8496a6; margin-top:18px;">
            Logged in via phone number only, with no email on file? Ask your school administrator
            to reset your password for you instead.
        </p>

        <a href="{{ route('login') }}" class="back-link">&larr; Back to log in</a>
    </div>
</div>
@endsection
