@extends('layouts.dashboard')

@section('title', 'Terms - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Terms')

@section('nav')
@include('principal._nav', ['active' => 'terms'])
@endsection

@section('content')

<p style="color:#52697d; font-size:0.92rem; margin-top:-8px; margin-bottom:20px; max-width:640px;">
    Terms are the shared reference point for exams, timetable sessions, and fee charges -
    creating them here is what makes real history possible across the school.
</p>

<div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

    <div style="flex:2; min-width:340px;">
        <div class="card" style="padding:0;">
            @if ($terms->isEmpty())
                <div class="empty-state">No terms yet. Add your school's first term using the form.</div>
            @else
                <table>
                    <thead><tr><th>Term</th><th>Year</th><th>Dates</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($terms as $term)
                            <tr>
                                <td>{{ $term->name }}</td>
                                <td>{{ $term->year }}</td>
                                <td>
                                    @if ($term->start_date || $term->end_date)
                                        {{ $term->start_date?->format('d M Y') ?? '—' }} to {{ $term->end_date?->format('d M Y') ?? '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($term->is_current)
                                        <span class="status-badge status-active">Current</span>
                                    @endif
                                </td>
                                <td style="display:flex; gap:8px;">
                                    @unless ($term->is_current)
                                        <form method="POST" action="{{ route('principal.terms.make-current', $term) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn small secondary">Make current</button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('principal.terms.destroy', $term) }}" onsubmit="return confirm('Remove this term?');">
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
    </div>

    <div style="flex:1; min-width:300px;">
        <div class="card">
            <h3 style="margin-top:0;">Add a term</h3>
            <form method="POST" action="{{ route('principal.terms.store') }}">
                @csrf
                <label for="name">Term name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Term 2" required>

                <label for="year">Year</label>
                <input type="text" id="year" name="year" value="{{ old('year', now()->year) }}" required>

                <label for="start_date">Start date (optional)</label>
                <input type="text" id="start_date" name="start_date" value="{{ old('start_date') }}" placeholder="YYYY-MM-DD">

                <label for="end_date">End date (optional)</label>
                <input type="text" id="end_date" name="end_date" value="{{ old('end_date') }}" placeholder="YYYY-MM-DD">

                <label style="display:flex; align-items:center; gap:8px; font-weight:500;">
                    <input type="checkbox" name="make_current" value="1" style="width:auto; margin:0;">
                    Make this the current term
                </label>
                <p style="font-size:0.78rem; color:#8496a6; margin:6px 0 16px;">
                    The current term is the default selected when creating a new exam, fee charge, or timetable session.
                </p>

                <button type="submit" class="btn">Add term</button>
            </form>
        </div>
    </div>

</div>

@endsection
