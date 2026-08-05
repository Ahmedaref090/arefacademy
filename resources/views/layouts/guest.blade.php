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
</head>
<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
    <div class="flex min-h-screen flex-col items-center justify-center p-4">
        <div class="mb-6 flex w-full max-w-md items-center justify-between">
            <a href="{{ route('home') }}" class="font-mono text-lg font-bold">
                <span class="rounded bg-brand-600 px-2 py-0.5 text-white">&lt;/&gt;</span> {{ __('Aref Academy') }}
            </a>
            <div class="flex items-center gap-2">
                {{-- Language switcher --}}
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('locale.switch', 'en') }}" class="btn-secondary px-3" title="English">EN</a>
                @else
                    <a href="{{ route('locale.switch', 'ar') }}" class="btn-secondary px-3" title="العربية">عربي</a>
                @endif
                <button type="button" onclick="toggleTheme()" class="btn-secondary">🌙</button>
            </div>
        </div>
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </div>
</body>
</html>
