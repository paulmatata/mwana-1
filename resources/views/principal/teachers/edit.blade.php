@extends('layouts.dashboard')

@section('title', 'Edit Teacher - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Edit ' . $teacher->name)

@section('nav')
@include('principal._nav', ['active' => 'teachers'])
@endsection

@section('content')

<div class="card" style="max-width:520px;">
    <form method="POST" action="{{ route('principal.teachers.update', $teacher) }}">
        @csrf
        @method('PUT')

        <label for="name">Full name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $teacher->name) }}" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $teacher->email) }}">

        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $teacher->phone) }}">

        <p style="font-size:0.8rem; color:#8496a6; margin:-8px 0 14px;">Provide at least one of email or phone.</p>

        @if ($subjects->isNotEmpty())
            <label>Qualified to teach</label>
            <div style="max-height:180px; overflow-y:auto; border:1px solid #dde3ea; border-radius:8px; padding:10px 14px; margin-bottom:16px;">
                @foreach ($subjects as $subject)
                    <label style="display:flex; align-items:center; gap:8px; font-weight:400; margin-bottom:6px;">
                        <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" style="width:auto; margin:0;"
                            {{ collect(old('subject_ids', $qualifiedIds))->contains($subject->id) ? 'checked' : '' }}>
                        {{ $subject->name }}
                    </label>
                @endforeach
            </div>
            <p style="font-size:0.78rem; color:#8496a6; margin:-10px 0 14px;">
                Leave all unchecked to allow this teacher to be assigned to any subject.
            </p>
        @endif

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn">Save changes</button>
            <a href="{{ route('principal.teachers.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
