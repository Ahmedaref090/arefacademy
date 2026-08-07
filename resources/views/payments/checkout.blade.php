@extends('layouts.app')
@section('title', __('Subscribe to :course', ['course' => $course->title]) . ' – ' . __('Aref Academy'))

@section('content')
<div class="mx-auto max-w-2xl">
    <h1 class="mb-6 text-2xl font-bold">{{ __('Complete Your Subscription') }}</h1>

    {{-- Course summary --}}
    <div class="card mb-6 flex items-center justify-between gap-4">
        <div>
            <div class="font-semibold">{{ $course->title }}</div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($courseMonths->isNotEmpty())
                    {{ __('Selected months: :count', ['count' => $courseMonths->count()]) }}
                @else
                    {{ __('Full course') }}
                @endif
            </div>
        </div>
        <div class="font-mono text-xl font-bold text-indigo-600 dark:text-indigo-400">
            @if($courseMonths->isNotEmpty())
                {{ number_format($course->effectivePrice() * $courseMonths->count(), 2) }} {{ __('EGP') }}
            @else
                {{ $course->effectivePrice() > 0 ? number_format($course->effectivePrice(), 2) . ' ' . __('EGP') : __('Free') }}
            @endif
        </div>
    </div>

    {{-- Selected months (per-month courses) --}}
    @if($courseMonths->isNotEmpty())
        <div class="card mb-6 border-indigo-500 text-sm">
            <div class="mb-1 font-semibold text-indigo-700 dark:text-indigo-300">{{ __('You are paying for:') }}</div>
            <ul class="space-y-1">
                @foreach($courseMonths as $month)
                    <li class="flex items-center justify-between">
                        <span class="font-medium">{{ $month->name }}</span>
                        <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ number_format($course->effectivePrice(), 2) }} {{ __('EGP') }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-2 flex items-center justify-between border-t border-gray-200 pt-2 font-bold dark:border-gray-700">
                <span>{{ __('Total') }}</span>
                <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ number_format($course->effectivePrice() * $courseMonths->count(), 2) }} {{ __('EGP') }}</span>
            </div>
        </div>
    @endif

    @if($course->effectivePrice() <= 0)
        <form method="POST" action="{{ route('payments.pay', $course) }}" class="card">
            @csrf
            @foreach($courseMonths as $month)
                <input type="hidden" name="course_month_ids[]" value="{{ $month->id }}">
            @endforeach
            <button class="btn w-full">{{ __('Subscribe for Free') }}</button>
        </form>
    @else
        {{-- Step 1: transfer instructions --}}
        <div class="card mb-6">
            <h2 class="mb-3 font-semibold">{{ __('1. Transfer the amount using one of these methods:') }}</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-500/20 dark:bg-red-500/10">
                    <div class="mb-1 font-semibold text-red-700 dark:text-red-400">{{ __('Vodafone Cash') }}</div>
                    <div class="font-mono text-lg font-bold tracking-wider" dir="ltr">01064788073</div>
                </div>
                <div class="rounded-lg border border-purple-200 bg-purple-50 p-4 dark:border-purple-500/20 dark:bg-purple-500/10">
                    <div class="mb-1 font-semibold text-purple-700 dark:text-purple-400">{{ __('InstaPay') }}</div>
                    <div class="font-mono text-lg font-bold tracking-wider" dir="ltr">01068014651</div>
                </div>
            </div>
            <p class="mt-3 text-sm leading-relaxed text-gray-500 dark:text-gray-400">{{ __('After the transfer, fill in the form below and attach a screenshot of the transfer receipt. Your subscription will be activated after the admin reviews the receipt.') }}</p>
        </div>

        {{-- Step 2: receipt form --}}
        <form method="POST" action="{{ route('payments.pay', $course) }}" enctype="multipart/form-data" class="card space-y-4">
            @csrf
            @foreach($courseMonths as $month)
                <input type="hidden" name="course_month_ids[]" value="{{ $month->id }}">
            @endforeach
            <h2 class="font-semibold">{{ __('2. Send the payment details:') }}</h2>

            <div>
                <span class="label">{{ __('The payment method you used') }}</span>
                <div class="mt-1 grid gap-2 sm:grid-cols-2">
                    @foreach($methods as $method)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 p-3 text-sm has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 dark:border-gray-700 dark:has-[:checked]:bg-indigo-500/10">
                            <input type="radio" name="payment_method" value="{{ $method->value }}" class="rounded-full" @checked(old('payment_method') === $method->value) required>
                            {{ $method->label() }}
                        </label>
                    @endforeach
                </div>
                @error('payment_method') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="sender_details">{{ __('Wallet number / InstaPay account you transferred from') }}</label>
                <input class="input" id="sender_details" name="sender_details" value="{{ old('sender_details') }}" placeholder="01012345678" dir="ltr" required data-phone maxlength="11" inputmode="numeric">
                @error('sender_details') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="receipt">{{ __('Transfer receipt image (screenshot)') }}</label>
                <input class="input" id="receipt" name="receipt" type="file" accept="image/*" required>
                @error('receipt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button class="btn w-full">{{ __('Submit for Review') }}</button>
        </form>
    @endif
</div>
@endsection
