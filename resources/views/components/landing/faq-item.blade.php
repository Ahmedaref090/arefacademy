@props(['question' => '', 'answer' => ''])

<div x-data="{ open: false }"
     class="overflow-hidden rounded-2xl border border-slate-200 bg-white transition dark:border-slate-800 dark:bg-slate-900"
     :class="open && 'border-brand-300 dark:border-brand-700'"
     data-reveal>
    <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-start font-semibold">
        <span>{{ $question }}</span>
        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-600/10 text-brand-600 transition-transform duration-300 dark:text-gold-400"
              :class="open && 'rotate-180'">▾</span>
    </button>
    <div x-show="open" x-cloak x-transition.opacity.duration.300ms
         class="px-5 pb-5 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
        {{ $answer }}
    </div>
</div>
