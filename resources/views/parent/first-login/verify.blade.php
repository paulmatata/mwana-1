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
    input[type="text"] { width: 100%; padding: 11px 14px; border: 1px solid #dde3ea; border-radius: 8px; font-size: 0.95rem; margin-bottom: 18px; font-family: inherit; }
    .btn-primary { width: 100%; background: var(--savanna-green); color: #fff; border: none; padding: 13px; border-radius: 8px; font-weight: 700; font-size: 0.98rem; cursor: pointer; }
    .btn-back { display: inline-block; margin-top: 14px; font-size: 0.85rem; color: #52697d; text-decoration: none; }
    .errors { background: #fbe7e9; border-left: 4px solid var(--laterite); padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; color: #7a1620; }
    .note { background: #fff8e1; border-left: 4px solid var(--sun-gold); padding: 12px 16px; border-radius: 6px; margin-top: 18px; font-size: 0.85rem; }
</style>
@endpush

@section('content')
<div class="container">
    <div class="wizard-wrap">
        <div class="steps-indicator">
            <span class="done"></span><span class="done"></span><span class="done"></span><span></span>
        </div>
        <h1>Step 3 of 4: Confirm your child</h1>
        <p class="sub">{{ $school->name }} &mdash; {{ $class->displayName() }}</p>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('parent.first-login.verify') }}">
            @csrf
            <label for="admission_no">Admission number</label>
            <input type="text" id="admission_no" name="admission_no" value="{{ old('admission_no') }}" required autofocus>

            <label for="name">Student's full name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Exactly as registered by the school">

            <button type="submit" class="btn-primary">Find my child</button>
        </form>

        <div class="note">
            This has to match the school's records exactly. If it doesn't, it means either a detail
            is off, or your child isn't registered in this class yet &mdash; contact the school directly.
        </div>

        <a href="{{ route('parent.first-login.class') }}" class="btn-back">&larr; Change class</a>
    </div>
</div>
@endsection
