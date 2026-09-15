@extends('layouts.dashboard')

@section('title', 'Edit School - Mwana')
@section('role-label', 'Super Admin')
@section('page-title', 'Edit ' . $school->name)

@section('nav')
@include('super-admin._nav', ['active' => 'schools'])
@endsection

@section('content')

<div class="card" style="max-width:600px;">
    <form method="POST" action="{{ route('super-admin.schools.update', $school) }}">
        @csrf
        @method('PUT')

        <label for="name">School name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $school->name) }}" required>

        <label for="code">School code</label>
        <input type="text" id="code" name="code" value="{{ old('code', $school->code) }}" required>

        <label for="county">County</label>
        <input type="text" id="county" name="county" value="{{ old('county', $school->county) }}">

        <label for="sub_county">Sub-county</label>
        <input type="text" id="sub_county" name="sub_county" value="{{ old('sub_county', $school->sub_county) }}">

        <label for="address">Address</label>
        <input type="text" id="address" name="address" value="{{ old('address', $school->address) }}">

        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $school->phone) }}">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $school->email) }}">

        <div style="display:flex; gap:12px; margin-top:8px;">
            <button type="submit" class="btn">Save changes</button>
            <a href="{{ route('super-admin.schools.show', $school) }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
