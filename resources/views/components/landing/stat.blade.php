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
    class="card-hover flex items-center gap-5 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/70"
>
    <span class="icon-tile-soft !h-14 !w-14 !rounded-2xl"><x-icon name="{{ $icon }}" class="h-6 w-6" :stroke="1.8"/></span>
    <div>
        <div class="font-mono text-3xl font-extrabold text-gradient" dir="ltr">
            <span x-text="display.toLocaleString('en-US')">0</span><span>{{ $suffix }}</span>
        </div>
        <div class="text-sm text-slate-500 dark:text-slate-400">{{ $label }}</div>
    </div>
</div>
