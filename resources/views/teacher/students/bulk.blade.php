@extends('layouts.dashboard')

@section('title', 'Bulk Add Students - Mwana')
@section('role-label', 'Teacher')
@section('page-title', 'Bulk Add Students')

@section('nav')
@include('teacher._nav', ['active' => 'students'])
@endsection

@section('content')

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

@if ($classes->isEmpty())
    <div class="card"><div class="empty-state">No classes exist yet at your school. Ask your principal to add one first.</div></div>
@else
    <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

        <div style="flex:1; min-width:300px;">
            <div class="card">
                <h3 style="margin-top:0;">1. Download template</h3>
                <p style="font-size:0.9rem; color:#52697d;">Pick the class, then download a blank spreadsheet ready to fill in.</p>
                <form method="GET" action="{{ route('teacher.students.bulk.template') }}">
                    <label for="template_class_id">Class</label>
                    <select id="template_class_id" name="class_id" required>
                        <option value="">— Select —</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->displayName() }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn secondary">Download template (.xlsx)</button>
                </form>
            </div>

            <div class="card">
                <h3 style="margin-top:0;">2. Upload completed sheet</h3>
                <form method="POST" action="{{ route('teacher.students.bulk.import') }}" enctype="multipart/form-data">
                    @csrf
                    <label for="school_class_id">Class</label>
                    <select id="school_class_id" name="school_class_id" required>
                        <option value="">— Select —</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->displayName() }}</option>
                        @endforeach
                    </select>

                    <label for="file">Completed spreadsheet (.xlsx)</label>
                    <input type="file" id="file" name="file" accept=".xlsx,.xls" required style="margin-bottom:16px;">

                    <button type="submit" class="btn">Upload students</button>
                </form>
                <p style="font-size:0.8rem; color:#8496a6; margin-top:12px;">
                    Make sure the class you pick here matches the class you downloaded the template for.
                    Duplicate admission numbers (either already in the system, or repeated within the file)
                    are skipped and reported, not overwritten.
                </p>
            </div>
        </div>

        <div style="flex:1; min-width:280px;">
            <div class="card">
                <h3 style="margin-top:0;">Prefer one at a time?</h3>
                <p style="font-size:0.9rem; color:#52697d;">You can still add students individually.</p>
                <a href="{{ route('teacher.students.create') }}" class="btn secondary">Add a single student</a>
            </div>
        </div>

    </div>
@endif

@endsection
