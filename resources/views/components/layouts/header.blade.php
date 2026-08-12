@php
    $role = auth()->check() ? auth()->user()->role : '';
    $period = auth()->check() ? \App\Models\Setting::find(1)?->period() : null;
    $unreadCount = 0;
    $recentNotifications = collect();
    if (auth()->check()) {
        $unreadCount = auth()->user()->unreadNotifications()->count();
        $recentNotifications = auth()->user()->notifications()->latest()->limit(8)->get();
    }
@endphp

<header id="app-header" class="sticky top-0 z-20 flex items-center gap-3 border-b border-white/10 bg-[linear-gradient(120deg,#0a1633,#0d2450,#164aa8)] px-4 py-2.5 text-white shadow-[0_4px_18px_rgba(2,6,23,0.25)] lg:px-6">
    <button id="menu-toggle" type="button" aria-label="Open Menu"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-[22px] text-white transition hover:bg-white/20 lg:hidden">
        &#9776;
    </button>

    <div class="flex items-center gap-3">
        <img src="{{ asset('images/dmnhs-no-bg.jpg') }}" alt="School Logo"
             class="h-11 w-11 rounded-[10px] border border-white/30 bg-white/10 object-cover">
        <div class="hidden sm:block">
            <h1 class="text-[16px] font-bold leading-tight">Don Mariano Marcos National High School Portal</h1>
            <p class="mt-0.5 text-[12px] text-white/65">Student Information and Grade Management System</p>
        </div>
    </div>

    <div class="ml-auto flex items-center gap-2.5">
        @if ($period)
            <span class="hidden rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[12px] font-medium text-white/80 md:inline-flex"
                  title="Current academic period">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="mr-1.5 h-3.5 w-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                {{ $period->label }}
            </span>
        @endif
        @if (auth()->check())
            <div id="notif-dropdown-root" class="relative">
                <button id="notif-bell" type="button" aria-label="Notifications"
                        class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-white transition hover:bg-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    @if ($unreadCount > 0)
                        <span id="notif-badge" class="absolute -right-1 -top-1 inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full border-2 border-[#0d2450] bg-red-500 px-1 text-[10px] font-bold text-white">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                    @endif
                </button>

                <div id="notif-panel" class="absolute right-0 z-30 mt-2 hidden w-[min(92vw,340px)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_18px_50px_-12px_rgba(2,6,23,0.35)]">
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-3">
                        <span class="text-[13px] font-bold text-white">Notifications</span>
                        @if ($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.read-all') }}">
                                @csrf
                                <button type="submit" class="text-[11px] font-semibold text-sky-300 transition hover:text-white">
                                    Mark all read
                                </button>
                            </form>
                        @endif
                    </div>

                    <ul class="max-h-[340px] overflow-y-auto">
                        @forelse ($recentNotifications as $notification)
                            @php
                                $unread = $notification->read_at === null;
                                $nData = $notification->data;
                            @endphp
                            <li class="{{ $loop->first ? '' : 'border-t border-slate-100' }}">
                                <a href="{{ route('notifications.open', $notification->id) }}"
                                   class="flex items-start gap-3 px-4 py-3 transition hover:bg-slate-50 {{ $unread ? 'bg-sky-50/70' : '' }}">
                                    <x-notification-icon :kind="$nData['kind'] ?? 'info'" class="h-4 w-4" />
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center gap-2">
                                            <span class="truncate text-[12.5px] font-bold text-slate-800">{{ $nData['title'] ?? 'Notification' }}</span>
                                            @if ($unread)
                                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-sky-500"></span>
                                            @endif
                                        </span>
                                        <span class="mt-0.5 block text-[11.5px] leading-snug text-slate-500">{{ $nData['message'] ?? '' }}</span>
                                        <time class="mt-1 block text-[10.5px] font-medium text-slate-400">{{ $notification->created_at->diffForHumans() }}</time>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="px-4 py-8 text-center">
                                <p class="text-[12.5px] font-semibold text-slate-500">You're all caught up</p>
                                <p class="mt-1 text-[11px] text-slate-400">No notifications yet.</p>
                            </li>
                        @endforelse
                    </ul>

                    <div class="border-t border-slate-100 bg-slate-50 px-4 py-2.5 text-center">
                        <a href="{{ route('notifications.index') }}" class="text-[12px] font-semibold text-sky-600 transition hover:text-sky-800">
                            View all notifications
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <span class="hidden rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[12px] font-medium capitalize text-white/80 md:inline-flex">
            {{ str_replace('_', ' ', $role) }} account
        </span>
    </div>
</header>