@extends('layouts.dashboard')

@section('title', 'Edit Class - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Edit ' . $class->displayName())

@section('nav')
@include('principal._nav', ['active' => 'classes'])
@endsection

@section('content')

<div class="card" style="max-width:500px;">
    <form method="POST" action="{{ route('principal.classes.update', $class) }}">
        @csrf
        @method('PUT')

        <label for="name">Class name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $class->name) }}" required>

        <label for="stream">Stream (optional)</label>
        <input type="text" id="stream" name="stream" value="{{ old('stream', $class->stream) }}">

        <label for="class_teacher_id">Class teacher</label>
        <select id="class_teacher_id" name="class_teacher_id">
            <option value="">— None —</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected(old('class_teacher_id', $class->class_teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
            @endforeach
        </select>

        <label for="promotes_to_class_id">Promotes to (at year-end)</label>
        <select id="promotes_to_class_id" name="promotes_to_class_id">
            <option value="">— This is a graduating class (no promotion) —</option>
            @foreach ($otherClasses as $other)
                <option value="{{ $other->id }}" @selected(old('promotes_to_class_id', $class->promotes_to_class_id) == $other->id)>{{ $other->displayName() }}</option>
            @endforeach
        </select>
        <p style="font-size:0.8rem; color:#8496a6; margin:-10px 0 14px;">
            When you promote students out of this class at year-end, they'll move into whichever class
            you pick here. Leave as "graduating class" if this is the school's final grade.
        </p>

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn">Save changes</button>
            <a href="{{ route('principal.classes.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
