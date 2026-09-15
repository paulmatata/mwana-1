@extends('layouts.dashboard')

@section('title', 'Marks Upload - Mwana')
@section('role-label', 'Teacher')
@section('page-title', 'Marks Upload')

@section('nav')
@include('teacher._nav', ['active' => 'marks'])
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

@if ($assignments->isEmpty())
    <div class="card"><div class="empty-state">You haven't been assigned to any class/subject yet. Contact your principal.</div></div>
@elseif ($exams->isEmpty())
    <div class="card"><div class="empty-state">No exam windows have been created yet for your classes. Your principal needs to create one first.</div></div>
@else
    <p style="color:#52697d; font-size:0.92rem; margin-top:-8px; margin-bottom:20px; max-width:640px;">
        Pick which subject and exam you want to upload marks for. You'll be able to download a
        ready-made spreadsheet with your students' admission numbers and names already filled in.
    </p>

    <div class="card" style="padding:0;">
        <table>
            <thead><tr><th>Exam</th><th>Class</th><th>Status</th><th>Your subject(s)</th><th></th></tr></thead>
            <tbody>
                @foreach ($exams as $exam)
                    @php $classAssignments = $assignments->where('school_class_id', $exam->school_class_id); @endphp
                    @foreach ($classAssignments as $assignment)
                        <tr>
                            <td>{{ $exam->name }}</td>
                            <td>{{ $exam->schoolClass->displayName() }}</td>
                            <td>
                                <span class="status-badge" style="background:{{ $statusColors[$exam->status] }}22; color:{{ $statusColors[$exam->status] }};">
                                    {{ ucfirst($exam->status) }}
                                </span>
                            </td>
                            <td>{{ $assignment->subject->name }}</td>
                            <td>
                                @if ($exam->status === 'approved')
                                    <span style="font-size:0.82rem; color:#8496a6;">Locked — already approved</span>
                                @else
                                    <a href="{{ route('teacher.marks.show', [$assignment, $exam]) }}" class="btn small">Upload / review</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection
