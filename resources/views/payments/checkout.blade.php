@extends('layouts.app')
@section('title', 'Checkout – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">Checkout</h1>

<div class="card max-w-xl">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="font-semibold">{{ $course->title }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $course->lessons->count() }} lessons @if($course->duration_weeks) · {{ $course->duration_weeks }} weeks @endif</div>
        </div>
        <div class="font-mono text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($course->price, 2) }} EGP</div>
    </div>

    <div class="mb-4 rounded-lg bg-gray-100 p-3 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300">
        Pay easily via <strong>Fawry</strong>: you'll get a reference number to pay at any Fawry outlet or via myFawry app. Your course unlocks automatically once payment is confirmed.
    </div>

    <form method="POST" action="{{ route('payments.pay', $course) }}">
        @csrf
        <button class="btn w-full">{{ (float) $course->price > 0 ? 'Pay with Fawry' : 'Enroll for Free' }}</button>
    </form>
</div>
@endsection
