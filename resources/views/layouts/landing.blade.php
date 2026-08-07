<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <title>@yield('title', __('Aref Academy'))</title>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
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
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
@php($u = auth()->user())

{{-- Header --}}
<header x-data="{ open: false }" class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/70 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/70">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5 font-mono text-lg font-extrabold">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-brand-600 to-fuchsia-600 text-xs text-white shadow-lg shadow-brand-500/40">&lt;/&gt;</span>
            <span class="text-gradient">{{ __('Aref Academy') }}</span>
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden items-center gap-7 text-sm font-semibold md:flex">
            <a href="{{ route('home') }}#courses" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-gold-400">{{ __('Courses') }}</a>
            <a href="{{ route('home') }}#how" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-gold-400">{{ __('How it works') }}</a>
            <a href="{{ route('home') }}#faq" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-gold-400">{{ __('FAQ') }}</a>
        </nav>

        {{-- Desktop actions --}}
        <div class="hidden items-center gap-2.5 md:flex">
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('locale.switch', 'en') }}" class="btn-secondary px-3.5 py-2 text-xs font-bold" title="English"><x-icon name="globe" class="h-4 w-4"/> EN</a>
            @else
                <a href="{{ route('locale.switch', 'ar') }}" class="btn-secondary px-3.5 py-2 text-xs font-bold" title="العربية"><x-icon name="globe" class="h-4 w-4"/> عربي</a>
            @endif
            <button type="button" onclick="toggleTheme()" class="btn-secondary !px-3 py-2" title="{{ __('Toggle theme') }}"><x-icon name="moon" class="h-4 w-4" :stroke="2"/></button>
            @auth
                <a href="{{ $u->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="btn"><x-icon name="dashboard" class="h-4 w-4"/> {{ __('Dashboard') }}</a>
            @else
                <a href="{{ route('login') }}" class="btn-secondary"> {{ __('Login') }}</a>
                <a href="{{ route('register') }}" class="btn"><x-icon name="sparkles" class="h-4 w-4" :stroke="2"/> {{ __('Get Started') }}</a>
            @endauth
        </div>

        {{-- Mobile actions --}}
        <div class="flex items-center gap-2 md:hidden">
            <button type="button" onclick="toggleTheme()" class="btn-secondary !px-3 py-2"><x-icon name="moon" class="h-5 w-5" :stroke="2"/></button>
            <button type="button" @click="open = !open" class="btn-secondary !px-3 py-2" title="{{ __('Menu') }}"><x-icon name="menu" class="h-5 w-5" :stroke="2"/></button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak @click.outside="open = false" x-transition
         class="border-t border-slate-200/70 px-4 pb-4 pt-3 dark:border-slate-800 md:hidden">
        <nav class="flex flex-col gap-1 text-sm font-semibold">
            <a href="{{ route('home') }}#courses" @click="open = false" class="rounded-xl px-3 py-2.5 hover:bg-slate-100 dark:hover:bg-white/5">{{ __('Courses') }}</a>
            <a href="{{ route('home') }}#how" @click="open = false" class="rounded-xl px-3 py-2.5 hover:bg-slate-100 dark:hover:bg-white/5">{{ __('How it works') }}</a>
            <a href="{{ route('home') }}#faq" @click="open = false" class="rounded-xl px-3 py-2.5 hover:bg-slate-100 dark:hover:bg-white/5">{{ __('FAQ') }}</a>
        </nav>
        <div class="mt-3 flex flex-col gap-2 border-t border-slate-200/70 pt-3 dark:border-slate-800">
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('locale.switch', 'en') }}" class="btn-secondary w-full"><x-icon name="globe" class="h-4 w-4"/> English</a>
            @else
                <a href="{{ route('locale.switch', 'ar') }}" class="btn-secondary w-full"><x-icon name="globe" class="h-4 w-4"/> العربية</a>
            @endif
            @auth
                <a href="{{ $u->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="btn w-full">{{ __('Dashboard') }}</a>
            @else
                <a href="{{ route('login') }}" class="btn-secondary w-full">{{ __('Login') }}</a>
                <a href="{{ route('register') }}" class="btn w-full">{{ __('Get Started') }}</a>
            @endauth
        </div>
    </div>
</header>

{{-- Page content — fluid, no fixed width constraint --}}
<main>
    @yield('content')
</main>

{{-- Footer --}}
<footer class="border-t border-slate-200/70 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-900">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-mono text-lg font-extrabold">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-brand-600 to-fuchsia-600 text-xs text-white">&lt;/&gt;</span>
                    <span class="text-gradient">{{ __('Aref Academy') }}</span>
                </a>
                <p class="mt-3 max-w-sm text-pretty text-slate-500 dark:text-slate-400">
                    {{ __('Learn programming the right way.') }}
                </p>
                <div class="mt-4 flex gap-3">
                    <a href="#" class="icon-tile-soft !h-10 !w-10 !rounded-xl" title="Facebook"><x-icon name="chat" class="h-5 w-5"/></a>
                    <a href="#" class="icon-tile-soft !h-10 !w-10 !rounded-xl" title="YouTube"><x-icon name="play" class="h-5 w-5" :stroke="1.8"/></a>
                    <a href="#" class="icon-tile-soft !h-10 !w-10 !rounded-xl" title="Instagram"><x-icon name="camera" class="h-5 w-5" :stroke="1.8"/></a>
                </div>
            </div>
            <div>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-400">{{ __('Quick Links') }}</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('home') }}" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300">{{ __('Home') }}</a></li>
                    <li><a href="{{ route('home') }}#courses" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300">{{ __('Courses') }}</a></li>
                    <li><a href="{{ route('home') }}#faq" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300">{{ __('Frequently Asked Questions') }}</a></li>
                    <li><a href="{{ route('login') }}" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300">{{ __('Login') }}</a></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-4 text-sm font-black uppercase tracking-widest text-slate-400">{{ __('Get Started') }}</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('register') }}" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300">{{ __('Register') }}</a></li>
                    <li><a href="{{ route('home') }}#how" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300">{{ __('How it works') }}</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-10 border-t border-slate-200/70 pt-6 text-center text-sm text-slate-400 dark:border-slate-800">
            © {{ date('Y') }} {{ __('Aref Academy') }}. {{ __('All rights reserved.') }}
        </div>
    </div>
</footer>

@include('components.toasts')
</body>
</html>