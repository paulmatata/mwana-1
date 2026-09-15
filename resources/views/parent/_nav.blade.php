@php $active = $active ?? ''; @endphp
<a href="{{ route('parent.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}">My Children</a>
<a href="{{ route('parent.first-login.school') }}">+ Add another child</a>
<a href="{{ route('account.password.edit') }}" class="{{ $active === 'account' ? 'active' : '' }}">Change Password</a>
