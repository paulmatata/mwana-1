@extends('layouts.app')

@section('title', 'Find Your Child - Mwana')

@push('styles')
<style>
    .wizard-wrap { max-width: 480px; margin: 56px auto; background: #fff; border: 1px solid #dde3ea; border-radius: 12px; padding: 36px; }
    .steps-indicator { display: flex; gap: 6px; margin-bottom: 24px; }
    .steps-indicator span { flex: 1; height: 4px; border-radius: 2px; background: #dde3ea; }
    .steps-indicator span.done { background: var(--savanna-green); }
    .wizard-wrap h1 { font-size: 1.4rem; margin: 0 0 6px; }
    .wizard-wrap p.sub { color: #52697d; margin: 0 0 24px; font-size: 0.92rem; }
    label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; }
    select, input[type="text"] { width: 100%; padding: 11px 14px; border: 1px solid #dde3ea; border-radius: 8px; font-size: 0.95rem; margin-bottom: 18px; font-family: inherit; }
    .btn-primary { width: 100%; background: var(--savanna-green); color: #fff; border: none; padding: 13px; border-radius: 8px; font-weight: 700; font-size: 0.98rem; cursor: pointer; }
    .btn-back { display: inline-block; margin-top: 14px; font-size: 0.85rem; color: #52697d; text-decoration: none; }
    .errors { background: #fbe7e9; border-left: 4px solid var(--laterite); padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; color: #7a1620; }
    .empty-note { text-align: center; color: #8496a6; font-size: 0.9rem; padding: 20px 0; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="wizard-wrap">
        <div class="steps-indicator">
            <span class="done"></span><span class="done"></span><span></span><span></span>
        </div>
        <h1>Step 2 of 4: Select the class</h1>
        <p class="sub">{{ $school->name }} &mdash; choose your child's current class.</p>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        @if ($classes->isEmpty())
            <p class="empty-note">This school hasn't set up any classes yet.</p>
        @else
            <form method="POST" action="{{ route('parent.first-login.class') }}">
                @csrf
                <label for="school_class_id">Class</label>
                <select id="school_class_id" name="school_class_id" required>
                    <option value="">— Select a class —</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->displayName() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary">Continue</button>
            </form>
        @endif

        <a href="{{ route('parent.first-login.school') }}" class="btn-back">&larr; Change school</a>
    </div>
</div>
@endsection
