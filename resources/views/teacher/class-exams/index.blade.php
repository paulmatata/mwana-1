@extends('layouts.dashboard')

@section('title', 'Class Exams - Mwana')
@section('role-label', 'Teacher')
@section('page-title', 'Class Exams (as Class Teacher)')

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

@if ($classes->isEmpty())
    <div class="card"><div class="empty-state">You're not set as the class teacher for any class. This page only applies if your principal has assigned you that role.</div></div>
@elseif ($exams->isEmpty())
    <div class="card"><div class="empty-state">No exam windows have been created yet for your class(es).</div></div>
@else
    <p style="color:#52697d; font-size:0.92rem; margin-top:-8px; margin-bottom:20px;">
        As class teacher, you're the one who submits these exams for principal review once every
        subject's marks are in - not each individual subject teacher.
    </p>

    <div class="card" style="padding:0;">
        <table>
            <thead><tr><th>Exam</th><th>Class</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($exams as $exam)
                    <tr>
                        <td>{{ $exam->name }}</td>
                        <td>{{ $exam->schoolClass->displayName() }}</td>
                        <td>
                            <span class="status-badge" style="background:{{ $statusColors[$exam->status] }}22; color:{{ $statusColors[$exam->status] }};">
                                {{ ucfirst($exam->status) }}
                            </span>
                        </td>
                        <td><a href="{{ route('teacher.class-exams.show', $exam) }}" class="btn small">Overview</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection
