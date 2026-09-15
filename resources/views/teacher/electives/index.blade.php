@extends('layouts.dashboard')

@section('title', 'Elective Enrollment - Mwana')
@section('role-label', 'Teacher')
@section('page-title', 'Elective Enrollment')

@section('nav')
@include('teacher._nav', ['active' => 'electives'])
@endsection

@section('content')

<p style="color:#52697d; font-size:0.92rem; margin-top:-8px; margin-bottom:20px;">
    For each elective you teach, choose which students in the class are actually taking it.
    Only enrolled students will appear on your marks upload template for that subject.
</p>

@if ($assignments->isEmpty())
    <div class="card"><div class="empty-state">You're not assigned to any elective subjects.</div></div>
@else
    <div class="card" style="padding:0;">
        <table>
            <thead><tr><th>Subject</th><th>Class</th><th></th></tr></thead>
            <tbody>
                @foreach ($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->subject->name }}</td>
                        <td>{{ $assignment->schoolClass->displayName() }}</td>
                        <td><a href="{{ route('teacher.electives.show', $assignment) }}" class="btn small">Manage enrollment</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection
