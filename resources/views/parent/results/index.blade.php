@extends('layouts.dashboard')

@section('title', $student->name . ' - Results - Mwana')
@section('role-label', 'Parent')
@section('page-title', $student->name . ' — Results')

@section('nav')
@include('parent._nav', ['active' => 'dashboard'])
@endsection

@section('content')

<div style="display:flex; gap:8px; margin-bottom:20px;">
    <a href="{{ route('parent.dashboard') }}" class="btn small secondary">&larr; All children</a>
    <a href="{{ route('parent.fees', $student) }}" class="btn small secondary">Fees</a>
    <a href="{{ route('parent.notices', $student) }}" class="btn small secondary">Notices</a>
</div>

@if ($byExam->isEmpty())
    <div class="card"><div class="empty-state">No approved results yet. Once the school approves an exam's results, they'll appear here.</div></div>
@else
    @foreach ($byExam as $examId => $summary)
        @php $exam = $summary['exam']; @endphp
        <div class="card">
            <h3 style="margin:0 0 4px;">{{ $exam->name }}</h3>
            <p style="margin:0 0 16px; color:#8496a6; font-size:0.85rem;">
                {{ $exam->academicTerm?->label() }} &middot; Approved {{ $exam->approved_at?->format('d M Y') }}
            </p>
            <table>
                <thead><tr><th>Subject</th><th>Score</th><th>Grade</th><th>Remarks</th></tr></thead>
                <tbody>
                    @foreach ($summary['marks'] as $mark)
                        <tr>
                            <td>{{ $mark->subject->name }}</td>
                            <td>{{ $mark->score }}</td>
                            <td>{{ $mark->grade }}</td>
                            <td>{{ $mark->remarks ?? '—' }}</td>
                        </tr>
                    @endforeach
                    <tr style="background:#eef3f8; font-weight:700;">
                        <td>Total / Average</td>
                        <td>{{ $summary['total'] }} / {{ $summary['average'] }} avg</td>
                        <td>{{ $summary['mean_grade'] }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach
@endif

@endsection
