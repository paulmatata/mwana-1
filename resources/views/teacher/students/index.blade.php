@extends('layouts.dashboard')

@section('title', 'Students - Mwana')
@section('role-label', 'Teacher')
@section('page-title', 'Students')

@section('nav')
@include('teacher._nav', ['active' => 'students'])
@endsection

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; gap:12px; flex-wrap:wrap;">
    <form method="GET" style="display:flex; gap:8px; flex-wrap:wrap;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or admission no." style="width:240px; margin:0;">
        <select name="class_id" onchange="this.form.submit()" style="width:auto; margin:0;">
            <option value="">All classes</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->displayName() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn secondary small">Search</button>
    </form>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('teacher.students.bulk') }}" class="btn secondary">Bulk add</a>
        <a href="{{ route('teacher.students.create') }}" class="btn">+ Add student</a>
    </div>
</div>

<div class="card" style="padding:0;">
    @if ($students->isEmpty())
        <div class="empty-state">No students found.</div>
    @else
        <table>
            <thead><tr><th>Name</th><th>Admission No.</th><th>Class</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($students as $student)
                    <tr>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->admission_no }}</td>
                        <td>{{ $student->schoolClass->displayName() }}</td>
                        <td><span class="status-badge status-{{ $student->status === 'active' ? 'active' : 'suspended' }}">{{ ucfirst($student->status) }}</span></td>
                        <td><a href="{{ route('teacher.students.edit', $student) }}" class="btn small secondary">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div style="margin-top:20px;">
    {{ $students->links() }}
</div>

@endsection
