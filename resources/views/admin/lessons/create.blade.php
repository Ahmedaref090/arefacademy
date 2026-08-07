@extends('layouts.app')
@section('title', __('New Lesson – Aref Academy'))

@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ __('New Lesson') }} — {{ $course->title }}</h1>

<form method="POST" action="{{ route('admin.courses.lessons.store', $course) }}" enctype="multipart/form-data"
      x-data="r2Upload"
      data-url="{{ route('admin.videos.presigned-upload') }}"
      data-token="{{ csrf_token() }}"
      @submit="if (uploading) $event.preventDefault()"
      class="card max-w-2xl space-y-4">
    @csrf
    @include('admin.lessons._form')
    <button type="submit" class="btn w-full"
            :disabled="uploading"
            :class="{ 'opacity-50 cursor-not-allowed': uploading }">
        <template x-if="uploading">
            <span>{{ __('uploading_video') }}</span>
        </template>
        <template x-if="!uploading">
            <span>{{ __('Create Lesson') }}</span>
        </template>
    </button>
</form>
@endsection
