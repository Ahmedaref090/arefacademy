@extends('layouts.app')
@section('title', 'Courses – Aref Academy')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">Courses</h1>
    <a class="btn" href="{{ route('admin.courses.create') }}">+ New Course</a>
</div>

<div class="card p-0">
    <table class="w-full">
        <thead><tr class="border-b border-gray-200 dark:border-gray-800"><th class="table-th">Title</th><th class="table-th">Price</th><th class="table-th">Lessons</th><th class="table-th">Enrollments</th><th class="table-th">Status</th><th class="table-th"></th></tr></thead>
        <tbody>
            @forelse($courses as $course)
                <tr class="border-b border-gray-100 dark:border-gray-800/50">
                    <td class="table-td font-medium">{{ $course->title }}</td>
                    <td class="table-td font-mono">{{ number_format($course->price, 2) }}</td>
                    <td class="table-td">{{ $course->lessons_count }}</td>
                    <td class="table-td">{{ $course->enrollments_count }}</td>
                    <td class="table-td">
                        <span class="badge {{ $course->is_published ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                            {{ $course->is_published ? 'Published' : 'Draft' }}
                        </span>
                    </td>
                    <td class="table-td">
                        <div class="flex justify-end gap-2">
                            <a class="btn-secondary" href="{{ route('admin.courses.edit', $course) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" onsubmit="return confirm('Delete this course and all its content?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td class="table-td text-gray-400" colspan="6">No courses yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $courses->links() }}</div>
@endsection
