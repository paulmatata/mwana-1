@extends('layouts.dashboard')

@section('title', 'Edit Student - Mwana')
@section('role-label', 'Teacher')
@section('page-title', 'Edit ' . $student->name)

@section('nav')
@include('teacher._nav', ['active' => 'students'])
@endsection

@section('content')

<div class="card" style="max-width:520px;">
    <form method="POST" action="{{ route('teacher.students.update', $student) }}">
        @csrf
        @method('PUT')

        <label for="name">Full name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $student->name) }}" required>

        <label for="admission_no">Admission number</label>
        <input type="text" id="admission_no" name="admission_no" value="{{ old('admission_no', $student->admission_no) }}" required>

        <label for="school_class_id">Class</label>
        <select id="school_class_id" name="school_class_id" required>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected(old('school_class_id', $student->school_class_id) == $class->id)>{{ $class->displayName() }}</option>
            @endforeach
        </select>

        <label for="gender">Gender</label>
        <select id="gender" name="gender">
            <option value="">— Not specified —</option>
            <option value="male" @selected(old('gender', $student->gender) === 'male')>Male</option>
            <option value="female" @selected(old('gender', $student->gender) === 'female')>Female</option>
        </select>

        <label for="date_of_birth">Date of birth</label>
        <input type="text" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" placeholder="YYYY-MM-DD">

        <label for="guardian_phone">Guardian phone</label>
        <input type="text" id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone) }}">

        <label for="status">Status</label>
        <select id="status" name="status" required>
            @foreach (['active', 'transferred', 'graduated', 'inactive'] as $status)
                <option value="{{ $status }}" @selected(old('status', $student->status) === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn">Save changes</button>
            <a href="{{ route('teacher.students.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
