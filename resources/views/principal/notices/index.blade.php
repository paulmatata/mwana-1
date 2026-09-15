@extends('layouts.dashboard')

@section('title', 'Notices - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Notices')

@section('nav')
@include('principal._nav', ['active' => 'notices'])
@endsection

@section('content')

<div style="display:flex; justify-content:flex-end; margin-bottom:20px;">
    <a href="{{ route('principal.notices.create') }}" class="btn">+ Post a notice</a>
</div>

@if ($notices->isEmpty())
    <div class="card"><div class="empty-state">No notices posted yet.</div></div>
@else
    @foreach ($notices as $notice)
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <h3 style="margin:0 0 4px;">{{ $notice->title }}</h3>
                    <p style="margin:0 0 10px; font-size:0.82rem; color:#8496a6;">
                        @if ($notice->student)
                            To: {{ $notice->student->name }} only
                        @elseif ($notice->schoolClass)
                            To: {{ $notice->schoolClass->displayName() }}
                        @else
                            To: whole school
                        @endif
                        &middot; by {{ $notice->postedBy->name }} &middot; {{ $notice->created_at->format('d M Y') }}
                    </p>
                </div>
                <form method="POST" action="{{ route('principal.notices.destroy', $notice) }}" onsubmit="return confirm('Remove this notice?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn small danger">Remove</button>
                </form>
            </div>
            <p style="margin:0; color:#3a453d; font-size:0.92rem;">{{ $notice->body }}</p>
        </div>
    @endforeach

    <div style="margin-top:20px;">
        {{ $notices->links() }}
    </div>
@endif

@endsection
