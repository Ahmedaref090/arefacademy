@extends('layouts.app')
@section('title', 'Edit ' . $quiz->title . ' – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">Edit Quiz</h1>

<form method="POST" action="{{ route('admin.quizzes.update', $quiz) }}" class="max-w-3xl space-y-4">
    @csrf
    @method('PUT')
    @include('admin.quizzes._form')
    <button class="btn">Save Changes</button>
</form>
@endsection
