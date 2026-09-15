@php $active = $active ?? ''; @endphp
<a href="{{ route('principal.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}">Dashboard</a>
<a href="{{ route('principal.terms.index') }}" class="{{ $active === 'terms' ? 'active' : '' }}">Terms</a>
<a href="{{ route('principal.classes.index') }}" class="{{ $active === 'classes' ? 'active' : '' }}">Classes</a>
<a href="{{ route('principal.subjects.index') }}" class="{{ $active === 'subjects' ? 'active' : '' }}">Subjects</a>
<a href="{{ route('principal.teachers.index') }}" class="{{ $active === 'teachers' ? 'active' : '' }}">Teachers</a>
<a href="{{ route('principal.assignments.index') }}" class="{{ $active === 'assignments' ? 'active' : '' }}">Assignments</a>
<a href="{{ route('principal.timetable.index') }}" class="{{ $active === 'timetable' ? 'active' : '' }}">Timetable</a>
<a href="{{ route('principal.fees.index') }}" class="{{ $active === 'fees' ? 'active' : '' }}">Fees</a>
<a href="{{ route('principal.notices.index') }}" class="{{ $active === 'notices' ? 'active' : '' }}">Notices</a>
<a href="{{ route('principal.exams.index') }}" class="{{ $active === 'exams' ? 'active' : '' }}">Exams &amp; Results</a>
<a href="{{ route('account.password.edit') }}" class="{{ $active === 'account' ? 'active' : '' }}">Change Password</a>
