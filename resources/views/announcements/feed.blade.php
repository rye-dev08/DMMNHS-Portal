@php
    $feedItems = $announcements ?? collect();
    $limit = $limit ?? null;
    $context = $context ?? 'dashboard';
    $heading = $heading ?? 'Announcements';
    $unreadCount = $unreadCount ?? 0;
    $shownItems = $limit ? $feedItems->take($limit) : $feedItems;
@endphp

<div class="overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
    <div class="flex items-center justify-between gap-3 border-b border-[#0018f9]/10 bg-white/40 px-4 py-3">
        <h3 class="m-0 text-[15px] font-bold text-[#0a1633]">{{ $heading }}</h3>
        <span class="announcement-unread-badge inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-2.5 py-1 text-[11px] font-semibold text-white {{ $unreadCount > 0 ? '' : 'hidden' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3 w-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            <span data-unread-count>{{ $unreadCount }}</span> new
        </span>
    </div>

    <ul class="divide-y divide-[#0018f9]/10">
        @forelse ($shownItems as $announcement)
            @php
                $accent = announcement_priority_style((string) $announcement->priority, 'accent');
                $badge = announcement_priority_style((string) $announcement->priority, 'badge');
                $isRead = (bool) ($announcement->is_read ?? false);
            @endphp
            <li>
                <button type="button" data-announcement-id="{{ $announcement->id }}"
                        class="announcement-row group flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-[#f4f8ff]">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $accent }} text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3Z" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="text-[13.5px] font-bold text-[#0a1633] group-hover:text-[#0018f9]">{{ $announcement->title }}</span>
                            @if ($announcement->priority !== 'normal')
                                <span class="inline-flex items-center rounded-md border px-1.5 py-0.5 text-[10.5px] font-semibold {{ $badge }}">{{ $announcement->priorityLabel() }}</span>
                            @endif
                        </span>
                        @if ($announcement->short_summary)
                            <span class="mt-0.5 block truncate text-[12.5px] text-slate-600">{{ $announcement->short_summary }}</span>
                        @endif
                        <span class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-400">
                            @if ($announcement->publish_date)
                                {{ $announcement->publish_date->format('M d, Y') }}
                            @endif
                            @if ($announcement->target_label)
                                <span>&middot;</span>
                                <span class="truncate">{{ $announcement->target_label }}</span>
                            @endif
                        </span>
                    </span>
                    <span class="announcement-dot-{{ $announcement->id }} h-2 w-2 shrink-0 rounded-full {{ $isRead ? 'bg-slate-300' : 'bg-[#0018f9]' }}"></span>
                </button>
            </li>
        @empty
            <li class="px-4 py-8 text-center">
                <p class="m-0 text-[13px] font-semibold text-slate-500">No announcements</p>
                <p class="mt-1 m-0 text-[12px] text-slate-400">Check back later for school updates.</p>
            </li>
        @endforelse
    </ul>

    @if ($context === 'dashboard' && $feedItems->count() > ($limit ?: 0))
        <div class="border-t border-[#0018f9]/10 bg-white/40 px-4 py-2.5 text-center">
            <a href="{{ route('announcements') }}" class="text-[12.5px] font-semibold text-[#0018f9] transition hover:text-[#0080fe]">
                View all announcements →
            </a>
        </div>
    @endif
</div>

@include('announcements.announcement-modal')

<script>
    (function () {
        window.announcementsData = window.announcementsData || {};
        @foreach ($feedItems as $announcement)
            window.announcementsData[{{ $announcement->id }}] = {
                id: {{ $announcement->id }},
                title: @json($announcement->title),
                summary: @json($announcement->short_summary),
                content: @json($announcement->content),
                priority: @json($announcement->priority),
                priority_label: @json($announcement->priorityLabel()),
                priority_badge: @json($badge = announcement_priority_style((string) $announcement->priority, 'badge')),
                priority_accent: @json(announcement_priority_style((string) $announcement->priority, 'accent')),
                publish_date: @json($announcement->publish_date ? $announcement->publish_date->format('M d, Y') : ''),
                expiration_date: @json($announcement->expiration_date ? $announcement->expiration_date->format('M d, Y') : ''),
                attachment_url: @json($announcement->attachment ? asset('storage/' . $announcement->attachment) : ''),
                attachment_name: @json($announcement->attachment_name),
                target_label: @json($announcement->target_label),
                is_read: @json((bool) ($announcement->is_read ?? false))
            };
        @endforeach

        document.querySelectorAll('.announcement-row').forEach(function (row) {
            row.addEventListener('click', function () {
                var id = parseInt(row.getAttribute('data-announcement-id'), 10);
                openAnnouncement(id);
            });
        });
    })();
</script>
