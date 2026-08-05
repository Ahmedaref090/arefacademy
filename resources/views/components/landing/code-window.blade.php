{{-- Animated "typing" code editor for the landing hero. --}}
{{-- Note: '<?php' is split in JS so Blade/PHP never sees a raw opening tag. --}}
<script>
    window.codeTyper = window.codeTyper || function () {
        return {
            lines: [],
            current: '',
            currentCls: '',
            lineIndex: 0,
            charIndex: 0,
            source: [
                { text: '<' + '?php', cls: 'text-fuchsia-400' },
                { text: "$student = new Student('you');", cls: 'text-sky-300' },
                { text: "$student->learn('Laravel')->build('projects');", cls: 'text-slate-200' },
                { text: 'while ($student->isLearning()) {', cls: 'text-amber-300' },
                { text: '    $student->levelUp();', cls: 'text-slate-200' },
                { text: '}', cls: 'text-amber-300' },
                { text: '// يلا نبدأ رحلتك 🚀', cls: 'text-emerald-300' },
            ],
            init() {
                this.type();
            },
            type() {
                const line = this.source[this.lineIndex];
                this.currentCls = line.cls;
                if (this.charIndex <= line.text.length) {
                    this.current = line.text.slice(0, this.charIndex++);
                    setTimeout(() => this.type(), 45);
                    return;
                }
                this.lines.push(line);
                this.current = '';
                this.charIndex = 0;
                this.lineIndex++;
                if (this.lineIndex >= this.source.length) {
                    setTimeout(() => {
                        this.lines = [];
                        this.lineIndex = 0;
                        this.type();
                    }, 4500);
                } else {
                    setTimeout(() => this.type(), 350);
                }
            },
        };
    };
</script>

<div x-data="codeTyper()" dir="ltr" class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 text-left shadow-2xl">
    <div class="flex items-center gap-2 border-b border-slate-800 px-4 py-3">
        <span class="h-3 w-3 rounded-full bg-red-500"></span>
        <span class="h-3 w-3 rounded-full bg-gold-400"></span>
        <span class="h-3 w-3 rounded-full bg-green-500"></span>
        <span class="ms-3 font-mono text-xs text-slate-500">journey.php</span>
    </div>
    <pre class="min-h-56 p-5 font-mono text-sm leading-7"><code><template x-for="(line, i) in lines" :key="i"><div :class="line.cls" x-text="line.text"></div></template><div :class="currentCls"><span x-text="current"></span><span class="animate-pulse text-gold-400">▌</span></div></code></pre>
</div>
