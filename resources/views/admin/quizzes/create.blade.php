@extends('layouts.app')
@section('title', 'New Quiz – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">New Quiz — {{ $lesson->title }}</h1>

<form method="POST" action="{{ route('admin.lessons.quizzes.store', $lesson) }}" class="max-w-3xl space-y-4">
    @csrf
    @include('admin.quizzes._form')
    <button class="btn">Create Quiz</button>
</form>
@endsection
