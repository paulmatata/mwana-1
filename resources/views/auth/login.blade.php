@extends('layouts.app')

@section('title', 'Log in - Mwana')

@push('styles')
<style>
    .login-wrap {
        max-width: 420px;
        margin: 64px auto;
        background: #fff;
        border: 1px solid #dde3ea;
        border-radius: 12px;
        padding: 36px;
    }
    .login-wrap h1 { font-size: 1.5rem; margin: 0 0 6px; }
    .login-wrap p.sub { color: #52697d; margin: 0 0 28px; font-size: 0.95rem; }
    label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; }
    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid #dde3ea;
        border-radius: 8px;
        font-size: 0.95rem;
        margin-bottom: 18px;
        font-family: inherit;
    }
    .btn-primary {
        width: 100%;
        background: var(--savanna-green);
        color: #fff;
        border: none;
        padding: 13px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.98rem;
        cursor: pointer;
    }
    .errors { background: #fbe7e9; border-left: 4px solid var(--laterite); padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; color: #7a1620; }
    .parent-hint { margin-top: 22px; padding-top: 18px; border-top: 1px solid #dde3ea; font-size: 0.88rem; color: #52697d; text-align: center; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="login-wrap">
        <h1>Welcome back</h1>
        <p class="sub">Log in to your Mwana account.</p>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <label for="login">Email or phone number</label>
            <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <p style="margin:-10px 0 18px; text-align:right;"><a href="{{ route('password.request') }}" style="font-size:0.82rem; color:#52697d;">Forgot password?</a></p>

            <button type="submit" class="btn-primary">Log in</button>
        </form>

        <p class="parent-hint">
            Parent logging in for the first time?
            <a href="{{ url('/parent/first-login') }}">Start here</a>
        </p>
    </div>
</div>
@endsection
