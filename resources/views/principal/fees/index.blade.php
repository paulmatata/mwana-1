@extends('layouts.dashboard')

@section('title', 'Fees - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Fee Ledger')

@section('nav')
@include('principal._nav', ['active' => 'fees'])
@endsection

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; gap:12px; flex-wrap:wrap;">
    <form method="GET" style="display:flex; gap:8px; flex-wrap:wrap;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or admission no." style="width:220px; margin:0;">
        <select name="class_id" onchange="this.form.submit()" style="width:auto; margin:0;">
            <option value="">All classes</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->displayName() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn secondary small">Search</button>
    </form>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('principal.fees.bulk') }}" class="btn secondary">Bulk post a charge</a>
        <a href="{{ route('principal.fees.charge.create') }}" class="btn">+ Record a charge</a>
    </div>
</div>

<div class="card" style="padding:0;">
    @if ($students->isEmpty())
        <div class="empty-state">No fee history yet. Record a charge to get started.</div>
    @else
        <table>
            <thead><tr><th>Student</th><th>Class</th><th>Current balance</th><th></th></tr></thead>
            <tbody>
                @foreach ($students as $student)
                    <tr>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->schoolClass->displayName() }}</td>
                        <td style="font-weight:700; color:{{ $student->current_balance > 0 ? '#dc3545' : '#1B7A3D' }};">
                            KSh {{ number_format($student->current_balance, 2) }}
                        </td>
                        <td style="display:flex; gap:8px;">
                            <a href="{{ route('principal.fees.ledger', $student) }}" class="btn small secondary">View ledger</a>
                            <a href="{{ route('principal.fees.payment.create', $student) }}" class="btn small">Record payment</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div style="margin-top:20px;">
    {{ $students->links() }}
</div>

@endsection
