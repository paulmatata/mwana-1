@extends('layouts.dashboard')

@section('title', $exam->name . ' - Mwana')
@section('role-label', 'Teacher')
@section('page-title', $exam->name)

@section('nav')
@include('teacher._nav', ['active' => 'class-exams'])
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

<p style="color:#52697d; font-size:0.92rem; margin-top:-8px; margin-bottom:20px;">
    {{ $exam->schoolClass->displayName() }} &middot; {{ $exam->academicTerm?->label() }} &middot;
    <span class="status-badge" style="background:{{ $statusColors[$exam->status] }}22; color:{{ $statusColors[$exam->status] }};">
        {{ ucfirst($exam->status) }}
    </span>
</p>

@if ($exam->status === 'rejected' && $exam->rejection_reason)
    <div class="card" style="background:#fbe7e9; border-color:#dc3545;">
        <strong>Your principal sent this back:</strong> {{ $exam->rejection_reason }}
    </div>
@endif

<div class="card" style="padding:0;">
    @if ($subjectRows->isEmpty())
        <div class="empty-state">No subjects have been assigned to this class yet.</div>
    @else
        <table>
            <thead><tr><th>Subject</th><th>Teacher</th><th>Marks entered</th><th>Subject average</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($subjectRows as $row)
                    <tr>
                        <td>{{ $row['subject']->name }}</td>
                        <td>{{ $row['teacher']->name }}</td>
                        <td>{{ $row['entered'] }} / {{ $row['total'] }}</td>
                        <td>{{ $row['average'] ?? '—' }}</td>
                        <td>
                            @if ($row['complete'])
                                <span class="status-badge status-active">Complete</span>
                            @else
                                <span class="status-badge status-suspended">Missing {{ $row['total'] - $row['entered'] }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@if ($byStudent->isNotEmpty())
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h3 style="margin:0;">Performance, ranked</h3>
            <div style="text-align:right;">
                <div style="font-size:0.75rem; color:#8496a6; text-transform:uppercase;">Class average</div>
                <div style="font-size:1.3rem; font-weight:700; font-family:'Poppins',sans-serif;">{{ $classAverage }}</div>
            </div>
        </div>
        <table>
            <thead><tr><th>Position</th><th>Student</th><th>Admission No.</th><th>Total</th><th>Average</th><th>Mean Grade</th></tr></thead>
            <tbody>
                @foreach ($byStudent as $studentId => $marks)
                    @php $student = $marks->first()->student; $summary = $studentSummaries[$studentId]; @endphp
                    <tr>
                        <td style="font-weight:700; color:#8496a6;">{{ $loop->iteration }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->admission_no }}</td>
                        <td>{{ $summary['total'] }}</td>
                        <td style="font-weight:700;">{{ $summary['average'] }}</td>
                        <td style="font-weight:700;">{{ $summary['mean_grade'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if (in_array($exam->status, ['draft', 'rejected']))
    <div class="card" style="{{ $allComplete ? 'background:#e6f4ea; border-color:#1B7A3D;' : 'background:#fff8e1; border-color:#ffc107;' }}">
        @if ($allComplete)
            <strong>All subjects have complete marks.</strong>
            <p style="margin:4px 0 14px; font-size:0.88rem;">Ready to send to your principal for approval.</p>
        @else
            <strong>Some subjects are still missing marks.</strong>
            <p style="margin:4px 0 14px; font-size:0.88rem;">
                You can still submit if needed, but it's usually worth chasing up the missing subjects
                first so parents don't see an incomplete report.
            </p>
        @endif
        <form method="POST" action="{{ route('teacher.class-exams.submit', $exam) }}" onsubmit="return confirm('Submit this exam for principal review?{{ $allComplete ? '' : ' Some subjects are still missing marks.' }}');">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn">Submit for principal review</button>
        </form>
    </div>
@endif

@endsection
