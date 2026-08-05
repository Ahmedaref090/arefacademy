@props(['course', 'gradeKey' => 'general'])

@php
    // A discount applies only when a sale price exists and is actually lower.
    $hasDiscount = ! is_null($course->sale_price) && (float) $course->sale_price < (float) $course->price;
    $effectivePrice = $hasDiscount ? (float) $course->sale_price : (float) $course->price;
@endphp

{{-- Must be rendered inside an Alpine scope providing `tab` (see the courses section in welcome.blade.php). --}}
<a href="{{ route('login') }}"
   x-show="tab === 'all' || tab === '{{ $gradeKey }}'"
   x-transition:enter="transition ease-out duration-300"
   x-transition:enter-start="opacity-0 scale-95"
   x-transition:enter-end="opacity-100 scale-100"
   class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
    <div class="h-56 w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
        @if($course->thumbnailUrl())
            <img src="{{ $course->thumbnailUrl() }}" alt="{{ $course->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center font-mono text-4xl text-brand-300 dark:text-brand-700">&lt;/&gt;</div>
        @endif
    </div>
    <div class="p-5">
        <h3 class="font-bold">{{ $course->title }}</h3>
        <p class="mt-1 line-clamp-2 text-sm text-slate-500 dark:text-slate-400">{{ $course->description }}</p>
        <div class="mt-4 flex items-center justify-between">
            <div class="flex flex-wrap items-baseline gap-x-2">
                @if($hasDiscount)
                    {{-- Original price struck through, sale price highlighted --}}
                    <span class="font-mono text-sm text-slate-400 line-through">{{ number_format($course->price, 2) }} {{ __('EGP') }}</span>
                    <span class="font-mono text-lg font-extrabold text-red-600 dark:text-red-400">
                        {{ $effectivePrice > 0 ? number_format($effectivePrice, 2) . ' ' . __('EGP') : __('Free') }}
                    </span>
                @else
                    <span class="font-mono font-bold text-brand-600 dark:text-gold-400">
                        {{ $effectivePrice > 0 ? number_format($effectivePrice, 2) . ' ' . __('EGP') : __('Free') }}
                    </span>
                @endif
            </div>
            <span class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white transition group-hover:bg-brand-700">{{ __('Subscribe') }}</span>
        </div>
    </div>
</a>
