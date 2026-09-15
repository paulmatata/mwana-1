@extends('layouts.dashboard')

@section('title', 'Exams & Results - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Exams & Results')

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
@endphp

@section('content')

@if (session('skipped_classes') && count(session('skipped_classes')))
    <div class="card" style="background:#fff8e1; border-color:#ffc107;">
        <strong>Skipped (no class teacher assigned yet):</strong>
        <ul style="margin:8px 0 0; padding-left:20px; font-size:0.88rem;">
            @foreach (session('skipped_classes') as $className)
                <li>{{ $className }}</li>
            @endforeach
        </ul>
        <p style="margin:8px 0 0; font-size:0.85rem;">
            Assign a class teacher from <a href="{{ route('principal.classes.index') }}">Classes</a>, then try again.
        </p>
    </div>
@endif

<div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

    <div style="flex:2; min-width:340px;">
        <div class="card" style="padding:0;">
            @if ($exams->isEmpty())
                <div class="empty-state">No exam windows yet. Create one using the form &mdash; teachers will then be able to upload marks against it.</div>
            @else
                <table>
                    <thead>
                        <tr><th>Exam</th><th>Class</th><th>Term</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($exams as $exam)
                            <tr>
                                <td>{{ $exam->name }}</td>
                                <td>{{ $exam->schoolClass->displayName() }}</td>
                                <td>{{ $exam->academicTerm?->label() ?? '—' }}</td>
                                <td>
                                    <span class="status-badge" style="background:{{ $statusColors[$exam->status] }}22; color:{{ $statusColors[$exam->status] }};">
                                        {{ ucfirst($exam->status) }}
                                    </span>
                                </td>
                                <td><a href="{{ route('principal.exams.show', $exam) }}" class="btn small secondary">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div style="flex:1; min-width:300px;">
        <div class="card">
            <h3 style="margin-top:0;">New exam window</h3>
            <p style="font-size:0.85rem; color:#8496a6; margin-top:-6px;">
                Creates one exam per class you select - each is reviewed and approved separately,
                but you only need to fill this form out once.
            </p>

            @if ($classes->isEmpty())
                <p style="color:#8496a6; font-size:0.9rem;">Add a class first before creating an exam window.</p>
            @else
                <form method="POST" action="{{ route('principal.exams.store') }}" id="exam-form">
                    @csrf

                    <label style="display:flex; align-items:center; gap:8px; font-weight:500;">
                        <input type="checkbox" id="whole_school" name="whole_school" value="1" style="width:auto; margin:0;" onchange="toggleClassList()">
                        Apply to the whole school
                    </label>

                    <div id="class-list" style="max-height:180px; overflow-y:auto; border:1px solid #dde3ea; border-radius:8px; padding:10px 14px; margin:12px 0 18px;">
                        @foreach ($classes as $class)
                            <label style="display:flex; align-items:center; gap:8px; font-weight:400; margin-bottom:8px;">
                                <input type="checkbox" name="school_class_ids[]" value="{{ $class->id }}" class="class-checkbox" style="width:auto; margin:0;">
                                {{ $class->displayName() }}
                                @unless ($class->class_teacher_id)
                                    <span style="font-size:0.75rem; color:#dc3545;">(no class teacher)</span>
                                @endunless
                            </label>
                        @endforeach
                    </div>

                    <label for="name">Exam name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Term 2 End-Term Exam 2026" required>

                    <label for="term_id">Term (optional)</label>
                    <select id="term_id" name="term_id">
                        <option value="">— Not tied to a specific term —</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" @selected(old('term_id', $terms->firstWhere('is_current', true)?->id) == $term->id)>{{ $term->label() }}{{ $term->is_current ? ' (current)' : '' }}</option>
                        @endforeach
                    </select>
                    @if ($terms->isEmpty())
                        <p style="font-size:0.78rem; color:#8496a6; margin:-10px 0 14px;">No terms set up yet - <a href="{{ route('principal.terms.index') }}">add one</a> to tag exams properly.</p>
                    @endif

                    <label for="exam_date">Exam date (optional)</label>
                    <input type="text" id="exam_date" name="exam_date" value="{{ old('exam_date') }}" placeholder="YYYY-MM-DD">

                    <button type="submit" class="btn">Create exam window(s)</button>
                </form>

                <script>
                    function toggleClassList() {
                        const wholeSchool = document.getElementById('whole_school').checked;
                        document.getElementById('class-list').style.opacity = wholeSchool ? '0.4' : '1';
                        document.querySelectorAll('.class-checkbox').forEach(cb => cb.disabled = wholeSchool);
                    }
                </script>
            @endif
        </div>
    </div>

</div>

@endsection
