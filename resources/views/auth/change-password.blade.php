@extends('layouts.dashboard')

@section('title', 'Change Password - Mwana')
@section('role-label', ucfirst(str_replace('_', ' ', auth()->user()->role)))
@section('page-title', 'Change Password')

@section('nav')
    @if (auth()->user()->isSuperAdmin())
        @include('super-admin._nav', ['active' => 'account'])
    @elseif (auth()->user()->isPrincipal())
        @include('principal._nav', ['active' => 'account'])
    @elseif (auth()->user()->isTeacher())
        @include('teacher._nav', ['active' => 'account'])
    @elseif (auth()->user()->isParent())
        @include('parent._nav', ['active' => 'account'])
    @endif
@endsection

@section('content')

<div class="card" style="max-width:460px;">
    <form method="POST" action="{{ route('account.password.update') }}">
        @csrf
        @method('PUT')

        <label for="current_password">Current password</label>
        <input type="password" id="current_password" name="current_password" required>

        <label for="password">New password</label>
        <input type="password" id="password" name="password" required minlength="8">

        <label for="password_confirmation">Confirm new password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">

        <button type="submit" class="btn">Update password</button>
    </form>
</div>

@endsection
