@extends('layouts.app')
@section('title', __(':name – Aref Academy', ['name' => $user->name]))

@section('content')
<div class="mb-6 flex items-center gap-4">
    @if($user->avatarUrl())
        <img src="{{ $user->avatarUrl() }}" alt="" class="h-16 w-16 rounded-full object-cover">
    @else
        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-600 text-xl font-bold text-white">{{ $user->initials() }}</span>
    @endif
    <div>
        <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            <span class="font-mono" dir="ltr">{{ $user->phone }}</span>
            @if($user->parent_phone) · 👪 <span class="font-mono" dir="ltr">{{ $user->parent_phone }}</span>@endif
            · {{ $user->governorate }} · {{ $user->grade_level?->label() }} · {{ __('joined') }} {{ $user->created_at->format('Y-m-d') }}
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="card">
        <h2 class="mb-3 font-semibold">{{ __('Enrollments & Progress') }}</h2>
        <ul class="space-y-3 text-sm">
            @forelse($user->enrollments as $enrollment)
                @php($progress = $enrollment->course->progressFor($user, $enrollment->month))
                <li>
                    @php($revokeConfirm = __('Revoke this subscription? :course – :month — the student will lose access.', [
                        'course' => $enrollment->course->title,
                        'month' => $enrollment->month?->name ?? __('Full course'),
                    ]))
                    <div class="mb-1 flex items-center justify-between gap-2">
                        <span class="font-medium">
                            {{ $enrollment->course->title }}
                            @if($enrollment->month)
                                <span class="badge bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">{{ $enrollment->month->name }}</span>
                            @endif
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="badge {{ $enrollment->status === \App\Enums\EnrollmentStatus::Active ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">{{ $enrollment->status->label() }}</span>
                            <form method="POST" action="{{ route('admin.enrollments.destroy', $enrollment) }}" onsubmit="return confirm(@json($revokeConfirm));">
                                @csrf @method('DELETE')
                                <button class="btn-danger">{{ __('Revoke') }}</button>
                            </form>
                        </div>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                        <div class="h-full rounded-full bg-indigo-600" style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="mt-1 text-xs text-gray-400">{{ __(':progress% complete', ['progress' => $progress]) }}</div>
                </li>
            @empty
                <li class="text-gray-400">{{ __('No enrollments.') }}</li>
            @endforelse

            @forelse($approvedMonths as $month)
                @php($progress = $month->course->progressFor($user, $month))
                <li>
                    @php($revokeMonthConfirm = __('Revoke this month subscription? :course – :month — the student will lose access.', [
                        'course' => $month->course->title,
                        'month' => $month->name,
                    ]))
                    <div class="mb-1 flex items-center justify-between gap-2">
                        <span class="font-medium">
                            {{ $month->course->title }}
                            <span class="badge bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">{{ $month->name }}</span>
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="badge bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400">{{ __('Active') }}</span>
                            <form method="POST" action="{{ route('admin.enrollments.month.revoke', ['user' => $user, 'courseMonth' => $month]) }}" onsubmit="return confirm(@json($revokeMonthConfirm));">
                                @csrf
                                <button class="btn-danger">{{ __('Revoke') }}</button>
                            </form>
                        </div>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                        <div class="h-full rounded-full bg-indigo-600" style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="mt-1 text-xs text-gray-400">{{ __(':progress% complete', ['progress' => $progress]) }}</div>
                </li>
            @empty
                <li class="text-gray-400 sm:hidden">{{ __('No monthly subscriptions.') }}</li>
            @endforelse
        </ul>

        <form method="POST" action="{{ route('admin.enrollments.store') }}" class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800" x-data="enrollDropdown()">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <div class="flex flex-wrap items-end gap-2">
                <div class="flex-1">
                    <label class="label">{{ __('Enroll in course (cash / free)') }}</label>
                    <select class="input" name="course_id" x-model="courseId" @change="loadMonths()" required>
                        <option value="">{{ __('Select course…') }}</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <template x-if="months.length">
                    <div class="flex-1">
                        <label class="label">{{ __('Select Month') }}</label>
                        <select class="input" name="course_month_id" x-model="monthId" required>
                            <option value="">{{ __('Select month…') }}</option>
                            <template x-for="m in months" :key="m.id">
                                <option :value="m.id" x-text="m.name"></option>
                            </template>
                        </select>
                    </div>
                </template>
                <button class="btn" :disabled="!canSubmit" x-text="loading ? '…' : @js(__('Enroll'))"></button>
            </div>
            <p x-show="isMonthly && !monthId && courseId" class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                {{ __('Please select a month for this monthly course first.') }}
            </p>
            @error('course_month_id')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </form>
    </div>

    <div class="space-y-6">
        <div class="card">
            <h2 class="mb-3 font-semibold">{{ __('Reset Password') }}</h2>
            <form method="POST" action="{{ route('admin.students.password', $user) }}" class="flex items-end gap-2">
                @csrf
                <div class="flex-1">
                    <label class="label">{{ __('New password') }}</label>
                    <input class="input" name="password" type="text" minlength="8" required placeholder="{{ __('min 8 characters') }}">
                </div>
                <button class="btn">{{ __('Reset') }}</button>
            </form>
        </div>

        <div class="card p-0" x-data="deviceModal()">
            <h2 class="flex items-center gap-2 border-b border-gray-200 p-4 font-semibold dark:border-gray-800">
                {{ __('Registered Devices') }}
                <span class="badge bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $user->devices->count() }}</span>
            </h2>

            <div class="space-y-2 p-4">
                @forelse($user->devices as $device)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-3 py-2.5 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $device->platform() }}
                                    <span class="font-normal text-gray-400">· {{ $device->deviceType() }}</span>
                                </p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ $device->browser() }}
                                    @if($device->last_seen_ip) · <span dir="ltr">{{ $device->last_seen_ip }}</span>@endif
                                    · {{ $device->last_active_at ? __('since :date', ['date' => $device->last_active_at->format('Y-m-d H:i')]) : __('Never') }}
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="open(@js(route('admin.students.devices.destroy', ['user' => $user, 'device' => $device])))" class="btn-danger shrink-0">
                            {{ __('Delete') }}
                        </button>
                    </div>
                @empty
                    <div class="flex flex-col items-center gap-2 py-6 text-center text-sm text-gray-400">
                        <span class="rounded-full bg-gray-100 p-3 dark:bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" /></svg>
                        </span>
                        {{ __('No devices are currently linked to this student.') }}
                    </div>
                @endforelse
            </div>

            {{-- Confirm removal modal --}}
            <template x-teleport="body">
                <div x-show="actionUrl !== null" x-cloak
                    class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/60 p-4 backdrop-blur-sm sm:items-center"
                    @click.self="actionUrl = null">
                    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/10 dark:bg-gray-900 dark:ring-white/10">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Remove this device?') }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                            {{ __('Removing the device frees up one slot so the student can log in from a new device. This action cannot be undone.') }}
                        </p>
                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button" @click="actionUrl = null" class="btn-secondary">{{ __('Cancel') }}</button>
                            <button type="button" @click="submit()" class="btn-danger">{{ __('Delete') }}</button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Target form: the modal points this at the device to remove. --}}
            <form id="device-delete-form" method="POST" action="#" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>

        <div class="card p-0">
            <h2 class="border-b border-gray-200 p-4 font-semibold dark:border-gray-800">{{ __('Quiz Attempts') }}</h2>
            <table class="w-full">
                <thead><tr class="border-b border-gray-200 dark:border-gray-800"><th class="table-th">{{ __('Quiz') }}</th><th class="table-th">{{ __('Score') }}</th><th class="table-th">{{ __('Result') }}</th><th class="table-th">{{ __('Date') }}</th></tr></thead>
                <tbody>
                    @forelse($user->quizAttempts as $attempt)
                        <tr class="border-b border-gray-100 dark:border-gray-800/50">
                            <td class="table-td">{{ $attempt->quiz->title }}</td>
                            <td class="table-td font-mono">{{ $attempt->score }}/{{ $attempt->total_questions }}</td>
                            <td class="table-td"><span class="badge {{ $attempt->passed ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">{{ $attempt->passed ? __('Passed') : __('Failed') }}</span></td>
                            <td class="table-td text-gray-400">{{ $attempt->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td class="table-td text-gray-400" colspan="4">{{ __('No attempts.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-0 lg:col-span-2">
        <h2 class="border-b border-gray-200 p-4 font-semibold dark:border-gray-800">{{ __('Assignment Submissions') }}</h2>
        <table class="w-full">
            <thead><tr class="border-b border-gray-200 dark:border-gray-800"><th class="table-th">{{ __('Assignment') }}</th><th class="table-th">{{ __('Course') }}</th><th class="table-th">{{ __('Score') }}</th><th class="table-th">{{ __('Submitted') }}</th></tr></thead>
            <tbody>
                @forelse($user->submissions as $submission)
                    <tr class="border-b border-gray-100 dark:border-gray-800/50">
                        <td class="table-td">{{ $submission->assignment->title }}</td>
                        <td class="table-td">{{ $submission->assignment->lesson->course->title }}</td>
                        <td class="table-td font-mono">{{ $submission->isGraded() ? $submission->score . '/' . $submission->assignment->max_score : '—' }}</td>
                        <td class="table-td text-gray-400">{{ $submission->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="4">{{ __('No submissions.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('enrollDropdown', () => ({
            courseId: '',
            monthId: '',
            months: [],
            loading: false,
            monthBaseUrl: @js(route('admin.courses.months', ['course' => '__COURSE_ID__'])),
            get isMonthly() {
                return this.courseId !== '' && this.months.length > 0;
            },
            get canSubmit() {
                // Monthly courses require a month; lifetime courses just need a course.
                return !this.loading && this.courseId !== '' && (this.months.length === 0 || this.monthId !== '');
            },
            async loadMonths() {
                this.monthId = '';
                this.months = [];
                if (!this.courseId) {
                    return;
                }
                this.loading = true;
                try {
                    const response = await fetch(this.monthBaseUrl.replace('__COURSE_ID__', this.courseId), {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (response.ok) {
                        this.months = await response.json();
                    }
                } finally {
                    this.loading = false;
                }
            },
        }));
    });
</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('deviceModal', () => ({
            actionUrl: null,
            open(url) {
                this.actionUrl = url;
            },
            submit() {
                if (!this.actionUrl) {
                    return;
                }
                document.getElementById('device-delete-form').action = this.actionUrl;
                document.getElementById('device-delete-form').submit();
            },
        }));
    });
</script>
@endpush
