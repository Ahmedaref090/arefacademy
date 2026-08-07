@extends('layouts.app')
@section('title', __('Payment Status – Aref Academy'))

@section('content')
<div class="mx-auto max-w-2xl">
    {{-- Page header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Payment Status') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Transaction number') }} <span class="font-mono" dir="ltr">#{{ $payment->id }}</span></p>
        </div>
        {{-- Status badge (header) --}}
        @if($payment->isPending())
            <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3.5 py-1.5 text-xs font-bold text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-500 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
                </span>
                {{ __('Pending') }}
            </span>
        @elseif($payment->isApproved())
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3.5 py-1.5 text-xs font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                {{ __('Approved') }}
            </span>
        @else
            <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3.5 py-1.5 text-xs font-bold text-red-700 dark:bg-red-500/10 dark:text-red-400">
                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                {{ __('Rejected') }}
            </span>
        @endif
    </div>

    {{-- Main card --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

        {{-- Course + amount header --}}
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 bg-gray-50/50 px-6 py-5 dark:border-gray-800 dark:bg-gray-950/30">
            <div class="min-w-0">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Course') }}</p>
                <h2 class="mt-1 truncate text-lg font-bold text-gray-900 dark:text-white">{{ $payment->course->title }}</h2>
            </div>
            <div class="text-end">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Amount') }}</p>
                <p class="mt-1 font-mono text-xl font-bold text-gray-900 dark:text-white">{{ number_format($payment->amount, 2) }} <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('EGP') }}</span></p>
            </div>
        </div>

        {{-- Detail cells (hairline-separated two-column grid) --}}
        <div class="grid gap-px bg-gray-100 dark:bg-gray-800 sm:grid-cols-2">

            {{-- Course month(s) --}}
            @php($displayMonths = $payment->displayMonths())
            @if($displayMonths->isNotEmpty())
                <div class="flex items-start gap-3 bg-white px-6 py-4 dark:bg-gray-900">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4.5 w-4.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Months (:count)', ['count' => $displayMonths->count()]) }}</p>
                        <div class="mt-0.5 flex flex-wrap gap-1">
                            @foreach($displayMonths as $month)
                                <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">{{ $month->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Payment method --}}
            <div class="flex items-center gap-3 bg-white px-6 py-4 dark:bg-gray-900">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4.5 w-4.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 0 4.5 6h15a.75.75 0 0 0 .75-.75v-.75m-16.5.75a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25m-16.5 0v13.5a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25V6.75" /></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Payment Method') }}</p>
                    <p class="mt-0.5 font-bold text-gray-900 dark:text-white">{{ $payment->payment_method?->label() ?? '—' }}</p>
                </div>
            </div>

            {{-- Sender details --}}
            <div class="flex items-center gap-3 bg-white px-6 py-4 dark:bg-gray-900">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4.5 w-4.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Sender Number / Account') }}</p>
                    <p class="mt-0.5 truncate font-mono font-bold text-gray-900 dark:text-white" dir="ltr">{{ $payment->sender_details }}</p>
                </div>
            </div>

            {{-- Request date --}}
            <div class="flex items-center gap-3 bg-white px-6 py-4 dark:bg-gray-900">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4.5 w-4.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Request Date') }}</p>
                    <p class="mt-0.5 font-bold text-gray-900 dark:text-white">{{ $payment->created_at?->format('Y/m/d') }} <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $payment->created_at?->format('H:i') }}</span></p>
                </div>
            </div>
        </div>

        {{-- Receipt viewer card --}}
        @if($payment->receipt_image_path)
            @php($receiptUrl = route('payments.receipt', $payment))
            @php($receiptName = basename($payment->receipt_image_path))
            <div class="border-t border-gray-100 px-6 py-5 dark:border-gray-800">
                <p class="mb-3 flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" /></svg>
                    {{ __('Attached Receipt') }}
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5.5 w-5.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-bold text-gray-900 dark:text-white" dir="ltr">{{ $receiptName }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Receipt Image') }}</p>
                        </div>
                    </div>
                    <a href="{{ $receiptUrl }}" target="_blank" class="btn inline-flex shrink-0 items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        {{ __('View Receipt') }}
                    </a>
                </div>
                <a href="{{ $receiptUrl }}" target="_blank" class="group mt-4 block overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <img src="{{ $receiptUrl }}" alt="{{ __('Receipt') }}" class="max-h-64 w-full object-cover transition-transform duration-300 ease-in-out group-hover:scale-[1.02]">
                </a>
            </div>
        @endif

        {{-- Action area per status --}}
        @if($payment->isApproved())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-emerald-50/60 px-6 py-5 dark:border-gray-800 dark:bg-emerald-500/5 sm:col-span-2">
                <p class="flex items-center gap-2 text-sm font-semibold text-emerald-700 dark:text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    {{ __('Your subscription has been activated successfully') }}
                </p>
                <a href="{{ route('courses.show', $payment->course) }}" class="btn">{{ __('Go to Course') }}</a>
            </div>
        @elseif($payment->isRejected())
            <div class="space-y-3 border-t border-gray-100 px-6 py-5 dark:border-gray-800 sm:col-span-2">
                @if($payment->rejection_reason)
                    <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mt-0.5 h-5 w-5 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                        <div>
                            <p class="font-bold">{{ __('Rejection reason:') }}</p>
                            <p class="mt-0.5">{{ $payment->rejection_reason }}</p>
                        </div>
                    </div>
                @endif
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('This payment was rejected. Check your transfer details and try again.') }}</p>
                <a href="{{ route('payments.checkout', $payment->course) }}" class="btn block w-full text-center">{{ __('Try Again') }}</a>
            </div>
        @else
            <div class="flex items-center gap-3 border-t border-gray-100 bg-amber-50/60 px-6 py-5 text-sm font-semibold text-amber-800 dark:border-gray-800 dark:bg-amber-500/5 dark:text-amber-200 sm:col-span-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                {{ __('Your receipt will be reviewed and your subscription activated soon.') }}
            </div>
        @endif
    </div>
</div>
@endsection
