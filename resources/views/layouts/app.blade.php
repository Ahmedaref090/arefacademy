<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

    @php
        // Capture sections once so tags stay clean (no nested quotes, no raw HTML).
        $pageTitle   = strip_tags(trim($__env->yieldContent('title', __('Aref Academy'))));
        $description = strip_tags(trim($__env->yieldContent('meta_description', __('Aref Academy – Learn programming the right way.'))));
        $keywords    = trim((string) $__env->yieldContent('meta_keywords'));
        $robots      = trim($__env->yieldContent('meta_robots')) ?: 'index, follow';
        $canonical   = trim($__env->yieldContent('canonical')) ?: url()->current();
        $urlAr       = trim($__env->yieldContent('url_ar')) ?: url()->current();
        $urlEn       = trim($__env->yieldContent('url_en')) ?: url()->current();
        $ogType      = trim($__env->yieldContent('og_type')) ?: 'website';
        $ogImage     = trim($__env->yieldContent('og_image')) ?: asset('images/og-default.jpg');
        $twitterSite = trim($__env->yieldContent('twitter_site'));
        $pageName    = __('Aref Academy');
    @endphp

    <title>{{ $pageTitle }}</title>

    {{-- Primary meta --}}
    <meta name="description" content="{{ $description }}">
    @if($keywords)
        <meta name="keywords" content="{{ $keywords }}">
    @endif
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="alternate" hreflang="ar" href="{{ $urlAr }}">
    <link rel="alternate" hreflang="en" href="{{ $urlEn }}">
    <link rel="alternate" hreflang="x-default" href="{{ $urlEn }}">

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "WebSite",
        "name": {{ json_encode($pageName, JSON_UNESCAPED_UNICODE) }},
        "url": {{ json_encode(url('/'), JSON_UNESCAPED_UNICODE) }}
    }
    </script>

    {{-- Open Graph (Facebook, WhatsApp, LinkedIn) --}}
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ $pageName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_EG' : 'en_US' }}">
    <meta property="og:locale:alternate" content="{{ app()->getLocale() === 'ar' ? 'en_US' : 'ar_EG' }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    @if($twitterSite)
        <meta name="twitter:site" content="{{ $twitterSite }}">
    @endif

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="{{ auth()->user()?->isAdmin() ? 'aurora-bg' : 'aurora-bg-light' }} min-h-screen font-sans text-slate-900 antialiased dark:text-slate-100">
@php($u = auth()->user())
@php($notifications = $notifications ?? collect())
<div class="flex min-h-screen" x-data="{ sidebarOpen: false }">

    {{-- Backdrop — visible on mobile/tablet when the off-canvas sidebar is open --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity x-cloak
         class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

    {{-- Sidebar: static drawer on lg+, off-canvas (slide-in) below lg --}}
    <aside
        x-show="sidebarOpen"
        x-transition:enter="transition ease-in-out duration-300"
        x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="ltr:-translate-x-full rtl:translate-x-full"
        class="fixed inset-y-0 start-0 z-50 flex w-64 shrink-0 flex-col overflow-y-auto border-e border-slate-200/70 bg-white/70 p-5 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/70 lg:static lg:z-auto lg:flex! lg:translate-x-0 lg:rtl:translate-x-0">
        <a href="{{ $u?->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="mb-8 flex items-center gap-3 font-mono text-lg font-extrabold">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-brand-600 to-fuchsia-600 text-sm text-white shadow-lg shadow-brand-500/40">&lt;/&gt;</span>
            <span class="text-gradient">{{ __('Aref Academy') }}</span>
        </a>

        <nav @click="sidebarOpen = false" class="flex flex-1 flex-col gap-1.5 text-sm">
            @if($u?->isAdmin())
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}" href="{{ route('admin.dashboard') }}"><x-icon name="dashboard" class="h-[18px] w-[18px]"/> {{ __('Dashboard') }}</a>
                <a class="nav-link {{ request()->routeIs('admin.courses.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.courses.index') }}"><x-icon name="book" class="h-[18px] w-[18px]"/> {{ __('Courses') }}</a>
                <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.students.index') }}"><x-icon name="users" class="h-[18px] w-[18px]"/> {{ __('Students') }}</a>
                <a class="nav-link {{ request()->routeIs('admin.payments.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.payments.index') }}"><x-icon name="credit" class="h-[18px] w-[18px]"/> {{ __('Payments') }}</a>
                <a class="nav-link {{ request()->routeIs('admin.submissions.*') ? 'nav-link-active' : '' }}" href="{{ route('admin.submissions.index') }}"><x-icon name="file" class="h-[18px] w-[18px]"/> {{ __('Submissions') }}</a>
            @else
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" href="{{ route('dashboard') }}"><x-icon name="home" class="h-[18px] w-[18px]"/> {{ __('Home') }}</a>
                <a class="nav-link {{ request()->routeIs('courses.my') ? 'nav-link-active' : '' }}" href="{{ route('courses.my') }}"><x-icon name="book" class="h-[18px] w-[18px]"/> {{ __('My Courses') }}</a>
                <a class="nav-link {{ request()->routeIs('courses.index') ? 'nav-link-active' : '' }}" href="{{ route('courses.index') }}"><x-icon name="bag" class="h-[18px] w-[18px]"/> {{ __('All Courses') }}</a>
                <a class="nav-link" href="#" title="{{ __('Coming soon') }}"><x-icon name="chat" class="h-[18px] w-[18px]"/> {{ __('Community') }}</a>
                <a class="nav-link {{ request()->routeIs('invoices.*') ? 'nav-link-active' : '' }}" href="{{ route('invoices.index') }}"><x-icon name="credit" class="h-[18px] w-[18px]"/> {{ __('Invoices & Subscriptions') }}</a>
                <a class="nav-link {{ request()->routeIs(['profile.*', 'account.*']) ? 'nav-link-active' : '' }}" href="{{ route('profile.edit') }}"><x-icon name="user" class="h-[18px] w-[18px]"/> {{ __('Account') }}</a>
                <a class="nav-link {{ request()->routeIs('contact') ? 'nav-link-active' : '' }}" href="{{ route('contact') }}"><x-icon name="headset" class="h-[18px] w-[18px]"/> {{ __('Contact Us') }}</a>
            @endif
        </nav>

        <div class="mt-auto space-y-3 border-t border-slate-200/70 pt-4 dark:border-slate-800">
            <button type="button" onclick="toggleTheme()" class="btn-secondary w-full"><x-icon name="moon" class="h-4 w-4" :stroke="2"/> <span class="dark:hidden">{{ __('Dark Mode') }}</span><span class="hidden dark:inline">{{ __('Light Mode') }}</span></button>
            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                @if($u?->avatarUrl())
                    <img src="{{ $u->avatarUrl() }}" alt="" class="h-9 w-9 rounded-full object-cover ring-2 ring-brand-500/30">
                @else
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-br from-brand-600 to-fuchsia-600 text-xs font-bold text-white">{{ $u?->initials() }}</span>
                @endif
                <div class="min-w-0">
                    <div class="truncate font-bold text-slate-800 dark:text-slate-100">{{ $u?->name }}</div>
                    <div dir="ltr">{{ $u?->phone }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-secondary w-full"><x-icon name="logout" class="h-4 w-4" :stroke="2"/> {{ __('Log out') }}</button>
            </form>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Top bar (language switcher + notifications bell) --}}
        <header class="sticky top-0 z-40 flex items-center justify-between border-b border-slate-200/70 bg-white/70 px-4 py-3 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/70">
            <span class="flex items-center gap-2 lg:hidden">
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="btn-secondary !px-2.5 py-2" aria-label="{{ __('Menu') }}" aria-expanded="false" :aria-expanded="sidebarOpen.toString()" title="{{ __('Menu') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-gradient-to-br from-brand-600 to-fuchsia-600 text-[11px] text-white">&lt;/&gt;</span>
            </span>
            <span class="hidden text-sm font-semibold text-slate-500 dark:text-slate-400 md:block">@yield('title', __('Aref Academy'))</span>

            <div class="flex items-center gap-2.5">
                {{-- Language switcher — visible on every page --}}
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('locale.switch', 'en') }}" class="btn-secondary px-3.5 py-2 text-xs font-bold" title="English"><x-icon name="globe" class="h-4 w-4"/> EN</a>
                @else
                    <a href="{{ route('locale.switch', 'ar') }}" class="btn-secondary px-3.5 py-2 text-xs font-bold" title="العربية"><x-icon name="globe" class="h-4 w-4"/> عربي</a>
                @endif

                @if($u && $u->isStudent())
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" class="btn-secondary relative" title="{{ __('Notifications') }}">
                            <x-icon name="bell" class="h-5 w-5" :stroke="2"/>
                            @if($notifications->isNotEmpty())
                                <span class="absolute -end-1 -top-1 grid h-5 w-5 place-items-center rounded-full bg-gradient-to-br from-rose-500 to-brand-600 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900">{{ $notifications->count() }}</span>
                            @endif
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" x-transition.origin.top.right
                             class="absolute end-0 z-50 mt-2 w-80 rounded-2xl border border-slate-200 bg-white/90 p-2 shadow-2xl backdrop-blur-xl dark:border-slate-700 dark:bg-slate-900/90">
                            <div class="px-2 py-1.5 text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('Notifications') }}</div>
                            @forelse($notifications as $n)
                                <a href="{{ $n['url'] }}" class="flex items-start gap-2.5 rounded-xl px-2 py-2 text-sm transition hover:bg-slate-100 dark:hover:bg-white/5">
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-300"><x-icon name="{{ $n['icon'] ?? 'bell' }}" class="h-4 w-4"/></span>
                                    <span class="min-w-0">
                                        <span class="block truncate">{{ $n['text'] }}</span>
                                        <span class="text-xs text-slate-400">{{ $n['time']?->diffForHumans() }}</span>
                                    </span>
                                </a>
                            @empty
                                <div class="px-2 py-3 text-sm text-slate-400">{{ __('No notifications yet.') }}</div>
                            @endforelse
                        </div>
                    </div>
                @endif

                <button type="button" onclick="toggleTheme()" class="btn-secondary !px-3 py-2 lg:hidden"><x-icon name="moon" class="h-5 w-5" :stroke="2"/></button>
                <form method="POST" action="{{ route('logout') }}" class="lg:hidden">@csrf<button class="btn-secondary"><x-icon name="logout" class="h-5 w-5" :stroke="2"/></button></form>
            </div>
        </header>

        <main class="page-enter flex-1 p-4 md:p-8">
            @yield('content')
        </main>

        @include('components.toasts')
    </div>
