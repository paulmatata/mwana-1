@extends('layouts.dashboard')

@section('title', 'Super Admin Dashboard - Mwana')
@section('role-label', 'Super Admin')
@section('page-title', 'Overview')

@section('nav')
@include('super-admin._nav', ['active' => 'dashboard'])
@endsection

@section('content')

<div class="card" style="display:flex; gap:32px; flex-wrap:wrap;">
    <div>
        <div style="font-size:0.8rem; color:#8496a6; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px;">Total schools</div>
        <div style="font-size:2rem; font-family:'Poppins',sans-serif; font-weight:700;">{{ $stats['total_schools'] }}</div>
    </div>
    <div>
        <div style="font-size:0.8rem; color:#8496a6; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px;">Active</div>
        <div style="font-size:2rem; font-family:'Poppins',sans-serif; font-weight:700; color:#1B7A3D;">{{ $stats['active_schools'] }}</div>
    </div>
    <div>
        <div style="font-size:0.8rem; color:#8496a6; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px;">Suspended</div>
        <div style="font-size:2rem; font-family:'Poppins',sans-serif; font-weight:700; color:#dc3545;">{{ $stats['suspended_schools'] }}</div>
    </div>
    <div>
        <div style="font-size:0.8rem; color:#8496a6; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px;">Principal accounts</div>
        <div style="font-size:2rem; font-family:'Poppins',sans-serif; font-weight:700;">{{ $stats['total_principals'] }}</div>
    </div>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h3 style="margin:0;">Recently added schools</h3>
        <a href="{{ route('super-admin.schools.create') }}" class="btn small">+ Add school</a>
    </div>

    @if ($recentSchools->isEmpty())
        <div class="empty-state">No schools added yet. Add your first school to get started.</div>
    @else
        <table>
            <thead>
                <tr><th>Name</th><th>Code</th><th>County</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($recentSchools as $school)
                    <tr>
                        <td>{{ $school->name }}</td>
                        <td>{{ $school->code }}</td>
                        <td>{{ $school->county ?? '—' }}</td>
                        <td><span class="status-badge status-{{ $school->status }}">{{ ucfirst($school->status) }}</span></td>
                        <td><a href="{{ route('super-admin.schools.show', $school) }}" class="btn small secondary">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
