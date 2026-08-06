@extends('layouts.app')
@section('title', 'حالة الدفع – Aref Academy')

@section('content')
<div class="mx-auto max-w-xl" dir="rtl">
    <h1 class="mb-6 text-2xl font-bold">حالة الدفع</h1>

    @if(session('status'))
        <div class="card mb-4 border-emerald-500 text-sm">{{ session('status') }}</div>
    @endif

    <div class="card space-y-4">
        <div class="flex items-center justify-between gap-4">
            <span class="font-semibold">{{ $payment->course->title }}</span>
            <span class="font-mono font-bold">{{ number_format($payment->