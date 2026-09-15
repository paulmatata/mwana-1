@extends('layouts.dashboard')

@section('title', $student->name . ' - Notices - Mwana')
@section('role-label', 'Parent')
@section('page-title', $student->name . ' — Notices')

@section('nav')
@include('parent._nav', ['active' => 'dashboard'])
@endsection

@section('content')

<div style="display:flex; gap:8px; margin-bottom:20px;">
    <a href="{{ route('parent.dashboard') }}" class="btn small secondary">&larr; All children</a>
    <a href="{{ route('parent.results', $student) }}" class="btn small secondary">Results</a>
    <a href="{{ route('parent.fees', $student) }}" class="btn small secondary">Fees</a>
</div>

@if ($notices->isEmpty())
    <div class="card"><div class="empty-state">No notices yet.</div></div>
@else
    @foreach ($notices as $notice)
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <h3 style="margin:0 0 6px;">{{ $notice->title }}</h3>
                <span style="font-size:0.8rem; color:#8496a6; white-space:nowrap;">{{ $notice->created_at->format('d M Y') }}</span>
            </div>
            <p style="margin:0; color:#3a453d; font-size:0.92rem;">{{ $notice->body }}</p>
            <p style="margin:10px 0 0; font-size:0.8rem; color:#8496a6;">— {{ $notice->postedBy->name }}</p>
        </div>
    @endforeach
@endif

@endsection
