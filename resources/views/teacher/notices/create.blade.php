@extends('layouts.dashboard')

@section('title', 'Post a Notice - Mwana')
@section('role-label', 'Teacher')
@section('page-title', 'Post a notice')

@section('nav')
@include('teacher._nav', ['active' => 'notices'])
@endsection

@push('styles')
<style>
    .target-choice { display:flex; gap:16px; margin-bottom:18px; }
    .target-choice label { display:flex; align-items:center; gap:6px; font-weight:500; margin-bottom:0; }
</style>
@endpush

@section('content')

<div class="card" style="max-width:560px;">
    <form method="POST" action="{{ route('teacher.notices.store') }}">
        @csrf

        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="{{ old('title') }}" required>

        <label for="body">Message</label>
        <textarea id="body" name="body" rows="4" required>{{ old('body') }}</textarea>

        <label>Who should see this?</label>
        <div class="target-choice">
            <label><input type="radio" name="target" value="class" onchange="toggleTarget()" checked> One class</label>
            <label><input type="radio" name="target" value="student" onchange="toggleTarget()"> One student</label>
        </div>

        <div id="class-picker">
            <label for="school_class_id">Class</label>
            <select id="school_class_id" name="school_class_id">
                <option value="">— Select —</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->displayName() }}</option>
                @endforeach
            </select>
        </div>

        <div id="student-picker" style="display:none;">
            <label for="student_id">Student</label>
            <select id="student_id" name="student_id">
                <option value="">— Select —</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->name }} — {{ $student->admission_no }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn">Post notice</button>
            <a href="{{ route('teacher.notices.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    function toggleTarget() {
        const target = document.querySelector('input[name="target"]:checked').value;
        document.getElementById('class-picker').style.display = target === 'class' ? 'block' : 'none';
        document.getElementById('student-picker').style.display = target === 'student' ? 'block' : 'none';
        if (target !== 'class') document.getElementById('school_class_id').value = '';
        if (target !== 'student') document.getElementById('student_id').value = '';
    }
</script>

@endsection
