@extends('layouts.dashboard')

@section('title', 'Principal Dashboard - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Overview')

@section('nav')
@include('principal._nav', ['active' => 'dashboard'])
@endsection

@section('content')

<div class="card" style="display:flex; gap:32px; flex-wrap:wrap;">
    <div>
        <div style="font-size:0.8rem; color:#8496a6; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px;">Classes</div>
        <div style="font-size:2rem; font-family:'Poppins',sans-serif; font-weight:700;">{{ $stats['classes'] }}</div>
    </div>
    <div>
        <div style="font-size:0.8rem; color:#8496a6; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px;">Subjects</div>
        <div style="font-size:2rem; font-family:'Poppins',sans-serif; font-weight:700;">{{ $stats['subjects'] }}</div>
    </div>
    <div>
        <div style="font-size:0.8rem; color:#8496a6; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px;">Teachers</div>
        <div style="font-size:2rem; font-family:'Poppins',sans-serif; font-weight:700;">{{ $stats['teachers'] }}</div>
    </div>
    <div>
        <div style="font-size:0.8rem; color:#8496a6; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px;">Awaiting your approval</div>
        <div style="font-size:2rem; font-family:'Poppins',sans-serif; font-weight:700; color:#dc3545;">{{ $stats['pending_exams'] }}</div>
    </div>
</div>

@include('partials.insights-list')

@if ($classAverages->isNotEmpty())
    <div class="card">
        <h3 style="margin-top:0;">Class averages (latest approved exam per class)</h3>
        <canvas id="classAveragesChart" height="90"></canvas>
    </div>
@endif

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h3 style="margin:0;">Exams waiting for your review</h3>
        <a href="{{ route('principal.exams.index') }}" class="btn small secondary">View all exams</a>
    </div>

    @if ($pendingExams->isEmpty())
        <div class="empty-state">Nothing waiting on you right now.</div>
    @else
        <table>
            <thead>
                <tr><th>Exam</th><th>Class</th><th>Submitted</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($pendingExams as $exam)
                    <tr>
                        <td>{{ $exam->name }}</td>
                        <td>{{ $exam->schoolClass->displayName() }}</td>
                        <td>{{ $exam->submitted_at?->diffForHumans() }}</td>
                        <td><a href="{{ route('principal.exams.show', $exam) }}" class="btn small">Review</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@if ($classAverages->isNotEmpty())
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('classAveragesChart'), {
            type: 'bar',
            data: {
                labels: @json($classAverages->pluck('label')),
                datasets: [{
                    label: 'Average score',
                    data: @json($classAverages->pluck('average')),
                    backgroundColor: '#1B7A3D',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 100 } }
            }
        });
    </script>
    @endpush
@endif

@endsection