</div>
<style>
    .locked-popup { border-radius: 1.75rem !important; padding: 2rem 1.5rem !important; animation: pop-in .4s cubic-bezier(.34,1.56,.64,1) both !important; }
    .locked-title { font-weight: 800 !important; color: #1e293b !important; }
    .locked-icon { animation: pop-in .55s cubic-bezier(.34,1.56,.64,1) both !important; font-size: 3.75rem !important; line-height: 1 !important; }
    .locked-confirm { background: linear-gradient(135deg, #6d38f6 0%, #d946ef 100%) !important; border: 0 !important; border-radius: 9999px !important; font-weight: 700 !important; font-size: .95rem !important; padding: .7rem 1.8rem !important; box-shadow: 0 10px 25px -5px rgba(109,56,246,.55) !important; transition: transform .2s ease, box-shadow .2s ease !important; }
    .locked-confirm:hover { transform: scale(1.06) !important; box-shadow: 0 16px 30px -6px rgba(217,70,239,.65) !important; }
    .locked-cancel { background: rgba(226,232,240,.9) !important; color: #475569 !important; border: 0 !important; border-radius: 9999px !important; font-weight: 600 !important; }
</style>
<script>
    // Centralized, locale-aware "locked lesson" modal (SweetAlert2).
    window.LockedModal = {
        strings: {
            title: @json(__('lesson_locked_title')),
            message: @json(__('lesson_locked')),
            primary: @json(__('lesson_locked_button')),
            close: @json(__('close')),
            courseUrl: @json(session('locked_course_url', '')),
            hasFlash: @json((bool) session('locked_error')),
        },

        open(courseUrl = null, message = null) {
            const s = this.strings;
            const target = courseUrl || s.courseUrl;

            Swal.fire({
                title: s.title,
                icon: 'warning',
                color: '#1e293b',
                background: 'rgba(255,255,255,0.92)',
                backdrop: 'rgba(15,23,42,0.55)',
                iconHtml: '<span class="locked-icon">🔒</span>',
                html: `<div dir="auto" class="leading-relaxed text-slate-600">${message || s.message}</div>`,
                showCancelButton: true,
                confirmButtonText: s.primary,
                cancelButtonText: s.close,
                focusConfirm: true,
                allowOutsideClick: true,
                customClass: {
                    popup: 'locked-popup',
                    title: 'locked-title',
                    icon: 'locked-icon',
                    confirmButton: 'locked-confirm',
                    cancelButton: 'locked-cancel',
                },
            }).then((result) => {
                if (result.isConfirmed && target) {
                    window.location.href = target;
                }
            });
        },
    };

    // Auto-fire the modal when we landed here via a flashed redirect
    // (i.e. the user clicked a locked lesson the "normal" way).
    if (window.LockedModal && window.LockedModal.strings.hasFlash) {
        document.addEventListener('DOMContentLoaded', () => window.LockedModal.open());
    }

    // Gracefully catch 403 JSON responses from fetch()/AJAX — prevents the
    // default (ugly) 403 page when a locked-lesson click is made via AJAX.
    (function () {
        const originalFetch = window.fetch;
        window.fetch = async function (...args) {
            const response = await originalFetch.apply(this, args);
            if (response.status === 403) {
                try {
                    const data = await response.clone().json();
                    if (data && data.locked) {
                        window.LockedModal.open(data.course_url ?? null, data.message ?? null);
                    }
                } catch (e) { /* not a JSON payload — ignore */ }
            }
            return response;
        };
    })();
</script>
@stack('scripts')
</body>
</html>