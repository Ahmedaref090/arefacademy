@props(['icon' => '📊', 'value' => 0, 'label' => '', 'suffix' => '+'])

<div
    x-data="{
        display: 0,
        target: {{ (int) $value }},
        started: false,
        start() {
            if (this.started) return;
            this.started = true;
            const duration = 1600;
            const t0 = performance.now();
            const tick = (t) => {
                const p = Math.min((t - t0) / duration, 1);
                this.display = Math.round(this.target * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        }
    }"
    x-init="
        const io = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) { start(); io.disconnect(); }
        }, { threshold: 0.4 });
        io.observe($el);
    "
    class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950"
>
    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-600/10 text-2xl">{{ $icon }}</span>
    <div>
        <div class="font-mono text-2xl font-extrabold text-brand-700 dark:text-gold-400" dir="ltr">
            <span x-text="display.toLocaleString('en-US')">0</span><span>{{ $suffix }}</span>
        </div>
        <div class="text-sm text-slate-500 dark:text-slate-400">{{ $label }}</div>
    </div>
</div>
