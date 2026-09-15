@extends('layouts.dashboard')

@section('title', 'Edit Subject - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Edit ' . $subject->name)

@section('nav')
@include('principal._nav', ['active' => 'subjects'])
@endsection

@section('content')

<div class="card" style="max-width:500px;">
    <form method="POST" action="{{ route('principal.subjects.update', $subject) }}">
        @csrf
        @method('PUT')

        <label for="name">Subject name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $subject->name) }}" required>

        <label for="code">Code</label>
        <input type="text" id="code" name="code" value="{{ old('code', $subject->code) }}">

        <input type="hidden" name="is_core" value="0">
        <label style="display:flex; align-items:center; gap:8px; font-weight:500;">
            <input type="checkbox" name="is_core" value="1" style="width:auto; margin:0;" {{ old('is_core', $subject->is_core) ? 'checked' : '' }}>
            Core subject (every student in an assigned class is enrolled automatically)
        </label>
        <p style="font-size:0.78rem; color:#8496a6; margin:4px 0 16px;">
            Switching this to core will immediately enroll every current student in classes this
            subject is already assigned to.
        </p>

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn">Save changes</button>
            <a href="{{ route('principal.subjects.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
