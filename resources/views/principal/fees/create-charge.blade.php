@extends('layouts.dashboard')

@section('title', 'Record a Charge - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Record a charge')

@section('nav')
@include('principal._nav', ['active' => 'fees'])
@endsection

@section('content')

@if ($students->isEmpty())
    <div class="card"><div class="empty-state">No active students registered yet.</div></div>
@else
    <div class="card" style="max-width:520px;">
        <form method="POST" action="{{ route('principal.fees.charge.store') }}">
            @csrf

            <label for="student_id">Student</label>
            <select id="student_id" name="student_id" required>
                <option value="">— Select —</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->name }} — {{ $student->admission_no }} ({{ $student->schoolClass->displayName() }})</option>
                @endforeach
            </select>

            <label for="term_id">Term (optional)</label>
            <select id="term_id" name="term_id">
                <option value="">— Not tied to a specific term —</option>
                @foreach ($terms as $term)
                    <option value="{{ $term->id }}" @selected(old('term_id', $terms->firstWhere('is_current', true)?->id) == $term->id)>{{ $term->label() }}</option>
                @endforeach
            </select>

            <label for="description">Description</label>
            <input type="text" id="description" name="description" value="{{ old('description') }}" placeholder="e.g. Term 2 tuition fees" required>

            <label for="amount">Amount (KSh)</label>
            <input type="text" id="amount" name="amount" value="{{ old('amount') }}" required>

            <label for="transaction_date">Date (optional, defaults to today)</label>
            <input type="text" id="transaction_date" name="transaction_date" value="{{ old('transaction_date') }}" placeholder="YYYY-MM-DD">

            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" class="btn">Record charge</button>
                <a href="{{ route('principal.fees.index') }}" class="btn secondary">Cancel</a>
            </div>
        </form>
    </div>
@endif

@endsection
