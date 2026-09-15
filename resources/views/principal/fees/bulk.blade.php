@extends('layouts.dashboard')

@section('title', 'Bulk Post a Charge - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Bulk post a charge')

@section('nav')
@include('principal._nav', ['active' => 'fees'])
@endsection

@section('content')

<div class="card" style="max-width:520px;">
    <p style="font-size:0.9rem; color:#52697d; margin-top:-6px;">
        Adds one debit transaction per active student - either for a single class, or the whole
        school if you leave the class field blank. Each student's running balance updates on top
        of whatever they already owed, nothing gets overwritten.
    </p>

    <form method="POST" action="{{ route('principal.fees.bulk.store') }}">
        @csrf

        <label for="school_class_id">Class (leave blank for the whole school)</label>
        <select id="school_class_id" name="school_class_id">
            <option value="">— Whole school —</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->displayName() }}</option>
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

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn">Post charge to all matching students</button>
            <a href="{{ route('principal.fees.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
