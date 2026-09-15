@extends('layouts.dashboard')

@section('title', 'Classes - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Classes')

@section('nav')
@include('principal._nav', ['active' => 'classes'])
@endsection

@section('content')

<div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

    <div style="flex:2; min-width:340px;">
        <div class="card" style="padding:0;">
            @if ($classes->isEmpty())
                <div class="empty-state">No classes yet. Add your first class using the form.</div>
            @else
                <table>
                    <thead>
                        <tr><th>Class</th><th>Stream</th><th>Class teacher</th><th>Promotes to</th><th>Students</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($classes as $class)
                            <tr>
                                <td><a href="{{ route('principal.classes.show', $class) }}" style="font-weight:600;">{{ $class->name }}</a></td>
                                <td>{{ $class->stream ?? '—' }}</td>
                                <td>{{ $class->classTeacher->name ?? '—' }}</td>
                                <td>{{ $class->promotesTo?->displayName() ?? 'Graduating class' }}</td>
                                <td>{{ $class->students_count }}</td>
                                <td style="display:flex; gap:8px;">
                                    <a href="{{ route('principal.promotion.show', $class) }}" class="btn small secondary">Promote</a>
                                    <a href="{{ route('principal.classes.edit', $class) }}" class="btn small secondary">Edit</a>
                                    <form method="POST" action="{{ route('principal.classes.destroy', $class) }}" onsubmit="return confirm('Remove this class?');">
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
            <h3 style="margin-top:0;">Add a class</h3>
            <form method="POST" action="{{ route('principal.classes.store') }}">
                @csrf
                <label for="name">Class name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Grade 5" required>

                <label for="stream">Stream (optional)</label>
                <input type="text" id="stream" name="stream" value="{{ old('stream') }}" placeholder="e.g. Blue">

                <label for="class_teacher_id">Class teacher (optional)</label>
                <select id="class_teacher_id" name="class_teacher_id">
                    <option value="">— None yet —</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(old('class_teacher_id') == $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn">Add class</button>
            </form>
            @if ($teachers->isEmpty())
                <p style="font-size:0.82rem; color:#8496a6; margin-top:12px;">
                    No teachers added yet — you can assign a class teacher later from the <a href="{{ route('principal.teachers.index') }}">Teachers</a> page.
                </p>
            @endif
        </div>
    </div>

</div>

@endsection
