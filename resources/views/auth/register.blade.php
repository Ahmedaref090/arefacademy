@extends('layouts.guest')
@section('title', __('Register') . ' – ' . __('Aref Academy'))

@section('content')
<div class="card">
    <h1 class="mb-6 text-xl font-bold">{{ __('Create your account') }}</h1>

    @if($errors->any())
        <div class="mb-4 flex items-start gap-2 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/50 dark:text-red-400" role="alert">
            <span class="mt-0.5 shrink-0">⚠️</span>
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label class="label" for="name">{{ __('Full Name') }}</label>
            <input class="input" id="name" name="name" value="{{ old('name') }}" required autofocus>
        </div>
        <div>
            <label class="label" for="phone">{{ __('Phone Number') }}</label>
            <input class="input" id="phone" name="phone" type="tel" dir="ltr" value="{{ old('phone') }}" required placeholder="01xxxxxxxxx">
        </div>
        <div>
            <label class="label" for="parent_phone">{{ __("Parent's Phone (optional)") }}</label>
            <input class="input" id="parent_phone" name="parent_phone" type="tel" dir="ltr" value="{{ old('parent_phone') }}" placeholder="01xxxxxxxxx">
        </div>
        <div>
            <label class="label" for="governorate">{{ __('Governorate') }}</label>
            <select class="input" id="governorate" name="governorate" required>
                <option value="">{{ __('Select governorate…') }}</option>
                @foreach($governorates as $gov)
                    <option value="{{ $gov }}" @selected(old('governorate') === $gov)>{{ __($gov) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="grade_level">{{ __('Grade Level') }}</label>
            <select class="input" id="grade_level" name="grade_level" required>
                <option value="">{{ __('Select grade…') }}</option>
                @foreach($grades as $grade)
                    <option value="{{ $grade->value }}" @selected(old('grade_level') === $grade->value)>{{ __($grade->label()) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="password">{{ __('Password') }}</label>
            <input class="input" id="password" name="password" type="password" required>
        </div>
        <div>
            <label class="label" for="password_confirmation">{{ __('Confirm Password') }}</label>
            <input class="input" id="password_confirmation" name="password_confirmation" type="password" required>
        </div>
        <button class="btn w-full">{{ __('Register') }}</button>
    </form>
    <p class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
        {{ __('Already registered?') }} <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('login') }}">{{ __('Log in') }}</a>
    </p>
</div>
@endsection
