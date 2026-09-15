@extends('layouts.dashboard')

@section('title', 'Subjects - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Subjects')

@section('nav')
@include('principal._nav', ['active' => 'subjects'])
@endsection

@section('content')

<div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

    <div style="flex:2; min-width:340px;">
        <div class="card" style="padding:0;">
            @if ($subjects->isEmpty())
                <div class="empty-state">No subjects yet. Add your first one using the form.</div>
            @else
                <table>
                    <thead>
                        <tr><th>Subject</th><th>Code</th><th>Type</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($subjects as $subject)
                            <tr>
                                <td>{{ $subject->name }}</td>
                                <td>{{ $subject->code ?? '—' }}</td>
                                <td>{{ $subject->is_core ? 'Core' : 'Elective' }}</td>
                                <td style="display:flex; gap:8px;">
                                    <a href="{{ route('principal.subjects.edit', $subject) }}" class="btn small secondary">Edit</a>
                                    <form method="POST" action="{{ route('principal.subjects.destroy', $subject) }}" onsubmit="return confirm('Remove this subject?');">
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
            <h3 style="margin-top:0;">Add a subject</h3>
            <form method="POST" action="{{ route('principal.subjects.store') }}">
                @csrf
                <label for="name">Subject name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Mathematics" required>

                <label for="code">Code (optional)</label>
                <input type="text" id="code" name="code" value="{{ old('code') }}" placeholder="e.g. MAT">

                <input type="hidden" name="is_core" value="0">
                <label style="display:flex; align-items:center; gap:8px; font-weight:500;">
                    <input type="checkbox" name="is_core" value="1" style="width:auto; margin:0;" {{ old('is_core', '1') ? 'checked' : '' }}>
                    Core subject (every student in an assigned class is enrolled automatically)
                </label>
                <p style="font-size:0.78rem; color:#8496a6; margin:4px 0 16px;">
                    Leave unchecked for an elective &mdash; the assigned teacher will choose which students take it.
                </p>

                <button type="submit" class="btn">Add subject</button>
            </form>
        </div>
    </div>

</div>

@endsection
