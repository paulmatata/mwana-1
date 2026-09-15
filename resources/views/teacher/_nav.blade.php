@php $active = $active ?? ''; @endphp
<a href="{{ route('teacher.dashboard') }}" class="{{ $active === 'dashboard' ? 'active' : '' }}">Dashboard</a>
<a href="{{ route('teacher.students.index') }}" class="{{ $active === 'students' ? 'active' : '' }}">Students</a>
<a href="{{ route('teacher.marks.index') }}" class="{{ $active === 'marks' ? 'active' : '' }}">Marks Upload</a>
<a href="{{ route('teacher.electives.index') }}" class="{{ $active === 'electives' ? 'active' : '' }}">Elective Enrollment</a>
<a href="{{ route('teacher.class-exams.index') }}" class="{{ $active === 'class-exams' ? 'active' : '' }}">Class Exams (as Class Teacher)</a>
<a href="{{ route('teacher.notices.index') }}" class="{{ $active === 'notices' ? 'active' : '' }}">Notices</a>
<a href="{{ route('teacher.timetable') }}" class="{{ $active === 'timetable' ? 'active' : '' }}">My Timetable</a>
<a href="{{ route('account.password.edit') }}" class="{{ $active === 'account' ? 'active' : '' }}">Change Password</a>
