@extends('layouts.guest')
@section('title', __('Log in') . ' – ' . __('Aref Academy'))

@section('content')
<div class="text-center">
    <div class="icon-tile mx-auto reset-ico"><x-icon name="lock" class="h-6 w-6" :stroke="1.8"/></div>
    <h1 class="mt-4 text-2xl font-extrabold">{{ __('Welcome back') }}</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Log in with your phone') }}</p>
</div>

<form method="POST" action="{{ route('login') }}" class="mt-7 space-y-4" novalidate>
    @csrf
    <div>
        <label class="label" for="phone">{{ __('Phone Number') }}</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-slate-400"><x-icon name="phone" class="h-5 w-5"/></span>
            <input class="input !ps-11 @error('phone') is-invalid @enderror" id="phone" name="phone" type="tel" dir="ltr" value="{{ old('phone') }}" required autofocus placeholder="01xxxxxxxxx" data-phone maxlength="11" inputmode="numeric">
        </div>
        @error('phone')
            <p class="mt-1.5 flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400">
                <x-icon name="alert" class="h-4 w-4 shrink-0" :stroke="2"/> <span dir="auto">{{ $message }}</span>
            </p>
        @enderror
    </div>
    <div>
        <label class="label" for="password">{{ __('Password') }}</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-slate-400"><x-icon name="lock" class="h-5 w-5"/></span>
            <input class="input !ps-11 !pe-12 @error('password') is-invalid @enderror" id="password" name="password" type="password" required placeholder="••••••••">
            <button type="button" data-password-toggle data-target="password"
                    class="absolute inset-y-0 end-0 flex items-center pe-4 text-slate-400 transition hover:text-brand-600 dark:hover:text-brand-300"
                    aria-label="{{ __('Show password') }}">
                <span data-ico-show><x-icon name="eye" class="h-5 w-5"/></span>
                <span data-ico-hide class="hidden"><x-icon name="eye-off" class="h-5 w-5"/></span>
            </button>
        </div>
        @error('password')
            <p class="mt-1.5 flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400">
                <x-icon name="alert" class="h-4 w-4 shrink-0" :stroke="2"/> <span dir="auto">{{ $message }}</span>
            </p>
        @enderror
    </div>
    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
        <input type="checkbox" name="remember" class="size-4 rounded-md accent-brand-600"> {{ __('Remember me') }}
    </label>
    <button class="btn w-full !py-3 text-base">{{ __('Log in') }} <x-icon name="arrow" class="h-4 w-4 rtl:-scale-x-100" :stroke="2.2"/></button>
</form>
<p class="mt-5 text-center text-sm text-slate-500 dark:text-slate-400">
    {{ __('No account?') }} <a class="font-bold text-brand-600 transition hover:text-brand-500 dark:text-brand-300" href="{{ route('register') }}">{{ __('Create one') }}</a>
</p>
@endsection