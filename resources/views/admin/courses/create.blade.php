@extends('layouts.app')
@section('title', 'New Course – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">New Course</h1>

<form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data" class="card max-w-2xl space-y-4">
    @csrf
    @include('admin.courses._form')
    <button class="btn">Create Course</button>
</form>
@endsection
