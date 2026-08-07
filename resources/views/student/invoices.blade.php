@extends('layouts.app')
@section('title', __('Invoices & Subscriptions – Aref Academy'))

@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ __('Invoices & Subscriptions') }}</h1>

<div x-data="{ active: null }" @keydown.escape.window="active = null">
    <div class="card p-0">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800">
                    <th class="table-th">{{ __('Course') }}</th>
                    <th class="table-th">{{ __('Amount') }}</th>
                    <th class="table-th">{{ __('Date') }}</th>
                    <th class="table-th">{{ __('Payment Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800/50">
                        <td class="table-td font-medium">
                            {{ $payment->course->title }}
                            @php($displayMonths = $payment->displayMonths())
                            @if($displayMonths->isNotEmpty())
                                <div class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">
                                    @foreach($displayMonths as $month)
                                        <span class="mr-1 inline-block rounded-full bg-indigo-100 px-2 py-0.5 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">{{ $month->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="table-td font-mono">{{ number_format($payment->amount, 2) }} {{ __('EGP') }}</td>
                        <td class="table-td text-gray-400">{{ $payment->created_at?->format('Y-m-d') }}</td>
                        <td class="table-td">
                            <button type="button" @click="active = {{ $payment->id }}"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-full px-3 py-1 text-xs font-bold transition-transform duration-200 ease-in-out hover:scale-105 {{ $payment->isPending() ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300' : ($payment->isApproved() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400') }}">
                                <span class="h-2 w-2 rounded-full {{ $payment->isPending() ? 'animate-pulse bg-amber-500' : ($payment->isApproved() ? 'bg-emerald-500' : 'bg-red-500') }}"></span>
                                {{ $payment->isPending() ? __('Pending') : ($payment->isApproved() ? __('Approved') : __('Rejected')) }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="table-td text-gray-400" colspan="4">
                            <div class="flex flex-col items-center gap-2 py-10 text-center">
                                <span class="rounded-full bg-gray-100 p-3 dark:bg-gray-800">
                                    <x-icon name="credit" class="h-8 w-8 text-gray-400"/>
                                </span>
                                <span>{{ __('No invoices yet.') }}</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Payment details modal --}}
    <template x-teleport="body">
        <div x-show="active !== null" x-cloak
            class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/60 p-4 backdrop-blur-sm sm:items-center"
            @click.self="active = null">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-black/10 dark:bg-gray-900 dark:ring-white/10">
                {{-- Modal header --}}
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-100 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Payment Status') }}</h3>
                    <button type="button" @click="active = null" class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- Payment detail cards --}}
                @foreach($payments as $payment)
                    <div x-show="active === {{ $payment->id }}" x-cloak class="px-6 py-5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Course') }}</p>
                                <p class="truncate font-bold text-gray-900 dark:text-white">{{ $payment->course->title }}</p>
                            </div>
                            <div class="text-end">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Amount') }}</p>
                                <p class="font-mono text-lg font-bold text-gray-900 dark:text-white">{{ number_format($payment->amount, 2) }} <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('EGP') }}</span></p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                            @php($displayMonths = $payment->displayMonths())
                            @if($displayMonths->isNotEmpty())
                                <div class="flex items-start justify-between gap-3 text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('Months (:count)', ['count' => $displayMonths->count()]) }}</span>
                                    <span class="text-end font-semibold text-gray-900 dark:text-white">
                                        @foreach($displayMonths as $month)
                                            <span class="block">{{ $month->name }}</span>
                                        @endforeach
                                    </span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Payment Method') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $payment->payment_method?->label() ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Sender Number / Account') }}</span>
                                <span class="font-mono font-semibold text-gray-900 dark:text-white" dir="ltr">{{ $payment->sender_details }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Status') }}</span>
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $payment->isPending() ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300' : ($payment->isApproved() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400') }}">
                                    <span class="h-2 w-2 rounded-full {{ $payment->isPending() ? 'animate-pulse bg-amber-500' : ($payment->isApproved() ? 'bg-emerald-500' : 'bg-red-500') }}"></span>
                                    {{ $payment->isPending() ? __('Pending') : ($payment->isApproved() ? __('Approved') : __('Rejected')) }}
                                </span>
                            </div>
                        </div>

                        @if($payment->receipt_image_path)
                            <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                                <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Attached Receipt') }}</p>
                                @php($receiptUrl = route('payments.receipt', $payment))
                                <a href="{{ $receiptUrl }}" target="_blank" class="group block overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                    <img src="{{ $receiptUrl }}" alt="{{ __('Receipt') }}" class="max-h-48 w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]">
                                </a>
                                <a href="{{ $receiptUrl }}" target="_blank" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    {{ __('View Receipt') }}
                                </a>
                            </div>
                        @endif

                        <div class="mt-4 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                            <button type="button" @click="active = null" class="btn-secondary">{{ __('Close') }}</button>
                            <a href="{{ route('payments.show', $payment) }}" class="btn">{{ __('View Full Page') }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </template>
</div>
@endsection