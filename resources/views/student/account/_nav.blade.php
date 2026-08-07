@php
    $u = auth()->user();
    $items = [
        ['key' => 'profile',     'label' => __('Profile'),                  'desc' => __('Personal info & photo'),  'route' => 'profile.edit',        'pattern' => 'profile.*'],
        ['key' => 'security',    'label' => __('Security & Login History'), 'desc' => __('Password & devices'),     'route' => 'account.security',    'pattern' => 'account.security'],
        ['key' => 'exam',        'label' => __('Exam Results'),             'desc' => __('Quiz scores & reviews'),  'route' => 'account.exams',       'pattern' => 'account.exams'],
        ['key' => 'assignment', 'label' => __('Assignment Results'),       'desc' => __('Grades & feedback'),      'route' => 'account.assignments', 'pattern' => 'account.assignments'],
        ['key' => 'video',      'label' => __('Video Views'),              'desc' => __('Watch history'),          'route' => 'account.videos',      'pattern' => 'account.videos'],
    ];
@endphp

<aside class="card p-4 lg:sticky lg:top-6">
    {{-- Mini profile card --}}
    <div class="mb-4 flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
        @if($u->avatarUrl())
            <img src="{{ $u->avatarUrl() }}" alt="" class="h-11 w-11 rounded-full object-cover ring-2 ring-emerald-500/30">
        @else
            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white ring-2 ring-emerald-500/30">{{ $u->initials() }}</span>
        @endif
        <div class="min-w-0">
            <div class="truncate text-sm font-bold">{{ $u->name }}</div>
            <div class="truncate font-mono text-xs text-gray-400" dir="ltr">{{ $u->phone }}</div>
        </div>
    </div>

    {{-- Vertical menu --}}
    <nav class="space-y-1">
        @foreach($items as $item)
            @php($active = request()->routeIs($item['pattern']))
            <a href="{{ route($item['route']) }}"
               class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-200
                      {{ $active
                            ? 'bg-emerald-500/10 dark:bg-emerald-500/15'
                            : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">

                {{-- Active indicator bar (leading edge — right side in RTL) --}}
                @if($active)
                    <span class="absolute inset-y-2 start-0 w-1 rounded-full bg-emerald-500"></span>
                @endif

                {{-- Icon tile --}}
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition-colors duration-200
                             {{ $active
                                   ? 'bg-emerald-500 text-white shadow-sm shadow-emerald-500/30'
                                   : 'bg-gray-100 text-gray-500 group-hover:bg-white group-hover:text-emerald-600 dark:bg-gray-800 dark:text-gray-400 dark:group-hover:bg-gray-700 dark:group-hover:text-emerald-400' }}">
                    <x-icon :name="$item['key']" class="h-5 w-5" />
                </span>

                {{-- Label + description --}}
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold {{ $active ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-700 dark:text-gray-200' }}">
                        {{ $item['label'] }}
                    </span>
                    <span class="block truncate text-xs text-gray-400 dark:text-gray-500">{{ $item['desc'] }}</span>
                </span>

                {{-- Chevron (auto-flips in RTL) --}}
                <svg class="h-4 w-4 shrink-0 transition-transform duration-200 group-hover:translate-x-0.5 rtl:-scale-x-100 rtl:group-hover:-translate-x-0.5 {{ $active ? 'text-emerald-500' : 'text-gray-300 dark:text-gray-600' }}"
                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                </svg>
            </a>
        @endforeach
    </nav>
</aside>
