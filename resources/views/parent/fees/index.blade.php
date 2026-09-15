@extends('layouts.dashboard')

@section('title', $student->name . ' - Fees - Mwana')
@section('role-label', 'Parent')
@section('page-title', $student->name . ' — Fee Statement')

@section('nav')
@include('parent._nav', ['active' => 'dashboard'])
@endsection

@section('content')

<div style="display:flex; gap:8px; margin-bottom:20px;">
    <a href="{{ route('parent.dashboard') }}" class="btn small secondary">&larr; All children</a>
    <a href="{{ route('parent.results', $student) }}" class="btn small secondary">Results</a>
    <a href="{{ route('parent.notices', $student) }}" class="btn small secondary">Notices</a>
</div>

<div class="card" style="background:#fff8e1; border-color:#ffc107; font-size:0.88rem;">
    Mwana displays fee information only. Payments are made directly to {{ $student->school->name }},
    not through this portal.
</div>

<div class="card" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <div style="font-size:0.78rem; color:#8496a6; text-transform:uppercase;">Current balance</div>
        <div style="font-size:1.6rem; font-family:'Poppins',sans-serif; font-weight:700; color:{{ $student->currentFeeBalance() > 0 ? '#dc3545' : '#1B7A3D' }};">
            KSh {{ number_format($student->currentFeeBalance(), 2) }}
        </div>
    </div>
</div>

@if ($transactions->isEmpty())
    <div class="card"><div class="empty-state">No fee records have been posted yet.</div></div>
@else
    <div class="card" style="padding:0;">
        <table>
            <thead><tr><th>Date</th><th>Description</th><th>Term</th><th>Charged</th><th>Paid</th><th>Balance</th></tr></thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ $transaction->academicTerm?->label() ?? '—' }}</td>
                        <td style="color:#dc3545;">{{ $transaction->isDebit() ? 'KSh '.number_format($transaction->amount, 2) : '—' }}</td>
                        <td style="color:#1B7A3D;">{{ $transaction->isCredit() ? 'KSh '.number_format($transaction->amount, 2) : '—' }}</td>
                        <td style="font-weight:700;">KSh {{ number_format($transaction->running_balance, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection
