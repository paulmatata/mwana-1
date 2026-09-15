@extends('layouts.dashboard')

@section('title', 'Manage Enrollment - Mwana')
@section('role-label', 'Teacher')
@section('page-title', $assignment->subject->name . ' — ' . $assignment->schoolClass->displayName())

@section('nav')
@include('teacher._nav', ['active' => 'electives'])
@endsection

@section('content')

<p style="color:#52697d; font-size:0.92rem; margin-top:-8px; margin-bottom:20px;">
    Check every student in {{ $assignment->schoolClass->displayName() }} who's actually taking
    {{ $assignment->subject->name }}. You can come back and adjust this at any time.
</p>

@if ($students->isEmpty())
    <div class="card"><div class="empty-state">No active students in this class.</div></div>
@else
    <div class="card">
        <form method="POST" action="{{ route('teacher.electives.update', $assignment) }}">
            @csrf
            @method('PUT')

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <label style="margin:0; display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" id="select-all" style="width:auto;"> Select / deselect all
                </label>
                <span style="font-size:0.85rem; color:#8496a6;">{{ $enrolledIds->count() }} of {{ $students->count() }} currently enrolled</span>
            </div>

            <table>
                <thead><tr><th style="width:40px;"></th><th>Name</th><th>Admission No.</th></tr></thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td><input type="checkbox" name="students[]" value="{{ $student->id }}" class="enroll-checkbox" style="width:auto; margin:0;" @checked($enrolledIds->contains($student->id))></td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->admission_no }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="display:flex; gap:12px; margin-top:20px;">
                <button type="submit" class="btn">Save enrollment</button>
                <a href="{{ route('teacher.electives.index') }}" class="btn secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('select-all')?.addEventListener('change', function () {
            document.querySelectorAll('.enroll-checkbox').forEach(cb => cb.checked = this.checked);
        });
    </script>
@endif

@endsection
