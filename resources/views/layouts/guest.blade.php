<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <title>@yield('title', __('Aref Academy'))</title>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        function toggleTheme() {
            const dark = document.documentElement.classList.toggle('dark');
            localStorage.theme = dark ? 'dark' : 'light';
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="aurora-bg-light min-h-screen overflow-x-clip font-sans text-slate-900 antialiased dark:text-slate-100">

    {{-- Floating decorative blobs --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="blob left-[-10%] top-[-8%] h-[28rem] w-[28rem] bg-brand-500/50"></div>
        <div class="blob right-[-8%] top-[22%] h-[24rem] w-[24rem] bg-fuchsia-500/40" style="animation-delay:-5s"></div>
        <div class="blob bottom-[-12%] left-[18%] h-[26rem] w-[26rem] bg-gold-400/40" style="animation-delay:-9s"></div>
    </div>

    <div class="relative flex min-h-screen flex-col items-center justify-center p-4">
        <div class="mb-6 flex w-full max-w-md items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-mono text-lg font-extrabold">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-brand-600 to-fuchsia-600 text-xs text-white shadow-lg shadow-brand-500/40">&lt;/&gt;</span>
                <span class="text-gradient">{{ __('Aref Academy') }}</span>
            </a>
            <div class="flex items-center gap-2">
                {{-- Language switcher --}}
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('locale.switch', 'en') }}" class="btn-secondary px-3.5 py-2 text-xs font-bold" title="English"><x-icon name="globe" class="h-4 w-4"/> EN</a>
                @else
                    <a href="{{ route('locale.switch', 'ar') }}" class="btn-secondary px-3.5 py-2 text-xs font-bold" title="العربية"><x-icon name="globe" class="h-4 w-4"/> عربي</a>
                @endif
                <button type="button" onclick="toggleTheme()" class="btn-secondary !px-3 py-2"><x-icon name="moon" class="h-4 w-4" :stroke="2"/></button>
            </div>
        </div>
        <div class="page-enter w-full max-w-md">
            <div class="card !bg-white/80 p-8 shadow-[var(--shadow-glow-lg)] backdrop-blur-xl dark:!bg-slate-900/80">
                @yield('content')
            </div>
        </div>
    </div>

    @include('components.toasts')
</body>
</html>