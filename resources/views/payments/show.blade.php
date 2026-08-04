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
            <span class="font-mono font-bold">{{ number_format($payment->amount, 2) }} EGP</span>
        </div>

        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">طريقة الدفع</span>
            <span>{{ $payment->payment_method?->label() ?? '—' }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">الرقم / الحساب المُرسل</span>
            <span class="font-mono" dir="ltr">{{ $payment->sender_details }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500 dark:text-gray-400">الحالة</span>
            @if($payment->isPending())
                <span class="badge bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">قيد المراجعة</span>
            @elseif($payment->isApproved())
                <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">تم القبول</span>
            @else
                <span class="badge bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400">مرفوض</span>
            @endif
        </div>

        @if($payment->receipt_image_path)
            <div>
                <div class="label">الإيصال المرفق</div>
                <a href="{{ Storage::disk('public')->url($payment->receipt_image_path) }}" target="_blank">
                    <img src="{{ Storage::disk('public')->url($payment->receipt_image_path) }}" alt="Receipt" class="mt-1 max-h-64 rounded-lg border border-gray-200 dark:border-gray-700">
                </a>
            </div>
        @endif

        @if($payment->isApproved())
            <a href="{{ route('courses.show', $payment->course) }}" class="btn block text-center">الانتقال إلى الكورس</a>
        @elseif($payment->isRejected())
            <p class="text-sm text-red-600">تم رفض هذا الدفع. تأكد من بيانات التحويل ثم أعد المحاولة.</p>
            <a href="{{ route('payments.checkout', $payment->course) }}" class="btn block text-center">إعادة المحاولة</a>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">سيتم مراجعة الإيصال وتفعيل اشتراكك قريبًا.</p>
        @endif
    </div>
</div>
@endsection
