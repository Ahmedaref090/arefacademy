@extends('layouts.guest')
@section('title', __('Register') . ' – ' . __('Aref Academy'))

@section('content')
<div class="text-center">
    <div class="icon-tile mx-auto !from-gold-400 !to-brand-500 !shadow-gold-500/30"><x-icon name="user-plus" class="h-6 w-6" :stroke="1.8"/></div>
    <h1 class="mt-4 text-2xl font-extrabold">{{ __('Create your account') }}</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Start learning in less than a minute.') }}</p>
</div>

<form method="POST" action="{{ route('register') }}" class="mt-7 space-y-4"
      x-data="{ showPassword: false, showConfirmPassword: false, nameError: '' }"
      @submit="
          const words = ($refs.nameField.value || '').trim().split(/\s+/).filter(Boolean);
          if (words.length < 3 || words.some(w => !/^[\p{L}]{2,}$/u.test(w))) {
              $event.preventDefault();
              nameError = '{{ __('Please enter at least three names - Arabic or English characters') }}';
          } else {
              nameError = '';
          }
      ">
    @csrf
    <div>
        <label class="label" for="name">{{ __('Full Name') }}</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-slate-400"><x-icon name="user" class="h-5 w-5"/></span>
            <input class="input !ps-11 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}"
                   required autofocus x-ref="nameField"
                   @input="
                       const words = $refs.nameField.value.trim().split(/\s+/).filter(Boolean);
                       nameError = (words.length >= 3 && words.every(w => /^[\p{L}]{2,}$/u.test(w)))
                           ? ''
                           : '{{ __('Please enter at least three names - Arabic or English characters') }}';
                   ">
        </div>
        <p x-show="nameError" x-cloak x-text="nameError"
           class="mt-1.5 flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400"></p>
        @error('name')
            <p class="mt-1.5 flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400">
                <x-icon name="alert" class="h-4 w-4 shrink-0" :stroke="2"/> <span dir="auto">{{ $message }}</span>
            </p>
        @enderror
    </div>
    <div>
        <label class="label" for="phone">{{ __('Phone Number') }}</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-slate-400"><x-icon name="phone" class="h-5 w-5"/></span>
            <input class="input !ps-11 @error('phone') !border-rose-400 @enderror" id="phone" name="phone" type="tel" dir="ltr" value="{{ old('phone') }}" required placeholder="01xxxxxxxxx" data-phone maxlength="11" inputmode="numeric">
        </div>
        @error('phone')
            <p class="mt-1.5 text-xs font-medium text-rose-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="label" for="parent_phone">{{ __("Parent's Phone (optional)") }}</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-slate-400"><x-icon name="users" class="h-5 w-5"/></span>
            <input class="input !ps-11" id="parent_phone" name="parent_phone" type="tel" dir="ltr" value="{{ old('parent_phone') }}" placeholder="01xxxxxxxxx" data-phone maxlength="11" inputmode="numeric">
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="label" for="governorate">{{ __('Governorate') }}</label>
            <select class="input" id="governorate" name="governorate" required>
                <option value="">{{ __('Select governorate…') }}</option>
                @foreach($governorates as $gov)
                    <option value="{{ $gov }}" @selected(old('governorate') === $gov)>{{ __($gov) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="grade_level">{{ __('Grade Level') }}</label>
            <select class="input" id="grade_level" name="grade_level" required>
                <option value="">{{ __('Select grade…') }}</option>
                @foreach($grades as $grade)
                    <option value="{{ $grade->value }}" @selected(old('grade_level') === $grade->value)>{{ __($grade->label()) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="label" for="password">{{ __('Password') }}</label>
        <div class="relative">
            <input class="input !pe-12 @error('password') !border-rose-400 @enderror" id="password" name="password" type="password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required>
            <button type="button" @click="showPassword = !showPassword"
                class="absolute inset-y-0 end-0 flex cursor-pointer items-center justify-center pe-4 text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-300"
                :aria-label="showPassword ? '{{ addslashes(__('Hide password')) }}' : '{{ addslashes(__('Show password')) }}'" tabindex="-1">
                <x-icon name="eye-off" class="h-5 w-5" x-show="showPassword" x-cloak/>
                <x-icon name="eye" class="h-5 w-5" x-show="!showPassword" x-cloak/>
            </button>
        </div>
        @error('password')
            <p class="mt-1.5 text-xs font-medium text-rose-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="label" for="password_confirmation">{{ __('Confirm Password') }}</label>
        <div class="relative">
            <input class="input !pe-12" id="password_confirmation" name="password_confirmation" type="password" :type="showConfirmPassword ? 'text' : 'password'" autocomplete="new-password" required data-password-match>
            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                class="absolute inset-y-0 end-0 flex cursor-pointer items-center justify-center pe-4 text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-300"
                :aria-label="showConfirmPassword ? '{{ addslashes(__('Hide password')) }}' : '{{ addslashes(__('Show password')) }}'" tabindex="-1">
                <x-icon name="eye-off" class="h-5 w-5" x-show="showConfirmPassword" x-cloak/>
                <x-icon name="eye" class="h-5 w-5" x-show="!showConfirmPassword" x-cloak/>
            </button>
        </div>
        <p id="password_match_error" class="mt-1.5 hidden text-xs font-medium text-rose-500"></p>
    </div>
    <button class="btn w-full !py-3 text-base">{{ __('Register') }} <x-icon name="arrow" class="h-4 w-4 rtl:-scale-x-100" :stroke="2.2"/></button>
</form>
<p class="mt-5 text-center text-sm text-slate-500 dark:text-slate-400">
    {{ __('Already registered?') }}
    <a class="font-bold text-brand-600 transition hover:text-brand-500 dark:text-brand-300" href="{{ route('login') }}">{{ __('Log in') }}</a>
</p>

<script>
    (function () {
        const MISMATCH = @json(__('Passwords do not match. Please make sure they are identical.'));

        // Real-time localized password confirmation matching.
        var pw = document.getElementById('password');
        var conf = document.getElementById('password_confirmation');
        var errEl = document.getElementById('password_match_error');

        function validateMatch() {
            var value = conf.value;
            var matches = value === pw.value;
            var showError = value.length > 0 && !matches;

            conf.classList.toggle('!border-rose-400', showError);
            errEl.textContent = showError ? MISMATCH : '';
            errEl.classList.toggle('hidden', !showError);
        }

        if (pw && conf) {
            pw.addEventListener('input', validateMatch);
            conf.addEventListener('input', validateMatch);
        }
    })();
</script>
@endsection