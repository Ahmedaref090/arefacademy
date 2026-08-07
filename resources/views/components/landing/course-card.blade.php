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
   class="card-hover group overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
    <div class="relative h-56 w-full overflow-hidden bg-gradient-to-br from-brand-100 to-fuchsia-100 dark:from-slate-800 dark:to-slate-800">
        @if($course->thumbnailUrl())
            <img src="{{ $course->thumbnailUrl() }}" alt="{{ $course->title }}" class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-110 group-hover:rotate-1">
        @else
            <div class="flex h-full items-center justify-center font-mono text-4xl text-brand-300 dark:text-brand-700">&lt;/&gt;</div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
    </div>
    <div class="p-6">
        <h3 class="text-lg font-extrabold">{{ $course->title }}</h3>
        <p class="mt-1.5 line-clamp-2 text-sm text-slate-500 dark:text-slate-400">{{ $course->description }}</p>
        <div class="mt-5 flex items-center justify-between">
            <div class="flex flex-wrap items-baseline gap-x-2">
                @if($hasDiscount)
                    <span class="font-mono text-sm text-slate-400 line-through">{{ number_format($course->price, 2) }} {{ __('EGP') }}</span>
                    <span class="font-mono text-lg font-extrabold text-rose-500 dark:text-rose-400">
                        {{ $effectivePrice > 0 ? number_format($effectivePrice, 2) . ' ' . __('EGP') : __('Free') }}
                    </span>
                @else
                    <span class="font-mono text-lg font-extrabold text-brand-600 dark:text-gold-400">
                        {{ $effectivePrice > 0 ? number_format($effectivePrice, 2) . ' ' . __('EGP') : __('Free') }}
                    </span>
                @endif
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-br from-brand-600 to-fuchsia-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-brand-500/25 transition-all duration-300 group-hover:shadow-lg group-hover:shadow-brand-500/40 group-hover:brightness-110">{{ __('Subscribe') }}</span>
        </div>
    </div>
</a>
