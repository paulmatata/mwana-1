@extends('layouts.dashboard')

@section('title', 'Timetable - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Timetable Builder')

@section('nav')
@include('principal._nav', ['active' => 'timetable'])
@endsection

@push('styles')
<style>
    .day-tabs { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:20px; }
    .day-tab-btn {
        background:#fff; border:1px solid #dde3ea; padding:8px 16px; border-radius:20px;
        font-size:0.85rem; font-weight:600; cursor:pointer; color:#52697d;
    }
    .day-tab-btn.active { background:var(--savanna-green); color:#fff; border-color:var(--savanna-green); }
    .day-panel { display:none; }
    .day-panel.active { display:block; }
    .class-block { background:#fff; border:1px solid #dde3ea; border-radius:10px; padding:18px 20px; margin-bottom:16px; }
    .class-block h4 { margin:0 0 10px; display:flex; justify-content:space-between; align-items:center; font-size:1rem; }
    .session-chip {
        display:inline-flex; align-items:center; gap:8px; background:#eef3f8; border-radius:20px;
        padding:6px 12px; font-size:0.82rem; margin:0 8px 8px 0;
    }
    .session-chip form { margin:0; }
    .session-chip button { background:none; border:none; color:#dc3545; font-weight:700; cursor:pointer; padding:0 2px; font-size:0.9rem; }
    .chip-status { font-size:0.7rem; padding:1px 7px; border-radius:10px; font-weight:700; }
    .chip-draft { background:#fff8e1; color:#a3720f; }
    .chip-final { background:#e6f4ea; color:#1B7A3D; }
    .add-session-row { display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end; margin-top:10px; padding-top:12px; border-top:1px dashed #dde3ea; }
    .add-session-row .field { display:flex; flex-direction:column; }
    .add-session-row label { font-size:0.72rem; font-weight:600; color:#8496a6; margin-bottom:3px; }
    .add-session-row select, .add-session-row input {
        margin-bottom:0; padding:7px 10px; font-size:0.85rem; min-width:100px;
    }
    .add-session-row select.err, .add-session-row input.err { border-color:#dc3545; background:#fdf1ec; }
    .field-error { color:#dc3545; font-size:0.72rem; margin-top:2px; }
    .clash-warning { color:#dc3545; font-size:0.75rem; margin-top:4px; display:none; }
    .term-bar { display:flex; align-items:center; gap:10px; margin-bottom:18px; background:#fff8e1; border:1px solid #ffc107; border-radius:8px; padding:12px 16px; }
    .term-bar input { margin:0; max-width:260px; }
</style>
@endpush

@section('content')

<div class="term-bar">
    <label for="shared-term" style="margin:0; font-weight:700; font-size:0.85rem;">Term for new sessions:</label>
    <select id="shared-term" style="margin:0; max-width:260px;">
        <option value="">— Not tied to a specific term —</option>
        @foreach ($terms as $term)
            <option value="{{ $term->id }}" @selected($term->is_current)>{{ $term->label() }}{{ $term->is_current ? ' (current)' : '' }}</option>
        @endforeach
    </select>
    @if ($terms->isEmpty())
        <span style="font-size:0.78rem; color:#6b5300;">No terms set up yet - <a href="{{ route('principal.terms.index') }}">add one</a> to tag sessions properly.</span>
    @else
        <span style="font-size:0.78rem; color:#6b5300;">Applied to every session you add below.</span>
    @endif
</div>

@if ($classes->isEmpty())
    <div class="card"><div class="empty-state">Add a class first before building a timetable.</div></div>
@else
    <div class="day-tabs" id="day-tabs">
        @foreach ($days as $day)
            <button type="button" class="day-tab-btn" data-day="{{ $day }}">{{ $day }}</button>
        @endforeach
    </div>

    @foreach ($days as $day)
        <div class="day-panel" id="panel-{{ $day }}">
            @foreach ($classes as $class)
                @php
                    $daySlots = $slots->get($day, collect())->get($class->id, collect())->sortBy('start_time');
                    $classAssignments = $assignmentsByClass->get($class->id, collect());
                @endphp
                <div class="class-block">
                    <h4>
                        <span>{{ $class->displayName() }}</span>
                        <form method="POST" action="{{ route('principal.timetable.publish', $class) }}" onsubmit="return confirm('Publish {{ $class->displayName() }}\'s entire timetable? All draft sessions across every day become visible to teachers.');" style="margin:0;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn small secondary">Publish this class's timetable</button>
                        </form>
                    </h4>

                    @forelse ($daySlots as $slot)
                        <span class="session-chip">
                            {{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i') }}–{{ \Illuminate\Support\Carbon::parse($slot->end_time)->format('H:i') }}
                            &middot; {{ $slot->subject->name }} ({{ $slot->teacher->name }})
                            @if ($slot->room) &middot; {{ $slot->room }} @endif
                            <span class="chip-status {{ $slot->status === 'final' ? 'chip-final' : 'chip-draft' }}">{{ ucfirst($slot->status) }}</span>
                            <form method="POST" action="{{ route('principal.timetable.destroy', $slot) }}" onsubmit="return confirm('Remove this session?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Remove">&times;</button>
                            </form>
                        </span>
                    @empty
                        <p style="font-size:0.82rem; color:#8496a6; margin:0;">No sessions yet for {{ $day }}.</p>
                    @endforelse

                    @if ($classAssignments->isEmpty())
                        <p style="font-size:0.78rem; color:#8496a6; margin-top:10px;">
                            No teacher-subject assignments for this class yet -
                            <a href="{{ route('principal.assignments.index') }}">set those up first</a>.
                        </p>
                    @else
                        @php
                            $isThisForm = old('day_of_week') === $day && (int) old('submitted_class_id') === $class->id;
                        @endphp
                        <form method="POST" action="{{ route('principal.timetable.store') }}" class="add-session-form" data-day="{{ $day }}" data-class="{{ $class->id }}">
                            @csrf
                            <input type="hidden" name="day_of_week" value="{{ $day }}">
                            <input type="hidden" name="submitted_class_id" value="{{ $class->id }}">
                            <input type="hidden" name="term_id" class="term-input">

                            <div class="add-session-row">
                                <div class="field">
                                    <label>Subject &amp; teacher</label>
                                    <select name="assignment_id" class="assignment-select" required>
                                        <option value="">— Select —</option>
                                        @foreach ($classAssignments as $a)
                                            <option value="{{ $a->id }}" data-teacher="{{ $a->teacher_id }}" data-core="{{ $a->subject->is_core ? '1' : '0' }}"
                                                @selected($isThisForm && old('assignment_id') == $a->id)>
                                                {{ $a->subject->name }}{{ $a->subject->is_core ? '' : ' (elective)' }} — {{ $a->teacher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($isThisForm) <div class="field-error">{{ $errors->first('assignment_id') }}</div> @endif
                                </div>
                                <div class="field">
                                    <label>Start</label>
                                    <input type="text" name="start_time" class="start-input" placeholder="08:00" value="{{ $isThisForm ? old('start_time') : '' }}">
                                    @if ($isThisForm) <div class="field-error">{{ $errors->first('start_time') }}</div> @endif
                                </div>
                                <div class="field">
                                    <label>End</label>
                                    <input type="text" name="end_time" class="end-input" placeholder="08:40" value="{{ $isThisForm ? old('end_time') : '' }}">
                                    @if ($isThisForm) <div class="field-error">{{ $errors->first('end_time') }}</div> @endif
                                </div>
                                <div class="field">
                                    <label>Room</label>
                                    <input type="text" name="room" class="room-input" style="min-width:70px;" value="{{ $isThisForm ? old('room') : '' }}">
                                </div>
                                <div class="field">
                                    <button type="submit" class="btn small">+ Add session</button>
                                </div>
                            </div>
                            <div class="clash-warning"></div>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
@endif

<script>
    const teacherBookings = @json($teacherBookings);
    const classBookings = @json($classBookings);

    // --- Day tab switching, remembered across page reloads ---
    const tabs = document.querySelectorAll('.day-tab-btn');
    const panels = document.querySelectorAll('.day-panel');

    function activateDay(day) {
        tabs.forEach(t => t.classList.toggle('active', t.dataset.day === day));
        panels.forEach(p => p.classList.toggle('active', p.id === 'panel-' + day));
        localStorage.setItem('mwana_active_day', day);
    }

    tabs.forEach(t => t.addEventListener('click', () => activateDay(t.dataset.day)));

    const serverActiveDay = @json(session('active_day'));
    const remembered = localStorage.getItem('mwana_active_day');
    const firstDay = tabs.length ? tabs[0].dataset.day : null;
    activateDay(serverActiveDay || remembered || firstDay);

    // --- Shared term selector copied into every add-session form on submit ---
    const sharedTerm = document.getElementById('shared-term');
    document.querySelectorAll('.add-session-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            form.querySelector('.term-input').value = sharedTerm.value;

            if (!validateForm(form)) {
                e.preventDefault();
            } else {
                activateDay(form.dataset.day);
            }
        });
    });

    // --- Client-side validation: required fields, time format, end>start, live clash preview ---
    function validateForm(form) {
        let valid = true;
        const assignmentSelect = form.querySelector('.assignment-select');
        const startInput = form.querySelector('.start-input');
        const endInput = form.querySelector('.end-input');
        const clashWarning = form.querySelector('.clash-warning');
        const timePattern = /^([01]\d|2[0-3]):[0-5]\d$/;

        [assignmentSelect, startInput, endInput].forEach(el => el.classList.remove('err'));
        clashWarning.style.display = 'none';

        if (!assignmentSelect.value) {
            assignmentSelect.classList.add('err');
            valid = false;
        }
        if (!timePattern.test(startInput.value)) {
            startInput.classList.add('err');
            valid = false;
        }
        if (!timePattern.test(endInput.value)) {
            endInput.classList.add('err');
            valid = false;
        }
        if (valid && endInput.value <= startInput.value) {
            endInput.classList.add('err');
            clashWarning.textContent = 'End time must be after start time.';
            clashWarning.style.display = 'block';
            valid = false;
        }

        if (valid) {
            const teacherId = assignmentSelect.selectedOptions[0]?.dataset.teacher;
            const day = form.dataset.day;
            const bookings = (teacherBookings[teacherId] || []);
            const clash = bookings.some(b => b.day === day && startInput.value < b.end && endInput.value > b.start);
            if (clash) {
                clashWarning.textContent = 'This teacher already has another session at this time on ' + day + '.';
                clashWarning.style.display = 'block';
                startInput.classList.add('err');
                valid = false;
            }
        }

        // Class-level check: this class can't take two subjects at once unless
        // every overlapping subject is an elective - and even then, rooms must
        // differ so it's clear where each group of students goes.
        if (valid) {
            const classId = form.dataset.class;
            const day = form.dataset.day;
            const isCore = assignmentSelect.selectedOptions[0]?.dataset.core === '1';
            const roomInput = form.querySelector('.room-input');
            const overlapping = (classBookings[classId] || []).filter(
                b => b.day === day && startInput.value < b.end && endInput.value > b.start
            );

            if (overlapping.length > 0) {
                const anyCore = isCore || overlapping.some(b => b.isCore);
                if (anyCore) {
                    clashWarning.textContent = 'This class already has ' + (overlapping[0].isCore ? 'a core subject' : 'another subject') + ' scheduled at this time. Two subjects can only overlap if both are electives.';
                    clashWarning.style.display = 'block';
                    assignmentSelect.classList.add('err');
                    valid = false;
                } else if (!roomInput.value) {
                    clashWarning.textContent = 'These are overlapping electives - enter a room so it\'s clear where students taking this one should go.';
                    clashWarning.style.display = 'block';
                    roomInput.classList.add('err');
                    valid = false;
                } else if (overlapping.some(b => b.room === roomInput.value)) {
                    clashWarning.textContent = 'Another elective at this same time is already using that room - pick a different one.';
                    clashWarning.style.display = 'block';
                    roomInput.classList.add('err');
                    valid = false;
                }
            }
        }

        return valid;
    }

    // Live-check as the person types, so the warning appears before they even try to submit.
    document.querySelectorAll('.add-session-form').forEach(form => {
        ['change', 'blur'].forEach(evt => {
            form.querySelectorAll('.assignment-select, .start-input, .end-input, .room-input').forEach(el => {
                el.addEventListener(evt, () => validateForm(form));
            });
        });
    });
</script>

@endsection
