<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aref Academy')</title>
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
<body class="min-h-screen bg-gray-100 text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
@php($u = auth()->user())
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="hidden w-64 shrink-0 flex-col border-r border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 md:flex">
        <a href="{{ $u?->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="mb-8 flex items-center gap-2 font-mono text-lg font-bold">
            <span class="rounded bg-indigo-600 px-2 py-0.5 text-white">&lt;/&gt;</span> Aref Academy
        </a>

        <nav class="flex flex-1 flex-col gap-1 text-sm">
            @if($u?->isAdmin())
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}" href="{{ route('admin.dashboard') }}">📊 Dashboard</a>
                <a class="nav-link {{ request()->routeIs('admin.courses.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.courses.index') }}">📚 Courses</a>
                <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.students.index') }}">🎓 Students</a>
                <a class="nav-link {{ request()->routeIs('admin.submissions.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.submissions.index') }}">📝 Submissions</a>
            @else
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" href="{{ route('dashboard') }}">🏠 Home</a>
                <a class="nav-link {{ request()->routeIs('courses.my') ? 'nav-link-active' : '' }}" href="{{ route('courses.my') }}">📚 My Courses</a>
                <a class="nav-link {{ request()->routeIs('courses.index') ? 'nav-link-active' : '' }}" href="{{ route('courses.index') }}">🛒 All Courses</a>
                <a class="nav-link {{ request()->routeIs('profile.*') ? 'nav-link-active' : '' }}" href="{{ route('profile.edit') }}">👤 Profile</a>
            @endif
        </nav>

        <div class="mt-auto space-y-3 border-t border-gray-200 pt-4 dark:border-gray-800">
            <button type="button" onclick="toggleTheme()" class="btn-secondary w-full">🌙 / ☀️ Theme</button>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                <div class="font-semibold text-gray-700 dark:text-gray-200">{{ $u?->name }}</div>
                <div dir="ltr">{{ $u?->phone }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-secondary w-full">Log out</button>
            </form>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Mobile top bar --}}
        <header class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900 md:hidden">
            <span class="font-mono font-bold">&lt;/&gt; Aref Academy</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="toggleTheme()" class="btn-secondary">🌙</button>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-secondary">Out</button></form>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8">
            @if(session('status'))
                <div class="card mb-4 border-green-500 text-sm text-green-600 dark:text-green-400">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="card mb-4 border-red-500 text-sm text-red-600 dark:text-red-400">
                    <ul class="list-inside list-disc">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
