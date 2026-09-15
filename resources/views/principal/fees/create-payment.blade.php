@extends('layouts.dashboard')

@section('title', 'Record a Payment - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Record a payment — ' . $student->name)

@section('nav')
@include('principal._nav', ['active' => 'fees'])
@endsection

@section('content')

<div class="card" style="max-width:520px;">
    <p style="font-size:0.88rem; color:#8496a6; margin-top:-6px;">
        Current balance: <strong>KSh {{ number_format($student->currentFeeBalance(), 2) }}</strong>
    </p>

    <form method="POST" action="{{ route('principal.fees.payment.store', $student) }}">
        @csrf

        <label for="term_id">Term (optional)</label>
        <select id="term_id" name="term_id">
            <option value="">— Not tied to a specific term —</option>
            @foreach ($terms as $term)
                <option value="{{ $term->id }}" @selected(old('term_id', $terms->firstWhere('is_current', true)?->id) == $term->id)>{{ $term->label() }}</option>
            @endforeach
        </select>

        <label for="description">Description (optional)</label>
        <input type="text" id="description" name="description" value="{{ old('description') }}" placeholder="e.g. Payment received - M-Pesa">

        <label for="amount">Amount paid (KSh)</label>
        <input type="text" id="amount" name="amount" value="{{ old('amount') }}" required>

        <label for="transaction_date">Date (optional, defaults to today)</label>
        <input type="text" id="transaction_date" name="transaction_date" value="{{ old('transaction_date') }}" placeholder="YYYY-MM-DD">

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn">Record payment</button>
            <a href="{{ route('principal.fees.ledger', $student) }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
