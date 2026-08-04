@extends('layouts.app')
@section('title', $lesson->title . ' – Aref Academy')

@section('content')
<div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
    <a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('courses.show', $lesson->course) }}">{{ $lesson->course->title }}</a>
    <span class="mx-1">/</span> {{ $lesson->title }}
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">

        {{-- Video player --}}
        <div class="card p-0">
            @if($lesson->embedUrl())
                <div class="aspect-video">
                    <iframe src="{{ $lesson->embedUrl() }}" class="h-full w-full rounded-t-xl" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                </div>
            @elseif($lesson->video_path)
                <video controls class="aspect-video w-full rounded-t-xl bg-black" src="{{ Storage::disk('public')->url($lesson->video_path) }}"></video>
            @else
                <div class="flex aspect-video items-center justify-center rounded-t-xl bg-gray-900 font-mono text-gray-500">// no video yet</div>
            @endif
            <div class="flex items-center justify-between p-4">
                <h1 class="font-bold">{{ $lesson->title }}</h1>
                @if($completed)
                    <span class="badge bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400">✓ Completed</span>
                @else
                    <form method="POST" action="{{ route('lessons.complete', $lesson) }}">
                        @csrf
                        <button class="btn">Mark Complete</button>
                    </form>
                @endif
            </div>
        </div>

        @if($lesson->description)
            <div class="card text-sm leading-relaxed text-gray-600 dark:text-gray-300">{!! nl2br(e($lesson->description)) !!}</div>
        @endif

        {{-- Resources --}}
        @if($lesson->attachments->isNotEmpty())
            <div class="card">
                <h2 class="mb-3 font-semibold">📎 Resources</h2>
                <ul class="space-y-2 text-sm">
                    @foreach($lesson->attachments as $attachment)
                        <li class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-800">
<span>{{ $attachment->title }} <span class="text-xs text-gray-400">({{ strtoupper($attachment->file_type) }} · {{ $attachment->humanSize }})</span></span>