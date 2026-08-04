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
                <a class="nav-link {{ request()->routeIs('admin.payments.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.payments.index') }}">💳 Payments</a>
                <a class="nav-link {{ request()->routeIs('admin.submissions.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.submissions.index') }}">📝 Submissions</a>
            @else
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" href="{{ route('dashboard') }}">🏠 Home</a>
                <a class="nav-link {{ request()->routeIs('courses.my') ? 'nav-link-active' : '' }}" href="{{ route('courses.my') }}">📚 My Courses</a>
                <a class="nav-link {{ request()->routeIs('courses.index') ? 'nav-link-active' : '' }}" href="{{ route('courses.index') }}">🛒 All Courses</a>
                <a class="nav-link" href="#" title="Coming soon">💬 Community</a>
                <a class="nav-link {{ request()->routeIs('profile.*') ? 'nav-link-active' : '' }}" href="{{ route('profile.edit') }}">👤 Account</a>
            @endif
        </nav>

        <div class="mt-auto space-y-3 border-t border-gray-200 pt-4 dark:border-gray-800">
            <button type="button" onclick="toggleTheme()" class="btn-secondary w-full">🌙 / ☀️ Theme</button>
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                @if($u?->avatarUrl())
                    <img src="{{ $u->avatarUrl() }}" alt="" class="h-8 w-8 rounded-full object-cover">
                @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">{{ $u?->initials() }}</span>
                @endif
                <div class="min-w-0">
                    <div class="truncate font-semibold text-gray-700 dark:text-gray-200">{{ $u?->name }}</div>
                    <div dir="ltr">{{ $u?->phone }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-secondary w-full">Log out</button>
            </form>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Top bar (mobile nav + notifications bell) --}}
        <header class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <span class="font-mono font-bold md:hidden">&lt;/&gt; Aref Academy</span>
            <span class="hidden text-sm text-gray-400 md:block">@yield('title', 'Aref Academy')</span>

            <div class="flex items-center gap-2">
                @if($u && ! $u->isAdmin())
                    @php
                        $notifications = collect();
                        foreach ($u->payments()->with('course')->where('status', \App\Enums\PaymentStatus::Paid)->latest('paid_at')->limit(3)->get() as $p) {
                            $notifications->push(['icon' => '💳', 'text' => 'Payment confirmed — "' . $p->course->title . '" is unlocked', 'url' => route('courses.show', $p->course), 'time' => $p->paid_at]);
                        }
                        foreach ($u->submissions()->with('assignment')->whereNotNull('graded_at')->latest('graded_at')->limit(3)->get() as $s) {
                            $notifications->push(['icon' => '📝', 'text' => '"' . $s->assignment->title . '" graded: ' . $s->score . '/' . $s->assignment->max_score, 'url' => route('lessons.show', $s->assignment->lesson_id), 'time' => $s->graded_at]);
                        }
                        $notifications = $notifications->sortByDesc('time')->take(5)->values();
                    @endphp

                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" class="btn-secondary relative" title="Notifications">
                            🔔
                            @if($notifications->isNotEmpty())
                                <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">{{ $notifications->count() }}</span>
                            @endif
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" x-transition
                             class="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-800 dark:bg-gray-900">
                            <div class="px-2 py-1 text-xs font-semibold uppercase tracking-wide text-gray-400">Notifications</div>
                            @forelse($notifications as $n)
                                <a href="{{ $n['url'] }}" class="flex items-start gap-2 rounded-lg px-2 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                                    <span>{{ $n['icon'] }}</span>
                                    <span class="min-w-0">
                                        <span class="block truncate">{{ $n['text'] }}</span>
                                        <span class="text-xs text-gray-400">{{ $n['time']?->diffForHumans() }}</span>
                                    </span>
                                </a>
                            @empty
                                <div class="px-2 py-3 text-sm text-gray-400">No notifications yet.</div>
                            @endforelse
                        </div>
                    </div>
                @endif

                <button type="button" onclick="toggleTheme()" class="btn-secondary md:hidden">🌙</button>
                <form method="POST" action="{{ route('logout') }}" class="md:hidden">@csrf<button class="btn-secondary">Out</button></form>
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
