@props(['question' => '', 'answer' => ''])

<div x-data="{ open: false }"
     class="card-hover overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/70"
     :class="open && 'border-brand-300/70 shadow-[var(--shadow-glow)] dark:border-brand-600/40'"
     data-reveal>
    <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start text-base font-bold">
        <span>{{ $question }}</span>
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-600 to-fuchsia-600 text-white shadow-md shadow-brand-500/30 transition-transform duration-300"
              :class="open && 'rotate-180'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><polyline points="6 9 12 15 18 9"/></svg>
        </span>
    </button>
    <div x-show="open" x-cloak x-transition.opacity.duration.300ms
         class="px-6 pb-6 text-[15px] leading-relaxed text-slate-500 dark:text-slate-400">
        {{ $answer }}
    </div>
</div>
