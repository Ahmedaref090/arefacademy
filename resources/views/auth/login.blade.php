@extends('layouts.guest')
@section('title', 'Log in – Aref Academy')

@section('content')
<div class="card">
    <h1 class="mb-6 text-xl font-bold">Log in with your phone</h1>

    @if($errors->any())
        <div class="mb-4 flex items-start gap-2 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/50 dark:text-red-400" role="alert">
            <span class="mt-0.5 shrink-0">⚠️</span>
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="label" for="phone">Phone Number</label>
            <input class="input" id="phone" name="phone" type="tel" dir="ltr" value="{{ old('phone') }}" required autofocus placeholder="01xxxxxxxxx">
        </div>
        <div>
            <label class="label" for="password">Password</label>
            <input class="input" id="password" name="password" type="password" required>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="rounded"> Remember me
        </label>
        <button class="btn w-full">Log in</button>
    </form>
    <p class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
        No account? <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('register') }}">Register</a>
    </p>
</div>
@endsection
