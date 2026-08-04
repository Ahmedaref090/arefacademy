@extends('layouts.account')
@section('title', 'Profile – Aref Academy')

@section('account')
<h1 class="mb-6 text-2xl font-bold">Profile</h1>

<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="card max-w-xl space-y-4">
    @csrf
    @method('PUT')

    <div class="flex items-center gap-4">
        @if($user->avatarUrl())
            <img src="{{ $user->avatarUrl() }}" alt="" class="h-16 w-16 rounded-full object-cover">
        @else
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-600 text-xl font-bold text-white">{{ $user->initials() }}</span>
        @endif
        <div class="flex-1">
            <label class="label" for="avatar">Profile Photo</label>
            <input class="input" id="avatar" name="avatar" type="file" accept="image/*">
        </div>
    </div>

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
        <label class="label" for="parent_phone">Parent's Phone</label>
        <input class="input" id="parent_phone" name="parent_phone" type="tel" dir="ltr" value="{{ old('parent_phone', $user->parent_phone) }}" placeholder="01xxxxxxxxx">
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
        <label class="label" for="grade_level">Academic Track</label>
        <select class="input" id="grade_level" name="grade_level" required>
            @foreach($grades as $grade)
                <option value="{{ $grade->value }}" @selected(old('grade_level', $user->grade_level?->value) === $grade->value)>{{ $grade->label() }}</option>
            @endforeach
        </select>
    </div>
    <p class="text-xs text-gray-400">
        Want to change your password? Use the
        <a class="text-emerald-600 hover:underline dark:text-emerald-400" href="{{ route('account.security') }}">Security page</a>.
    </p>
    <button class="btn">Save Changes</button>
</form>
@endsection
