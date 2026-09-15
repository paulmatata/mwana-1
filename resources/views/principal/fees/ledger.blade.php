@extends('layouts.dashboard')

@section('title', $student->name . ' - Fee Ledger - Mwana')
@section('role-label', 'Principal')
@section('page-title', $student->name . ' — Fee Ledger')

@section('nav')
@include('principal._nav', ['active' => 'fees'])
@endsection

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <a href="{{ route('principal.fees.index') }}" class="btn small secondary">&larr; All students</a>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('principal.fees.charge.create') }}" class="btn small secondary">Record a charge</a>
        <a href="{{ route('principal.fees.payment.create', $student) }}" class="btn small">Record payment</a>
    </div>
</div>

<div class="card" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h3 style="margin:0 0 4px;">{{ $student->name }}</h3>
        <p style="margin:0; color:#8496a6; font-size:0.9rem;">{{ $student->schoolClass->displayName() }} &middot; Adm. No. {{ $student->admission_no }}</p>
    </div>
    <div style="text-align:right;">
        <div style="font-size:0.78rem; color:#8496a6; text-transform:uppercase;">Current balance</div>
        <div style="font-size:1.6rem; font-family:'Poppins',sans-serif; font-weight:700; color:{{ $student->currentFeeBalance() > 0 ? '#dc3545' : '#1B7A3D' }};">
            KSh {{ number_format($student->currentFeeBalance(), 2) }}
        </div>
    </div>
</div>

<div class="card" style="padding:0;">
    @if ($transactions->isEmpty())
        <div class="empty-state">No transactions yet for this student.</div>
    @else
        <table>
            <thead><tr><th>Date</th><th>Description</th><th>Term</th><th>Debit</th><th>Credit</th><th>Balance</th><th></th></tr></thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ $transaction->academicTerm?->label() ?? '—' }}</td>
                        <td style="color:#dc3545;">{{ $transaction->isDebit() ? 'KSh '.number_format($transaction->amount, 2) : '—' }}</td>
                        <td style="color:#1B7A3D;">{{ $transaction->isCredit() ? 'KSh '.number_format($transaction->amount, 2) : '—' }}</td>
                        <td style="font-weight:700;">KSh {{ number_format($transaction->running_balance, 2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('principal.fees.destroy', $transaction) }}" onsubmit="return confirm('Remove this transaction? Balances for everything after it will be recalculated.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn small danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
