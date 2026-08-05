<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Aref in Programming'))</title>
    <script>
        // Dark mode: respect saved choice, else system preference.
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        function toggleTheme() {
            const dark = document.documentElement.classList.toggle('dark');
            localStorage.theme = dark ? 'dark' : 'light';
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-200">

    {{-- Top navigation --}}
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-extrabold tracking-tight">
                <span class="rounded-lg bg-burgundy-600 px-2 py-1 text-white">&lt;/&gt;</span>
                <span>{{ __('Aref in Programming') }}</span>
            </a>

            <div class="flex items-center gap-2">
                {{-- Language switcher --}}
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('locale.switch', 'en') }}" class="rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">EN</a>
                @else
                    <a href="{{ route('locale.switch', 'ar') }}" class="rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">عربي</a>
                @endif

                {{-- Dark mode toggle --}}
                <button type="button" onclick="toggleTheme()" class="rounded-lg px-3 py-1.5 text-sm hover:bg-slate-100 dark:hover:bg-slate-800" title="{{ __('Toggle theme') }}">🌙 / ☀️</button>

                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="rounded-lg bg-burgundy-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-burgundy-700">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-4 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Login') }}</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-burgundy-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-burgundy-700">{{ __('Register') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-16 border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
            <div>
                <div class="mb-3 flex items-center gap-2 text-lg font-extrabold">
                    <span class="rounded-lg bg-burgundy-600 px-2 py-1 text-white">&lt;/&gt;</span>
                    {{ __('Aref in Programming') }}
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Learn programming the right way.') }}</p>
            </div>
            <div>
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-400">{{ __('Quick Links') }}</h3>
                <ul class="space-y-2 text-sm">
                    <li><a class="hover:text-burgundy-600" href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li><a class="hover:text-burgundy-600" href="{{ route('home') }}#courses">{{ __('Courses') }}</a></li>
                    <li><a class="hover:text-burgundy-600" href="{{ route('login') }}">{{ __('Login') }}</a></li>
                    <li><a class="hover:text-burgundy-600" href="{{ route('register') }}">{{ __('Register') }}</a></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-400">{{ __('Contact') }}</h3>
                <ul class="space-y-2 text-sm text-slate-500 dark:text-slate-400">
                    <li>📞 <span dir="ltr">+20 100 000 0000</span></li>
                    <li>✉️ <span dir="ltr">info@example.com</span></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-400">{{ __('Follow Us') }}</h3>
                <div class="flex gap-3 text-xl">
                    <a href="#" class="hover:text-burgundy-600" title="Facebook">📘</a>
                    <a href="#" class="hover:text-burgundy-600" title="YouTube">▶️</a>
                    <a href="#" class="hover:text-burgundy-600" title="WhatsApp">💬</a>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-200 py-4 text-center text-xs text-slate-400 dark:border-slate-800">
            {{ __('All rights reserved.') }} © {{ date('Y') }} {{ __('Aref in Programming') }}
        </div>
    </footer>
</body>
</html>
