@extends('layouts.dashboard')

@section('title', 'Edit Mark - Mwana')
@section('role-label', 'Teacher')
@section('page-title', 'Edit mark — ' . $student->name)

@section('nav')
@include('teacher._nav', ['active' => 'marks'])
@endsection

@section('content')

<p style="color:#52697d; font-size:0.92rem; margin-top:-8px; margin-bottom:20px;">
    {{ $assignment->subject->name }} &middot; {{ $exam->name }} &middot; {{ $assignment->schoolClass->displayName() }}
</p>

<div class="card" style="max-width:460px;">
    <p style="font-size:0.9rem; color:#8496a6; margin-top:-6px;">
        Admission No. {{ $student->admission_no }}
        @if ($mark)
            &middot; Current: {{ $mark->score }} ({{ $mark->grade }})
        @else
            &middot; No mark recorded yet
        @endif
    </p>

    <form method="POST" action="{{ route('teacher.marks.update-mark', [$assignment, $exam, $student]) }}">
        @csrf
        @method('PUT')

        <label for="score">Score (out of 100)</label>
        <input type="text" id="score" name="score" value="{{ old('score', $mark?->score) }}" required autofocus>

        <label for="remarks">Remarks (optional)</label>
        <textarea id="remarks" name="remarks" rows="3">{{ old('remarks', $mark?->remarks) }}</textarea>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn">Save mark</button>
            <a href="{{ route('teacher.marks.show', [$assignment, $exam]) }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
