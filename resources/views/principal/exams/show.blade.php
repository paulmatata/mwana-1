@extends('layouts.dashboard')

@section('title', $exam->name . ' - Mwana')
@section('role-label', 'Principal')
@section('page-title', $exam->name)

@section('nav')
@include('principal._nav', ['active' => 'exams'])
@endsection

@php
$statusColors = [
    'draft' => '#8496a6',
    'submitted' => '#ffc107',
    'approved' => '#1B7A3D',
    'rejected' => '#dc3545',
];
$subjects = $exam->marks->pluck('subject')->unique('id')->sortBy('name');
@endphp

@section('content')

<div class="card" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <p style="margin:0 0 4px; color:#8496a6; font-size:0.9rem;">{{ $exam->schoolClass->displayName() }} &middot; {{ $exam->academicTerm?->label() }}</p>
        <span class="status-badge" style="background:{{ $statusColors[$exam->status] }}22; color:{{ $statusColors[$exam->status] }};">
            {{ ucfirst($exam->status) }}
        </span>
    </div>

    <div style="display:flex; gap:10px;">
        @if ($exam->status === 'draft')
            <form method="POST" action="{{ route('principal.exams.submit', $exam) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn secondary">Submit for review</button>
            </form>
        @endif

        @if ($exam->status === 'submitted')
            <form method="POST" action="{{ route('principal.exams.approve', $exam) }}" onsubmit="return confirm('Approve these results? Parents will be able to view them immediately.');">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn">Approve results</button>
            </form>
            <button type="button" class="btn danger" onclick="document.getElementById('reject-form').style.display='block'">Reject</button>
        @endif
    </div>
</div>

@if ($exam->status === 'submitted')
    <div id="reject-form" class="card" style="display:none; background:#fff8e1; border-color:#ffc107;">
        <form method="POST" action="{{ route('principal.exams.reject', $exam) }}">
            @csrf
            @method('PATCH')
            <label for="rejection_reason">Reason for sending this back to the teacher</label>
            <textarea id="rejection_reason" name="rejection_reason" rows="3" required placeholder="e.g. Missing marks for 3 students in Mathematics"></textarea>
            <button type="submit" class="btn danger">Send back to teacher</button>
        </form>
    </div>
@endif

@if ($exam->status === 'rejected' && $exam->rejection_reason)
    <div class="card" style="background:#fbe7e9; border-color:#dc3545;">
        <strong>Rejection reason:</strong> {{ $exam->rejection_reason }}
    </div>
@endif

<div class="card" style="padding:0; overflow-x:auto;">
    @if ($byStudent->isEmpty())
        <div class="empty-state">No marks have been uploaded for this exam yet.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Student</th>
                    <th>Admission No.</th>
                    @foreach ($subjects as $subject)
                        <th>{{ $subject->name }}</th>
                    @endforeach
                    <th>Total</th>
                    <th>Average</th>
                    <th>Mean Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byStudent as $studentId => $marks)
                    @php $student = $marks->first()->student; $summary = $studentSummaries[$studentId]; @endphp
                    <tr>
                        <td style="font-weight:700; color:#8496a6;">{{ $loop->iteration }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->admission_no }}</td>
                        @foreach ($subjects as $subject)
                            @php $mark = $marks->firstWhere('subject_id', $subject->id); @endphp
                            <td>{{ $mark?->score ?? '—' }}{{ $mark?->grade ? ' ('.$mark->grade.')' : '' }}</td>
                        @endforeach
                        <td style="font-weight:700;">{{ $summary['total'] }}</td>
                        <td style="font-weight:700;">{{ $summary['average'] }}</td>
                        <td style="font-weight:700;">{{ $summary['mean_grade'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#eef3f8; font-weight:700;">
                    <td colspan="3">Subject average</td>
                    @foreach ($subjects as $subject)
                        <td>{{ $subjectAverages->get($subject->id) ?? '—' }}</td>
                    @endforeach
                    <td colspan="2">Class average</td>
                    <td>{{ $classAverage ?? '—' }}</td>
                </tr>
            </tfoot>
        </table>
    @endif
</div>

@endsection
