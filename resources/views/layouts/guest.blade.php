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
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 p-4 text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center font-mono text-2xl font-bold">
            <span class="rounded bg-indigo-600 px-2 py-1 text-white">&lt;/&gt;</span> Aref Academy
        </div>
        @if($errors->any())
            <div class="card mb-4 border-red-500 text-sm text-red-600 dark:text-red-400">
                <ul class="list-inside list-disc">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </div>
</body>
</html>
