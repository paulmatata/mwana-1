@extends('layouts.dashboard')

@section('title', 'Teacher Dashboard - Mwana')
@section('role-label', 'Teacher')
@section('page-title', 'Overview')

@section('nav')
@include('teacher._nav', ['active' => 'dashboard'])
@endsection

@section('content')

@include('partials.insights-list')

@if ($classTeacherOf->isNotEmpty())
    <div class="card" style="background:#e6f4ea; border-color:#1B7A3D;">
        <h3 style="margin-top:0;">You're the class teacher for:</h3>
        <ul style="margin:0; padding-left:20px;">
            @foreach ($classTeacherOf as $class)
                <li>{{ $class->displayName() }}</li>
            @endforeach
        </ul>
        <a href="{{ route('teacher.class-exams.index') }}" class="btn small" style="margin-top:12px;">Review &amp; submit exams</a>
    </div>
@endif

<div class="card">
    <h3 style="margin-top:0;">Today's classes ({{ now()->format('l') }})</h3>
    @if ($todaysClasses->isEmpty())
        <div class="empty-state">Nothing on your timetable for today.</div>
    @else
        <table>
            <thead><tr><th>Time</th><th>Class</th><th>Subject</th></tr></thead>
            <tbody>
                @foreach ($todaysClasses as $slot)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i') }}–{{ \Illuminate\Support\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                        <td>{{ $slot->schoolClass->displayName() }}</td>
                        <td>{{ $slot->subject->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h3 style="margin:0;">Your subject assignments</h3>
        <a href="{{ route('teacher.marks.index') }}" class="btn small secondary">Go to marks upload</a>
    </div>
    @if ($assignments->isEmpty())
        <div class="empty-state">You haven't been assigned to any class/subject yet. Contact your principal.</div>
    @else
        <table>
            <thead><tr><th>Class</th><th>Subject</th></tr></thead>
            <tbody>
                @foreach ($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->schoolClass->displayName() }}</td>
                        <td>{{ $assignment->subject->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
