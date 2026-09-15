@extends('layouts.dashboard')

@section('title', 'Upload Marks - Mwana')
@section('role-label', 'Teacher')
@section('page-title', $assignment->subject->name . ' — ' . $exam->name)

@section('nav')
@include('teacher._nav', ['active' => 'marks'])
@endsection

@section('content')

<p style="color:#52697d; font-size:0.92rem; margin-top:-8px; margin-bottom:20px;">
    {{ $assignment->schoolClass->displayName() }} &middot; {{ $exam->academicTerm?->label() }}
</p>

@if (in_array($exam->status, ['draft', 'rejected']))
    <div class="card" style="background:#fff8e1; border-color:#ffc107;">
        @if ($assignment->schoolClass->class_teacher_id === auth()->id())
            <strong>You're this class's class teacher.</strong>
            <p style="margin:4px 0 0; font-size:0.88rem;">
                Once every subject's marks are in, submit this exam for principal review from the
                <a href="{{ route('teacher.class-exams.show', $exam) }}">class exam overview</a> page,
                which shows what's still missing across all subjects.
            </p>
        @else
            <strong>This exam is submitted by the class teacher, not individual subject teachers.</strong>
            <p style="margin:4px 0 0; font-size:0.88rem;">
                Once you've finished uploading marks here, let {{ $assignment->schoolClass->classTeacher->name ?? 'the class teacher' }}
                know so they can review all subjects and submit for approval.
            </p>
        @endif
    </div>
@endif

@if (session('skipped_rows') && count(session('skipped_rows')))
    <div class="card" style="background:#fff8e1; border-color:#ffc107;">
        <strong>Some rows were skipped:</strong>
        <ul style="margin:8px 0 0; padding-left:20px; font-size:0.88rem;">
            @foreach (session('skipped_rows') as $issue)
                <li>{{ $issue }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

    <div style="flex:1; min-width:300px;">
        <div class="card">
            <h3 style="margin-top:0;">1. Download template</h3>
            <p style="font-size:0.9rem; color:#52697d;">
                A spreadsheet with each student's admission number and name already filled in
                for {{ $assignment->schoolClass->displayName() }}. Just fill in the score column.
            </p>
            <a href="{{ route('teacher.marks.template', [$assignment, $exam]) }}" class="btn secondary">Download template (.xlsx)</a>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">2. Upload completed sheet</h3>
            @if (in_array($exam->status, ['submitted', 'approved']))
                <p style="font-size:0.9rem; color:#8496a6;">
                    This exam is currently <strong>{{ $exam->status }}</strong> and can't accept new uploads right now
                    @if ($exam->status === 'submitted')
                        — it's waiting on your principal's review.
                    @endif
                </p>
            @else
                <form method="POST" action="{{ route('teacher.marks.store', [$assignment, $exam]) }}" enctype="multipart/form-data">
                    @csrf
                    <label for="file">Completed spreadsheet (.xlsx)</label>
                    <input type="file" id="file" name="file" accept=".xlsx,.xls" required style="margin-bottom:16px;">
                    <button type="submit" class="btn">Upload marks</button>
                </form>
                <p style="font-size:0.8rem; color:#8496a6; margin-top:12px;">
                    Only admission numbers matching students in this class are accepted. Blank score cells
                    are skipped, not overwritten &mdash; safe to re-upload as marking progresses.
                </p>
            @endif
        </div>
    </div>

    <div style="flex:2; min-width:340px;">
        <div class="card" style="padding:0;">
            <table>
                <thead><tr><th>Admission No.</th><th>Name</th><th>Score</th><th>Grade</th><th></th></tr></thead>
                <tbody>
                    @forelse ($students as $student)
                        @php $mark = $existingMarks->get($student->id); @endphp
                        <tr>
                            <td>{{ $student->admission_no }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $mark->score ?? '—' }}</td>
                            <td>{{ $mark->grade ?? '—' }}</td>
                            <td>
                                @if (in_array($exam->status, ['draft', 'rejected']))
                                    <a href="{{ route('teacher.marks.edit-mark', [$assignment, $exam, $student]) }}" class="btn tiny secondary">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">No active students in this class yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
