@php $active = $active ?? ''; @endphp
<a href="{{ route('super-admin.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}">Dashboard</a>
<a href="{{ route('super-admin.schools.index') }}" class="{{ $active === 'schools' ? 'active' : '' }}">Schools</a>
<a href="{{ route('account.password.edit') }}" class="{{ $active === 'account' ? 'active' : '' }}">Change Password</a>
