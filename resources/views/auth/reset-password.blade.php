@extends('layouts.app')

@section('title', 'Set New Password - Mwana')

@push('styles')
<style>
    .login-wrap { max-width: 420px; margin: 64px auto; background: #fff; border: 1px solid #dde3ea; border-radius: 12px; padding: 36px; }
    .login-wrap h1 { font-size: 1.5rem; margin: 0 0 6px; }
    .login-wrap p.sub { color: #52697d; margin: 0 0 28px; font-size: 0.95rem; }
    label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; }
    input[type="email"], input[type="password"] { width: 100%; padding: 11px 14px; border: 1px solid #dde3ea; border-radius: 8px; font-size: 0.95rem; margin-bottom: 18px; font-family: inherit; }
    .btn-primary { width: 100%; background: var(--savanna-green); color: #fff; border: none; padding: 13px; border-radius: 8px; font-weight: 700; font-size: 0.98rem; cursor: pointer; }
    .errors { background: #fbe7e9; border-left: 4px solid var(--laterite); padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; color: #7a1620; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="login-wrap">
        <h1>Set a new password</h1>
        <p class="sub">Choose a new password for your Mwana account.</p>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus>

            <label for="password">New password</label>
            <input type="password" id="password" name="password" required minlength="8">

            <label for="password_confirmation">Confirm new password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">

            <button type="submit" class="btn-primary">Reset password</button>
        </form>
    </div>
</div>
@endsection
