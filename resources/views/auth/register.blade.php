@extends('layouts.guest')
@section('title', 'Register – Aref Academy')

@section('content')
<div class="card">
    <h1 class="mb-6 text-xl font-bold">Create your account</h1>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label class="label" for="name">Full Name</label>
            <input class="input" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label class="label" for="phone">Phone Number</label>
            <input class="input" id="phone" name="phone" type="tel" dir="ltr" value="{{ old('phone') }}" required placeholder="01xxxxxxxxx">
        </div>
        <div>
            <label class="label" for="governorate">Governorate</label>
            <select class="input" id="governorate" name="governorate" required>
                <option value="">Select governorate…</option>
                @foreach($governorates as $gov)
                    <option value="{{ $gov }}" @selected(old('governorate') === $gov)>{{ $gov }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="grade_level">Grade Level</label>
            <select class="input" id="grade_level" name="grade_level" required>
                <option value="">Select grade…</option>
                @foreach($grades as $grade)
                    <option value="{{ $grade->value }}" @selected(old('grade_level') === $grade->value)>{{ $grade->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="password">Password</label>
            <input class="input" id="password" name="password" type="password" required>
        </div>
        <div>
            <label class="label" for="password_confirmation">Confirm Password</label>
            <input class="input" id="password_confirmation" name="password_confirmation" type="password" required>
        </div>
        <button class="btn w-full">Register</button>
    </form>
    <p class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
        Already registered? <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('login') }}">Log in</a>
    </p>
</div>
@endsection
