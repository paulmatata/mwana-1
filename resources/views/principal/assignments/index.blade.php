@extends('layouts.dashboard')

@section('title', 'Assignments - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Teacher Assignments')

@section('nav')
@include('principal._nav', ['active' => 'assignments'])
@endsection

@section('content')

<p style="color:#52697d; font-size:0.92rem; margin-top:-8px; margin-bottom:20px; max-width:640px;">
    This determines which teacher can upload marks for which subject, in which class &mdash; and forms the
    basis of each teacher's timetable once schedules are set.
</p>

<div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

    <div style="flex:2; min-width:340px;">
        <div class="card" style="padding:0;">
            @if ($assignments->isEmpty())
                <div class="empty-state">No assignments yet. Add one using the form.</div>
            @else
                <table>
                    <thead>
                        <tr><th>Class</th><th>Subject</th><th>Teacher</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->schoolClass->displayName() }}</td>
                                <td>
                                    {{ $assignment->subject->name }}
                                    @unless ($assignment->subject->is_core)
                                        <span style="font-size:0.75rem; color:#8496a6;">(elective)</span>
                                    @endunless
                                </td>
                                <td>{{ $assignment->teacher->name }}</td>
                                <td style="display:flex; gap:8px;">
                                    <form method="POST" action="{{ route('principal.assignments.destroy', $assignment) }}" onsubmit="return confirm('Remove this assignment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn small danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div style="flex:1; min-width:300px;">
        <div class="card">
            <h3 style="margin-top:0;">New assignment</h3>

            @if ($classes->isEmpty() || $subjects->isEmpty() || $teachers->isEmpty())
                <p style="color:#8496a6; font-size:0.9rem;">
                    You need at least one class, one subject, and one active teacher before you can create assignments.
                </p>
            @else
                <form method="POST" action="{{ route('principal.assignments.store') }}">
                    @csrf
                    <label for="school_class_id">Class</label>
                    <select id="school_class_id" name="school_class_id" required>
                        <option value="">— Select —</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->displayName() }}</option>
                        @endforeach
                    </select>

                    <label for="subject_id">Subject</label>
                    <select id="subject_id" name="subject_id" required onchange="filterTeachersBySubject()">
                        <option value="">— Select —</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}{{ $subject->is_core ? '' : ' (elective)' }}</option>
                        @endforeach
                    </select>

                    <label for="teacher_id">Teacher</label>
                    <select id="teacher_id" name="teacher_id" required>
                        <option value="">— Select —</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}"
                                data-subjects="{{ $teacher->subjectsQualifiedFor->pluck('id')->implode(',') }}"
                                @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    <p style="font-size:0.78rem; color:#8496a6; margin:-10px 0 14px;">
                        The list narrows to teachers qualified for the selected subject. Teachers with no
                        qualifications set yet still show up for any subject.
                    </p>

                    <button type="submit" class="btn">Assign</button>
                </form>

                <script>
                    function filterTeachersBySubject() {
                        const subjectId = document.getElementById('subject_id').value;
                        const teacherSelect = document.getElementById('teacher_id');
                        let currentIsValid = false;

                        Array.from(teacherSelect.options).forEach(option => {
                            if (!option.value) return; // the "— Select —" placeholder
                            const qualified = option.dataset.subjects ? option.dataset.subjects.split(',') : [];
                            const show = qualified.length === 0 || !subjectId || qualified.includes(subjectId);
                            option.hidden = !show;
                            if (show && option.selected) currentIsValid = true;
                        });

                        if (!currentIsValid) {
                            teacherSelect.value = '';
                        }
                    }
                </script>
            @endif
        </div>
    </div>

</div>

@endsection
