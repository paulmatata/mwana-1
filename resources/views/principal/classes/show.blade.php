@extends('layouts.dashboard')

@section('title', $class->displayName() . ' - Mwana')
@section('role-label', 'Principal')
@section('page-title', $class->displayName())

@section('nav')
@include('principal._nav', ['active' => 'classes'])
@endsection

@php
$statusColors = [
    'draft' => '#8496a6',
    'submitted' => '#ffc107',
    'approved' => '#1B7A3D',
    'rejected' => '#dc3545',
];
@endphp

@section('content')

<div style="display:flex; gap:8px; margin-bottom:20px;">
    <a href="{{ route('principal.classes.index') }}" class="btn small secondary">&larr; All classes</a>
    <a href="{{ route('principal.classes.edit', $class) }}" class="btn small secondary">Edit class</a>
    <a href="{{ route('principal.promotion.show', $class) }}" class="btn small secondary">Promote students</a>
</div>

@if ($currentTerm)
    <div class="card" style="background:#e6f4ea; border-color:#1B7A3D; font-size:0.88rem; display:flex; justify-content:space-between; align-items:center;">
        <span>Showing exams for the current term: <strong>{{ $currentTerm->label() }}</strong></span>
        <a href="{{ route('principal.terms.index') }}" style="font-size:0.82rem;">Change current term</a>
    </div>
@else
    <div class="card" style="background:#fff8e1; border-color:#ffc107; font-size:0.88rem;">
        No current term set - showing all exams for this class. <a href="{{ route('principal.terms.index') }}">Set a current term</a> to scope this view properly.
    </div>
@endif

<div class="card" style="display:flex; gap:32px; flex-wrap:wrap;">
    <div>
        <div style="font-size:0.78rem; color:#8496a6; text-transform:uppercase;">Class teacher</div>
        <div style="font-size:1.05rem; font-weight:600;">{{ $class->classTeacher->name ?? '— Not assigned' }}</div>
    </div>
    <div>
        <div style="font-size:0.78rem; color:#8496a6; text-transform:uppercase;">Students</div>
        <div style="font-size:1.05rem; font-weight:600;">{{ $students->count() }}</div>
    </div>
    <div>
        <div style="font-size:0.78rem; color:#8496a6; text-transform:uppercase;">Subjects taught</div>
        <div style="font-size:1.05rem; font-weight:600;">{{ $assignments->count() }}</div>
    </div>
    <div>
        <div style="font-size:0.78rem; color:#8496a6; text-transform:uppercase;">Promotes to</div>
        <div style="font-size:1.05rem; font-weight:600;">{{ $class->promotesTo?->displayName() ?? 'Graduating class' }}</div>
    </div>
    <div>
        <div style="font-size:0.78rem; color:#8496a6; text-transform:uppercase;">Total fees owed</div>
        <div style="font-size:1.05rem; font-weight:700; color:{{ $totalOwed > 0 ? '#dc3545' : '#1B7A3D' }};">KSh {{ number_format($totalOwed, 2) }}</div>
    </div>
</div>

<div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

    <div style="flex:1; min-width:320px;">
        <div class="card">
            <h3 style="margin-top:0;">Teachers for this class</h3>
            @if ($assignments->isEmpty())
                <p class="empty-state" style="padding:16px 0;">No teacher-subject assignments yet.</p>
            @else
                <table>
                    <thead><tr><th>Subject</th><th>Teacher</th></tr></thead>
                    <tbody>
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->subject->name }}{{ $assignment->subject->is_core ? '' : ' (elective)' }}</td>
                                <td>{{ $assignment->teacher->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Exams {{ $currentTerm ? '— '.$currentTerm->label() : '' }}</h3>
            @if ($exams->isEmpty())
                <p class="empty-state" style="padding:16px 0;">No exams recorded for this period.</p>
            @else
                <table>
                    <thead><tr><th>Exam</th><th>Status</th><th>Average</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($exams as $row)
                            <tr>
                                <td>{{ $row['exam']->name }}</td>
                                <td>
                                    <span class="status-badge" style="background:{{ $statusColors[$row['exam']->status] }}22; color:{{ $statusColors[$row['exam']->status] }};">
                                        {{ ucfirst($row['exam']->status) }}
                                    </span>
                                </td>
                                <td>{{ $row['average'] ?? '—' }}</td>
                                <td><a href="{{ route('principal.exams.show', $row['exam']) }}" class="btn tiny secondary">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div style="flex:1; min-width:320px;">
        <div class="card">
            <h3 style="margin-top:0;">Students ({{ $students->count() }})</h3>
            @if ($students->isEmpty())
                <p class="empty-state" style="padding:16px 0;">No active students yet.</p>
            @else
                <table>
                    <thead><tr><th>Admission No.</th><th>Name</th></tr></thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td>{{ $student->admission_no }}</td>
                                <td>{{ $student->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Published timetable</h3>
            @if ($timetable->isEmpty())
                <p class="empty-state" style="padding:16px 0;">No published sessions yet.</p>
            @else
                @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                    @if ($timetable->has($day))
                        <p style="margin:14px 0 6px; font-weight:700; font-size:0.85rem;">{{ $day }}</p>
                        <table style="margin-bottom:10px;">
                            <tbody>
                                @foreach ($timetable[$day]->sortBy('start_time') as $slot)
                                    <tr>
                                        <td style="width:110px;">{{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i') }}–{{ \Illuminate\Support\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                                        <td>{{ $slot->subject->name }}</td>
                                        <td>{{ $slot->teacher->name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @endforeach
                <a href="{{ route('principal.timetable.index') }}" style="font-size:0.82rem;">Edit timetable &rarr;</a>
            @endif
        </div>
    </div>

</div>

@endsection
