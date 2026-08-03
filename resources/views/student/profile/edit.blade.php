@extends('layouts.app')
@section('title', 'Profile – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">Profile</h1>

<form method="POST" action="{{ route('profile.update') }}" class="card max-w-xl space-y-4">
    @csrf
    @method('PUT')
    <div>
        <label class="label">Phone Number</label>
        <input class="input opacity-60" value="{{ $user->phone }}" dir="ltr" disabled>
        <p class="mt-1 text-xs text-gray-400">Your phone is your login ID and cannot be changed.</p>
    </div>
    <div>
        <label class="label" for="name">Full Name</label>
        <input class="input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
    </div>
    <div>
        <label class="label" for="governorate">Governorate</label>
        <select class="input" id="governorate" name="governorate" required>
            @foreach($governorates as $gov)
                <option value="{{ $gov }}" @selected(old('governorate', $user->governorate) === $gov)>{{ $gov }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label" for="grade_level">Grade Level</label>
        <select class="input" id="grade_level" name="grade_level" required>
            @foreach($grades as $grade)
                <option value="{{ $grade->value }}" @selected(old('grade_level', $user->grade_level?->value) === $grade->value)>{{ $grade->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label" for="password">New Password (leave blank to keep current)</label>
        <input class="input" id="password" name="password" type="password">
    </div>
    <div>
        <label class="label" for="password_confirmation">Confirm New Password</label>
        <input class="input" id="password_confirmation" name="password_confirmation" type="password">
    </div>
    <button class="btn">Save Changes</button>
</form>
@endsection
