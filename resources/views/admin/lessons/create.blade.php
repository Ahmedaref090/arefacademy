@extends('layouts.app')
@section('title', 'New Lesson – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">New Lesson — {{ $course->title }}</h1>

<form method="POST" action="{{ route('admin.courses.lessons.store', $course) }}" enctype="multipart/form-data" class="card max-w-2xl space-y-4">
    @csrf
    @include('admin.lessons._form')
    <button class="btn">Create Lesson</button>
</form>
@endsection
