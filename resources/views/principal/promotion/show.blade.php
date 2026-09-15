@extends('layouts.dashboard')

@section('title', 'Promote Students - Mwana')
@section('role-label', 'Principal')
@section('page-title', 'Promote ' . $class->displayName())

@section('nav')
@include('principal._nav', ['active' => 'classes'])
@endsection

@section('content')

@if ($class->promotesTo)
    <div class="card" style="background:#e6f4ea; border-color:#1B7A3D; font-size:0.9rem;">
        Students left checked below will move from <strong>{{ $class->displayName() }}</strong> to
        <strong>{{ $class->promotesTo->displayName() }}</strong>. Unchecked students stay in
        {{ $class->displayName() }} (held back).
    </div>
@else
    <div class="card" style="background:#fff8e1; border-color:#ffc107; font-size:0.9rem;">
        <strong>{{ $class->displayName() }}</strong> has no promotion destination set, so it's treated
        as a graduating class. Students left checked below will be marked <strong>graduated</strong>
        rather than moved to another class. Unchecked students stay in this class (held back).
        You can set a destination class instead from <a href="{{ route('principal.classes.edit', $class) }}">Edit class</a>.
    </div>
@endif

@if ($students->isEmpty())
    <div class="card"><div class="empty-state">No active students in this class.</div></div>
@else
    <div class="card">
        <form method="POST" action="{{ route('principal.promotion.store', $class) }}" id="promotion-form">
            @csrf

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <label style="margin:0; display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" id="select-all" checked style="width:auto;"> Select / deselect all
                </label>
                <span style="font-size:0.85rem; color:#8496a6;">{{ $students->count() }} active student(s)</span>
            </div>

            <table>
                <thead><tr><th style="width:40px;"></th><th>Name</th><th>Admission No.</th></tr></thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td><input type="checkbox" name="promote[]" value="{{ $student->id }}" class="promote-checkbox" checked style="width:auto; margin:0;"></td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->admission_no }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="display:flex; gap:12px; margin-top:20px;">
                <button type="submit" class="btn" id="submit-btn">
                    @if ($class->promotesTo)
                        Promote checked students to {{ $class->promotesTo->displayName() }}
                    @else
                        Mark checked students as graduated
                    @endif
                </button>
                <a href="{{ route('principal.classes.index') }}" class="btn secondary">Cancel</a>
            </div>
        </form>
    </div>
@endif

<script>
    document.getElementById('select-all')?.addEventListener('change', function () {
        document.querySelectorAll('.promote-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('promotion-form')?.addEventListener('submit', function (e) {
        const checked = document.querySelectorAll('.promote-checkbox:checked').length;
        const total = document.querySelectorAll('.promote-checkbox').length;
        const heldBack = total - checked;
        const isPromotion = {{ $class->promotesTo ? 'true' : 'false' }};
        const action = isPromotion ? 'promote' : 'mark as graduated';
        const msg = `About to ${action} ${checked} student(s).` + (heldBack > 0 ? ` ${heldBack} will be held back.` : '') + ' Continue?';
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });
</script>

@endsection
