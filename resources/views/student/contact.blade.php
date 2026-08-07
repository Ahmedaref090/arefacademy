@extends('layouts.app')
@section('title', __('Contact Us – Aref Academy'))

@section('content')
<div class="mb-6">
    <span class="eyebrow"><x-icon name="headset" class="h-4 w-4"/> {{ __('Contact Us') }}</span>
    <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('We would love to hear from you') }}</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Reach out anytime — our team usually replies within a few hours.') }}</p>
</div>

<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
    {{-- Facebook --}}
    <a href="https://www.facebook.com/share/1FebFjU6Tq/"
       target="_blank"
       rel="noopener noreferrer"
       class="card card-hover group flex flex-col items-center gap-4 p-6 text-center">
        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-600/30 transition-all duration-300 group-hover:-translate-y-1 group-hover:scale-110 group-hover:shadow-blue-500/50">
            <x-icon name="facebook" class="h-8 w-8" :stroke="2"/>
        </span>
        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Facebook') }}</span>
        <span class="mt-auto text-xs text-slate-400">{{ __('facebook.com/ArefAcademy') }}</span>
    </a>

    {{-- Telegram --}}
    <a href="https://t.me/+201068014651"
       target="_blank"
       rel="noopener noreferrer"
       class="card card-hover group flex flex-col items-center gap-4 p-6 text-center">
        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-sky-400 to-blue-500 text-white shadow-lg shadow-sky-500/30 transition-all duration-300 group-hover:-translate-y-1 group-hover:scale-110 group-hover:shadow-sky-400/50">
            <x-icon name="telegram" class="h-8 w-8" :stroke="2"/>
        </span>
        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Telegram') }}</span>
        <span class="mt-auto text-xs text-slate-400" dir="ltr">01068014651</span>
    </a>

    {{-- WhatsApp --}}
    <a href="https://wa.me/201068014651"
       target="_blank"
       rel="noopener noreferrer"
       class="card card-hover group flex flex-col items-center gap-4 p-6 text-center">
        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-green-500 to-emerald-500 text-white shadow-lg shadow-green-500/30 transition-all duration-300 group-hover:-translate-y-1 group-hover:scale-110 group-hover:shadow-green-400/50">
            <x-icon name="whatsapp" class="h-8 w-8" :stroke="2"/>
        </span>
        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('WhatsApp') }}</span>
        <span class="mt-auto text-xs text-slate-400" dir="ltr">+20 106 801 4651</span>
    </a>

    {{-- Email --}}
    <a href="mailto:ahmedaref009988@gmail.com"
       class="card card-hover group flex flex-col items-center gap-4 p-6 text-center">
        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-fuchsia-500 to-brand-600 text-white shadow-lg shadow-fuchsia-500/30 transition-all duration-300 group-hover:-translate-y-1 group-hover:scale-110 group-hover:shadow-fuchsia-500/50">
            <x-icon name="mail" class="h-8 w-8" :stroke="2"/>
        </span>
        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Email') }}</span>
        <span class="mt-auto text-xs text-slate-400" dir="ltr">ahmedaref009988@gmail.com</span>
    </a>
</div>

<div class="card mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
    <div class="flex items-center gap-3">
        <span class="icon-tile-soft"><x-icon name="headset" class="h-6 w-6"/></span>
        <div>
            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ __('Prefer one-on-one support?') }}</p>
            <p class="text-xs text-slate-400">{{ __('Message us on WhatsApp or Telegram and a member of our team will assist you.') }}</p>
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="https://wa.me/201068014651" target="_blank" rel="noopener noreferrer" class="btn bg-green-500 text-white hover:bg-green-600"><x-icon name="whatsapp" class="h-4 w-4"/> {{ __('Chat on WhatsApp') }}</a>
        <a href="https://t.me/+201068014651" target="_blank" rel="noopener noreferrer" class="btn bg-sky-500 text-white hover:bg-sky-600"><x-icon name="telegram" class="h-4 w-4"/> {{ __('Chat on Telegram') }}</a>
    </div>
</div>
@endsection
