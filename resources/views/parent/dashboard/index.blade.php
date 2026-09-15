@extends('layouts.dashboard')

@section('title', 'My Children - Mwana')
@section('role-label', 'Parent')
@section('page-title', 'My Children')

@section('nav')
@include('parent._nav', ['active' => 'dashboard'])
@endsection

@section('content')

@if ($children->isEmpty())
    <div class="card">
        <div class="empty-state">
            No children linked to your account yet.
            <br><br>
            <a href="{{ route('parent.first-login.school') }}" class="btn small">Link a child</a>
        </div>
    </div>
@else
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">
        @foreach ($children as $entry)
            @php $student = $entry['student']; @endphp
            <div class="card">
                <h3 style="margin:0 0 4px;">{{ $student->name }}</h3>
                <p style="margin:0 0 16px; color:#8496a6; font-size:0.88rem;">
                    {{ $student->school->name }} &middot; {{ $student->schoolClass->displayName() }} &middot; Adm. No. {{ $student->admission_no }}
                </p>

                <div style="display:flex; gap:20px; margin-bottom:18px;">
                    <div>
                        <div style="font-size:0.78rem; color:#8496a6; text-transform:uppercase;">Latest results</div>
                        <div style="font-size:0.92rem; font-weight:600;">
                            {{ $entry['latest_exam']->name ?? 'None approved yet' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:0.78rem; color:#8496a6; text-transform:uppercase;">Fee balance</div>
                        <div style="font-size:0.92rem; font-weight:600; color:{{ $entry['fee_balance'] > 0 ? '#dc3545' : '#1B7A3D' }};">
                            KSh {{ number_format($entry['fee_balance'], 2) }}
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="{{ route('parent.results', $student) }}" class="btn small secondary">Results</a>
                    <a href="{{ route('parent.fees', $student) }}" class="btn small secondary">Fees</a>
                    <a href="{{ route('parent.notices', $student) }}" class="btn small secondary">Notices</a>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
