<x-layouts.app :title="'Notifications'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Notifications</h2>
        </div>
        <div class="flex items-center gap-2">
            @if ($notifications->where('read_at', null)->count() > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">
                        Mark all read
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        @forelse ($notifications as $notification)
            @php
                $unread = $notification->read_at === null;
                $nData = $notification->data;
            @endphp
            <a href="{{ route('notifications.open', $notification->id) }}"
               class="flex items-start gap-3 border-b border-[#dbe4f0] px-4 py-3.5 transition hover:bg-[#eaf3ff] {{ $unread ? 'bg-sky-50/70' : '' }}">
                <x-notification-icon :kind="$nData['kind'] ?? 'info'" class="h-4 w-4" />
                <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-2">
                        <span class="text-[13.5px] font-bold text-[#0a1633]">{{ $nData['title'] ?? 'Notification' }}</span>
                        @if ($unread)
                            <span class="rounded-full bg-sky-500 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">New</span>
                        @endif
                    </span>
                    <span class="mt-0.5 block text-[12.5px] leading-snug text-slate-600">{{ $nData['message'] ?? '' }}</span>
                    <time class="mt-1 block text-[11px] font-medium text-slate-400">{{ $notification->created_at->format('M d, Y g:i A') }}</time>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 self-center text-slate-300">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        @empty
            <div class="px-6 py-14 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#eaf3ff]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7 text-[#164aa8]">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </div>
                <p class="text-[14px] font-semibold text-[#0a1633]">You're all caught up</p>
                <p class="mt-1 text-[12.5px] text-slate-500">Notifications about your enrollment, grades and account will appear here.</p>
            </div>
        @endforelse

        @if ($notifications->hasPages())
            <div class="border-t border-[#dbe4f0] bg-white/70 px-4 py-3">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
