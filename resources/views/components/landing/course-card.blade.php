@props(['course', 'gradeKey' => 'general'])

{{-- Must be rendered inside an Alpine scope providing `tab` (see the courses section in welcome.blade.php). --}}
<a href="{{ route('login') }}"
   x-show="tab === 'all' || tab === '{{ $gradeKey }}'"
   x-transition:enter="transition ease-out duration-300"
   x-transition:enter-start="opacity-0 scale-95"
   x-transition:enter-end="opacity-100 scale-100"
   class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
    <div class="aspect-video w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
        @if($course->thumbnail)
            <img src="{{ Storage::disk('public')->url($course->thumbnail) }}" alt="{{ $course->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center font-mono text-4xl text-brand-300 dark:text-brand-700">&lt;/&gt;</div>
        @endif
    </div>
    <div class="p-5">
        <h3 class="font-bold">{{ $course->title }}</h3>
        <p class="mt-1 line-clamp-2 text-sm text-slate-500 dark:text-slate-400">{{ $course->description }}</p>
        <div class="mt-4 flex items-center justify-between">
            <span class="font-mono font-bold text-brand-600 dark:text-gold-400">
                {{ (float) $course->price > 0 ? number_format($course->price, 2) . ' ' . __('EGP') : __('Free') }}
            </span>
            <span class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white transition group-hover:bg-brand-700">{{ __('Subscribe') }}</span>
        </div>
    </div>
</a>
