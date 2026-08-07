@extends('layouts.account')
@section('title', __('Security – Aref Academy'))

@section('account')
<h1 class="mb-6 text-2xl font-bold">{{ __('Security & Login History') }}</h1>

{{-- Change password --}}
<div class="card mb-6">
    <h2 class="mb-4 font-semibold">{{ __('Change Password') }}</h2>
    <form method="POST" action="{{ route('account.password.update') }}" class="grid gap-4 md:grid-cols-3">
        @csrf
        @method('PUT')
        <div>
            <label class="label" for="current_password">{{ __('Current Password') }}</label>
            <input class="input" id="current_password" name="current_password" type="password" required>
        </div>
        <div>
            <label class="label" for="password">{{ __('New Password') }}</label>
            <input class="input" id="password" name="password" type="password" required>
        </div>
        <div>
            <label class="label" for="password_confirmation">{{ __('Confirm New Password') }}</label>
            <input class="input" id="password_confirmation" name="password_confirmation" type="password" required>
        </div>
        <div class="md:col-span-3">
            <button class="btn">{{ __('Update Password') }}</button>
        </div>
    </form>
</div>

{{-- Login history --}}
<div class="card p-0">
    <h2 class="border-b border-gray-200 p-4 font-semibold dark:border-gray-800">{{ __('Recent Logins') }}</h2>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800">
                    <th class="table-th">{{ __('Date') }}</th>
                    <th class="table-th">{{ __('Device') }}</th>
                    <th class="table-th">{{ __('Browser') }}</th>
                    <th class="table-th">{{ __('IP Address') }}</th>
                    <th class="table-th"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logins as $login)
                    <tr class="border-b border-gray-100 dark:border-gray-800/50">
                        <td class="table-td text-gray-500 dark:text-gray-400">{{ $login->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="table-td">{{ $login->platform() }}</td>
                        <td class="table-td">{{ $login->browser() }}</td>
                        <td class="table-td font-mono text-xs" dir="ltr">{{ $login->ip_address ?? '—' }}</td>
                        <td class="table-td">
                            @if($login->isCurrentSession())
                                <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">{{ __('This device') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="5">{{ __('No login history yet — it starts recording from your next login.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
