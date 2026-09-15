@extends('layouts.dashboard')

@section('title', 'My Timetable - Mwana')
@section('role-label', 'Teacher')
@section('page-title', 'My Timetable')

@section('nav')
@include('teacher._nav', ['active' => 'timetable'])
@endsection

@section('content')

@if ($slots->isEmpty())
    <div class="card"><div class="empty-state">Your timetable is empty. Once your principal schedules your classes, they'll appear here.</div></div>
@else
    @foreach ($days as $day)
        @if ($slots->has($day))
            <div class="card">
                <h3 style="margin-top:0;">{{ $day }}</h3>
                <table>
                    <thead><tr><th>Time</th><th>Class</th><th>Subject</th><th>Room</th></tr></thead>
                    <tbody>
                        @foreach ($slots[$day]->sortBy('start_time') as $slot)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i') }}–{{ \Illuminate\Support\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                                <td>{{ $slot->schoolClass->displayName() }}</td>
                                <td>{{ $slot->subject->name }}</td>
                                <td>{{ $slot->room ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach
@endif

@endsection
