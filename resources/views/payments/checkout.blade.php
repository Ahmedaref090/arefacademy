@extends('layouts.app')
@section('title', 'اشترك في ' . $course->title . ' – Aref Academy')

@section('content')
<div class="mx-auto max-w-2xl" dir="rtl">
    <h1 class="mb-6 text-2xl font-bold">إتمام الاشتراك في الكورس</h1>

    {{-- Course summary --}}
    <div class="card mb-6 flex items-center justify-between gap-4">
        <div>
            <div class="font-semibold">{{ $course->title }}</div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">اشتراك شهري (٣٠ يومًا من تاريخ التفعيل)</div>
        </div>
        <div class="font-mono text-xl font-bold text-indigo-600 dark:text-indigo-400">
            {{ (float) $course->price > 0 ? number_format($course->price, 2) . ' EGP' : 'مجاني' }}
        </div>
    </div>

    @if((float) $course->price <= 0)
        <form method="POST" action="{{ route('payments.pay', $course) }}" class="card">
            @csrf
            <button class="btn w-full">اشترك مجانًا</button>
        </form>
    @else
        {{-- Step 1: transfer instructions --}}
        <div class="card mb-6">
            <h2 class="mb-3 font-semibold">١. حوّل المبلغ بإحدى الطريقتين:</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-500/20 dark:bg-red-500/10">
                    <div class="mb-1 font-semibold text-red-700 dark:text-red-400">فودافون كاش</div>
                    <div class="font-mono text-lg font-bold tracking-wider" dir="ltr">01064788073</div>
                </div>
                <div class="rounded-lg border border-purple-200 bg-purple-50 p-4 dark:border-purple-500/20 dark:bg-purple-500/10">
                    <div class="mb-1 font-semibold text-purple-700 dark:text-purple-400">انستاباي (InstaPay)</div>
                    <div class="font-mono text-lg font-bold tracking-wider" dir="ltr">01068014651</div>
                </div>
            </div>
            <p class="mt-3 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                بعد التحويل، املأ النموذج بالأسفل وأرفق لقطة شاشة (سكرين شوت) لإيصال التحويل.
                سيتم تفعيل اشتراكك بعد مراجعة الإدارة للإيصال.
            </p>
        </div>

        {{-- Step 2: receipt form --}}
        <form method="POST" action="{{ route('payments.pay', $course) }}" enctype="multipart/form-data" class="card space-y-4">
            @csrf
            <h2 class="font-semibold">٢. أرسل بيانات الدفع:</h2>

            <div>
                <span class="label">طريقة الدفع التي استخدمتها</span>
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
                <label class="label" for="sender_details">رقم المحفظة / حساب InstaPay الذي حوّلت منه</label>
                <input class="input" id="sender_details" name="sender_details" value="{{ old('sender_details') }}" placeholder="01012345678" dir="ltr" required>
                @error('sender_details') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="receipt">صورة إيصال التحويل (سكرين شوت)</label>
                <input class="input" id="receipt" name="receipt" type="file" accept="image/*" required>
                @error('receipt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button class="btn w-full">إرسال للمراجعة</button>
        </form>
    @endif
</div>
@endsection
