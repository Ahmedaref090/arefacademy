<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
<body class="min-h-screen bg-white font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
@php($u = auth()->user())

{{-- Header --}}
<header x-data="{ open: false }" class="sticky top-0 z-50 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-950/80">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="shrink-0 font-mono text-lg font-bold">
            <span class="rounded bg-brand-600 px-2 py-0.5 text-white">&lt;/&gt;</span> {{ __('Aref Academy') }}
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
            <a href="{{ route('home') }}#courses" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-gold-400">{{ __('Courses') }}</a>
            <a href="{{ route('home') }}#faq" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300 dark:hover:text-gold-400">{{ __('Frequently Asked Questions') }}</a>
        </nav>

        {{-- Desktop actions --}}
        <div class="hidden items-center gap-2 md:flex">
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('locale.switch', 'en') }}" class="btn-secondary px-3" title="English">EN</a>
            @else
                <a href="{{ route('locale.switch', 'ar') }}" class="btn-secondary px-3" title="العربية">عربي</a>
            @endif
            <button type="button" onclick="toggleTheme()" class="btn-secondary" title="{{ __('Toggle theme') }}">🌙</button>
            @auth
                <a href="{{ $u->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">{{ __('Dashboard') }}</a>
            @else
                <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">{{ __('Login') }}</a>
                <a href="{{ route('register') }}" class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">{{ __('Get Started') }}</a>
            @endauth
        </div>

        {{-- Mobile actions --}}
        <div class="flex items-center gap-2 md:hidden">
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('locale.switch', 'en') }}" class="btn-secondary px-3" title="English">EN</a>
            @else
                <a href="{{ route('locale.switch', 'ar') }}" class="btn-secondary px-3" title="العربية">عربي</a>
            @endif
            <button type="button" onclick="toggleTheme()" class="btn-secondary" title="{{ __('Toggle theme') }}">🌙</button>
            <button type="button" @click="open = !open" class="btn-secondary" title="{{ __('Menu') }}">☰</button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak @click.outside="open = false" x-transition
         class="border-t border-slate-200 px-4 pb-4 pt-2 dark:border-slate-800 md:hidden">
        <nav class="flex flex-col gap-1 text-sm font-medium">
            <a href="{{ route('home') }}#courses" @click="open = false" class="rounded-lg px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Courses') }}</a>
            <a href="{{ route('home') }}#faq" @click="open = false" class="rounded-lg px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">{{ __('Frequently Asked Questions') }}</a>
        </nav>
        <div class="mt-3 flex flex-col gap-2 border-t border-slate-200 pt-3 dark:border-slate-800">
            @auth
                <a href="{{ $u->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="rounded-xl bg-brand-600 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-brand-700">{{ __('Dashboard') }}</a>
            @else
                <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-center text-sm font-semibold transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">{{ __('Login') }}</a>
                <a href="{{ route('register') }}" class="rounded-xl bg-brand-600 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-brand-700">{{ __('Get Started') }}</a>
            @endauth
        </div>
    </div>
</header>

{{-- Page content — fluid, no fixed width constraint --}}
<main>
    @yield('content')
</main>

{{-- Footer --}}
<footer class="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <a href="{{ route('home') }}" class="font-mono text-lg font-bold">
                    <span class="rounded bg-brand-600 px-2 py-0.5 text-white">&lt;/&gt;</span> {{ __('Aref Academy') }}
                </a>
                <p class="mt-3 max-w-xs text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Learn programming the right way.') }}
                </p>
            </div>
            <div>
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-400">{{ __('Quick Links') }}</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300">{{ __('Home') }}</a></li>
                    <li><a href="{{ route('home') }}#courses" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300">{{ __('Courses') }}</a></li>
                    <li><a href="{{ route('home') }}#faq" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300">{{ __('Frequently Asked Questions') }}</a></li>
                    <li><a href="{{ route('login') }}" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300">{{ __('Login') }}</a></li>
                    <li><a href="{{ route('register') }}" class="text-slate-600 transition hover:text-brand-600 dark:text-slate-300">{{ __('Register') }}</a></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-400">{{ __('Follow Us') }}</h3>
                <div class="flex gap-3">
                    <a href="#" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-300 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800" title="Facebook">📘</a>
                    <a href="#" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-300 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800" title="YouTube">▶️</a>
                    <a href="#" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-300 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800" title="Instagram">📸</a>
                </div>
            </div>
        </div>
        <div class="mt-10 border-t border-slate-200 pt-6 text-center text-sm text-slate-400 dark:border-slate-800">
            © {{ date('Y') }} {{ __('Aref Academy') }}. {{ __('All rights reserved.') }}
        </div>
    </div>
</footer>
</body>
</html>
