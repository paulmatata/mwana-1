@extends('layouts.dashboard')

@section('title', 'Schools - Mwana')
@section('role-label', 'Super Admin')
@section('page-title', 'Schools')

@section('nav')
@include('super-admin._nav', ['active' => 'schools'])
@endsection

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <form method="GET" style="display:flex; gap:8px;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name, code or county" style="width:280px; margin:0;">
        <button type="submit" class="btn secondary small">Search</button>
    </form>
    <a href="{{ route('super-admin.schools.create') }}" class="btn">+ Add school</a>
</div>

<div class="card" style="padding:0;">
    @if ($schools->isEmpty())
        <div class="empty-state">
            @if (request('q'))
                No schools match "{{ request('q') }}".
            @else
                No schools added yet. <a href="{{ route('super-admin.schools.create') }}">Add your first school</a>.
            @endif
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Name</th><th>Code</th><th>County</th><th>Classes</th><th>Teachers</th><th>Students</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($schools as $school)
                    <tr>
                        <td>{{ $school->name }}</td>
                        <td>{{ $school->code }}</td>
                        <td>{{ $school->county ?? '—' }}</td>
                        <td>{{ $school->classes_count }}</td>
                        <td>{{ $school->teachers_count }}</td>
                        <td>{{ $school->students_count }}</td>
                        <td><span class="status-badge status-{{ $school->status }}">{{ ucfirst($school->status) }}</span></td>
                        <td><a href="{{ route('super-admin.schools.show', $school) }}" class="btn small secondary">Manage</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div style="margin-top:20px;">
    {{ $schools->links() }}
</div>

@endsection
