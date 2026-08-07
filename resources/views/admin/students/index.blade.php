@extends('layouts.app')
@section('title', __('Students – Aref Academy'))

@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ __('Students') }}</h1>

<form method="GET" action="{{ route('admin.students.index') }}" class="card mb-6 grid gap-3 md:grid-cols-5">
    <input class="input" name="search" value="{{ request('search') }}" placeholder="{{ __('Search name / phone…') }}">
    <select class="input" name="course">
        <option value="">{{ __('All courses') }}</option>
        @foreach($courses as $course)
            <option value="{{ $course->id }}" @selected((string) $course->id === request('course'))>{{ $course->title }}</option>
        @endforeach
    </select>
    <select class="input" name="governorate">
        <option value="">{{ __('All governorates') }}</option>
        @foreach($governorates as $gov)
            <option value="{{ $gov }}" @selected($gov === request('governorate'))>{{ $gov }}</option>
        @endforeach
    </select>
    <select class="input" name="grade">
        <option value="">{{ __('All grades') }}</option>
        @foreach($grades as $grade)
            <option value="{{ $grade->value }}" @selected($grade->value === request('grade'))>{{ $grade->label() }}</option>
        @endforeach
    </select>
    <button class="btn">{{ __('Filter') }}</button>
</form>

<div class="card p-0">
    <table class="w-full">
        <thead><tr class="border-b border-gray-200 dark:border-gray-800"><th class="table-th">{{ __('Name') }}</th><th class="table-th">{{ __('Phone') }}</th><th class="table-th">{{ __('Governorate') }}</th><th class="table-th">{{ __('Grade') }}</th><th class="table-th">{{ __('Courses') }}</th><th class="table-th">{{ __('Joined') }}</th></tr></thead>
        <tbody>
            @forelse($students as $student)
                <tr class="border-b border-gray-100 dark:border-gray-800/50">
                    <td class="table-td"><a class="font-medium text-indigo-600 dark:text-indigo-400" href="{{ route('admin.students.show', $student) }}">{{ $student->name }}</a></td>
                    <td class="table-td font-mono" dir="ltr">{{ $student->phone }}</td>
                    <td class="table-td">{{ $student->governorate }}</td>
                    <td class="table-td">{{ $student->grade_level?->label() }}</td>
                    <td class="table-td">{{ $student->enrollments_count }}</td>
                    <td class="table-td text-gray-400">{{ $student->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td class="table-td text-gray-400" colspan="6">{{ __('No students match your filters.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $students->links() }}</div>
@endsection
