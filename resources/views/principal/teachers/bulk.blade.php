@extends('layouts.dashboard')

@section('title', 'Bulk Add Teachers - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Bulk Add Teachers')

@section('nav')
@include('principal._nav', ['active' => 'teachers'])
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

<div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

    <div style="flex:1; min-width:300px;">
        <div class="card">
            <h3 style="margin-top:0;">1. Download template</h3>
            <p style="font-size:0.9rem; color:#52697d;">One row per teacher: name, and email or phone (or both).</p>
            <a href="{{ route('principal.teachers.bulk.template') }}" class="btn secondary">Download template (.xlsx)</a>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">2. Upload completed sheet</h3>
            <form method="POST" action="{{ route('principal.teachers.bulk.import') }}" enctype="multipart/form-data">
                @csrf
                <label for="file">Completed spreadsheet (.xlsx)</label>
                <input type="file" id="file" name="file" accept=".xlsx,.xls" required style="margin-bottom:16px;">
                <button type="submit" class="btn">Upload teachers</button>
            </form>
            <p style="font-size:0.8rem; color:#8496a6; margin-top:12px;">
                Every account created gets an auto-generated password, shown together on the next
                page so you can hand them all out at once.
            </p>
        </div>
    </div>

    <div style="flex:1; min-width:280px;">
        <div class="card">
            <h3 style="margin-top:0;">Prefer one at a time?</h3>
            <p style="font-size:0.9rem; color:#52697d;">You can still add a single teacher from the main Teachers page.</p>
            <a href="{{ route('principal.teachers.index') }}" class="btn secondary">Back to Teachers</a>
        </div>
    </div>

</div>

@endsection
