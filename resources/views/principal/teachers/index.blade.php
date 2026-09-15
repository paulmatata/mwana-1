@extends('layouts.dashboard')

@section('title', 'Teachers - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Teachers')

@section('nav')
@include('principal._nav', ['active' => 'teachers'])
@endsection

@section('content')

@if (session('bulk_created_accounts') && count(session('bulk_created_accounts')))
    <div class="card" style="background:#fff8e1; border-color:#ffc107;">
        <strong>Accounts created:</strong>
        <p style="margin:6px 0 12px; font-size:0.85rem; color:#6b5300;">
            Share these with each teacher securely now &mdash; passwords are not shown again.
        </p>
        <table style="box-shadow:none;">
            <thead><tr><th>Name</th><th>Login (email/phone)</th><th>Temporary password</th></tr></thead>
            <tbody>
                @foreach (session('bulk_created_accounts') as $account)
                    <tr>
                        <td>{{ $account['name'] }}</td>
                        <td>{{ $account['login'] }}</td>
                        <td><code style="background:#fff; padding:3px 8px; border-radius:5px;">{{ $account['password'] }}</code></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if (session('generated_password'))
    <div class="card" style="background:#fff8e1; border-color:#ffc107;">
        <strong>Account created for {{ session('generated_for') }}.</strong>
        <p style="margin:8px 0 0;">
            Temporary password: <code style="background:#fff; padding:4px 10px; border-radius:6px; font-size:1rem;">{{ session('generated_password') }}</code>
        </p>
        <p style="margin:8px 0 0; font-size:0.85rem; color:#6b5300;">
            Share this with them securely now &mdash; it will not be shown again.
        </p>
    </div>
@endif

<div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">

    <div style="flex:2; min-width:340px;">
        <div class="card" style="padding:0;">
            @if ($teachers->isEmpty())
                <div class="empty-state">No teachers added yet.</div>
            @else
                <table>
                    <thead>
                        <tr><th>Name</th><th>Contact</th><th>Qualified subjects</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($teachers as $teacher)
                            <tr>
                                <td>{{ $teacher->name }}</td>
                                <td>{{ $teacher->email ?? $teacher->phone }}</td>
                                <td style="font-size:0.85rem;">
                                    @if ($teacher->subjectsQualifiedFor->isEmpty())
                                        <span style="color:#8496a6;">Any (none set)</span>
                                    @else
                                        {{ $teacher->subjectsQualifiedFor->pluck('name')->join(', ') }}
                                    @endif
                                </td>
                                <td><span class="status-badge status-{{ $teacher->status }}">{{ ucfirst($teacher->status) }}</span></td>
                                <td style="display:flex; gap:8px;">
                                    <a href="{{ route('principal.teachers.edit', $teacher) }}" class="btn small secondary">Edit</a>
                                    <form method="POST" action="{{ route('principal.teachers.reset-password', $teacher) }}" onsubmit="return confirm('Reset this teacher\'s password?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn small secondary">Reset password</button>
                                    </form>
                                    <form method="POST" action="{{ route('principal.teachers.toggle-status', $teacher) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn small {{ $teacher->status === 'active' ? 'danger' : '' }}">
                                            {{ $teacher->status === 'active' ? 'Suspend' : 'Reactivate' }}
                                        </button>
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
            <h3 style="margin-top:0;">Add a teacher</h3>
            <a href="{{ route('principal.teachers.bulk') }}" class="btn secondary small" style="margin-bottom:16px; display:inline-block;">Bulk add via spreadsheet</a>
            <form method="POST" action="{{ route('principal.teachers.store') }}">
                @csrf
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}">

                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}">

                <p style="font-size:0.8rem; color:#8496a6; margin:-8px 0 14px;">Provide at least one of email or phone &mdash; that's what they'll log in with.</p>

                @if ($subjects->isNotEmpty())
                    <label>Qualified to teach (optional)</label>
                    <div style="max-height:140px; overflow-y:auto; border:1px solid #dde3ea; border-radius:8px; padding:10px 14px; margin-bottom:16px;">
                        @foreach ($subjects as $subject)
                            <label style="display:flex; align-items:center; gap:8px; font-weight:400; margin-bottom:6px;">
                                <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" style="width:auto; margin:0;">
                                {{ $subject->name }}
                            </label>
                        @endforeach
                    </div>
                    <p style="font-size:0.78rem; color:#8496a6; margin:-10px 0 14px;">
                        Leave all unchecked if unsure &mdash; they'll be assignable to any subject until you set this.
                    </p>
                @endif

                <button type="submit" class="btn">Create teacher account</button>
            </form>
        </div>
    </div>

</div>

@endsection
