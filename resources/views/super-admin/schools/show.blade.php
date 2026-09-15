@extends('layouts.dashboard')

@section('title', $school->name . ' - Mwana')
@section('role-label', 'Super Admin')
@section('page-title', $school->name)

@section('nav')
@include('super-admin._nav', ['active' => 'schools'])
@endsection

@section('content')

@if (session('generated_password'))
    <div class="card" style="background:#fff8e1; border-color:#ffc107;">
        <strong>Account created for {{ session('generated_for') }}.</strong>
        <p style="margin:8px 0 0;">
            Temporary password: <code style="background:#fff; padding:4px 10px; border-radius:6px; font-size:1rem;">{{ session('generated_password') }}</code>
        </p>
        <p style="margin:8px 0 0; font-size:0.85rem; color:#6b5300;">
            Share this with them securely now &mdash; it will not be shown again. They should change it after first login.
        </p>
    </div>
@endif

<div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

    <div style="flex:2; min-width:340px;">
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <h3 style="margin:0 0 4px;">{{ $school->name }}</h3>
                    <p style="margin:0; color:#8496a6; font-size:0.9rem;">Code: {{ $school->code }}</p>
                </div>
                <span class="status-badge status-{{ $school->status }}">{{ ucfirst($school->status) }}</span>
            </div>

            <table style="margin-top:20px; box-shadow:none;">
                <tr><td style="width:140px; color:#8496a6;">County</td><td>{{ $school->county ?? '—' }}</td></tr>
                <tr><td style="color:#8496a6;">Sub-county</td><td>{{ $school->sub_county ?? '—' }}</td></tr>
                <tr><td style="color:#8496a6;">Address</td><td>{{ $school->address ?? '—' }}</td></tr>
                <tr><td style="color:#8496a6;">Phone</td><td>{{ $school->phone ?? '—' }}</td></tr>
                <tr><td style="color:#8496a6;">Email</td><td>{{ $school->email ?? '—' }}</td></tr>
            </table>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <a href="{{ route('super-admin.schools.edit', $school) }}" class="btn secondary small">Edit details</a>
                <form method="POST" action="{{ route('super-admin.schools.toggle-status', $school) }}" onsubmit="return confirm('{{ $school->status === 'active' ? 'Suspend this school? Staff and parents will be unable to log in.' : 'Reactivate this school?' }}');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn small {{ $school->status === 'active' ? 'danger' : '' }}">
                        {{ $school->status === 'active' ? 'Suspend school' : 'Reactivate school' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="card" style="display:flex; gap:32px;">
            <div>
                <div style="font-size:0.8rem; color:#8496a6; text-transform:uppercase;">Classes</div>
                <div style="font-size:1.6rem; font-family:'Poppins',sans-serif; font-weight:700;">{{ $school->classes_count }}</div>
            </div>
            <div>
                <div style="font-size:0.8rem; color:#8496a6; text-transform:uppercase;">Subjects</div>
                <div style="font-size:1.6rem; font-family:'Poppins',sans-serif; font-weight:700;">{{ $school->subjects_count }}</div>
            </div>
            <div>
                <div style="font-size:0.8rem; color:#8496a6; text-transform:uppercase;">Students</div>
                <div style="font-size:1.6rem; font-family:'Poppins',sans-serif; font-weight:700;">{{ $school->students_count }}</div>
            </div>
        </div>
    </div>

    <div style="flex:1; min-width:300px;">
        <div class="card">
            <h3 style="margin-top:0;">Principal account</h3>

            @if ($school->principals->isEmpty())
                <p style="color:#8496a6; font-size:0.9rem;">No principal account yet. Create one so this school can start setting up classes.</p>
                <form method="POST" action="{{ route('super-admin.schools.principals.store', $school) }}">
                    @csrf
                    <label for="p_name">Full name</label>
                    <input type="text" id="p_name" name="name" value="{{ old('name') }}" required>

                    <label for="p_email">Email</label>
                    <input type="email" id="p_email" name="email" value="{{ old('email') }}">

                    <label for="p_phone">Phone</label>
                    <input type="text" id="p_phone" name="phone" value="{{ old('phone') }}">

                    <p style="font-size:0.8rem; color:#8496a6; margin:-8px 0 14px;">Provide at least one of email or phone &mdash; that's what they'll log in with.</p>

                    <button type="submit" class="btn">Create principal account</button>
                </form>
            @else
                @foreach ($school->principals as $principal)
                    <div style="padding:14px 0; border-bottom:1px solid #dde3ea;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <strong>{{ $principal->name }}</strong>
                            <span class="status-badge status-{{ $principal->status }}">{{ ucfirst($principal->status) }}</span>
                        </div>
                        <div style="font-size:0.85rem; color:#8496a6; margin:4px 0 10px;">
                            {{ $principal->email ?? $principal->phone }}
                        </div>
                        <div style="display:flex; gap:8px;">
                            <form method="POST" action="{{ route('super-admin.schools.principals.reset-password', [$school, $principal]) }}" onsubmit="return confirm('Reset this principal\'s password?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn small secondary">Reset password</button>
                            </form>
                            <form method="POST" action="{{ route('super-admin.schools.principals.toggle-status', [$school, $principal]) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn small {{ $principal->status === 'active' ? 'danger' : '' }}">
                                    {{ $principal->status === 'active' ? 'Suspend' : 'Reactivate' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

</div>

@endsection
