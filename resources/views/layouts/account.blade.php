@extends('layouts.app')

@section('content')
{{--
    Account section shell: vertical nav + content.
    RTL-aware: flips automatically when APP_LOCALE=ar, and all spacing
    uses logical properties (start/ms/me) so both directions work.
--}}
<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="grid items-start gap-6 lg:grid-cols-[300px_minmax(0,1fr)]">
    @include('student.account._nav')

    <div class="min-w-0">
        @yield('account')
    </div>
</div>
@endsection
