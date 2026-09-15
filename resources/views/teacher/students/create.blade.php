@extends('layouts.dashboard')

@section('title', 'Add Student - Mwana')
@section('role-label', 'Teacher')
@section('page-title', 'Add a student')

@section('nav')
@include('teacher._nav', ['active' => 'students'])
@endsection

@section('content')

@if ($classes->isEmpty())
    <div class="card"><div class="empty-state">No classes exist yet at your school. Ask your principal to add one first.</div></div>
@else
    <div class="card" style="max-width:520px;">
        <form method="POST" action="{{ route('teacher.students.store') }}">
            @csrf

            <label for="name">Full name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>

            <label for="admission_no">Admission number</label>
            <input type="text" id="admission_no" name="admission_no" value="{{ old('admission_no') }}" required>
            <p style="font-size:0.8rem; color:#8496a6; margin:-10px 0 14px;">This is what the student's parent will need for their guided first-login &mdash; double-check it matches the school register exactly.</p>

            <label for="school_class_id">Class</label>
            <select id="school_class_id" name="school_class_id" required>
                <option value="">— Select —</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->displayName() }}</option>
                @endforeach
            </select>

            <label for="gender">Gender (optional)</label>
            <select id="gender" name="gender">
                <option value="">— Not specified —</option>
                <option value="male" @selected(old('gender') === 'male')>Male</option>
                <option value="female" @selected(old('gender') === 'female')>Female</option>
            </select>

            <label for="date_of_birth">Date of birth (optional)</label>
            <input type="text" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" placeholder="YYYY-MM-DD">

            <label for="guardian_phone">Guardian phone (optional)</label>
            <input type="text" id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}">

            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" class="btn">Add student</button>
                <a href="{{ route('teacher.students.index') }}" class="btn secondary">Cancel</a>
            </div>
        </form>
    </div>
@endif

@endsection
