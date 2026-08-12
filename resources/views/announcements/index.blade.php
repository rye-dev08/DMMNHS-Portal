<x-layouts.app :title="'Announcements'">
    <div class="mb-5 flex items-center gap-2">
        <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
        <h2 class="m-0 text-[#0a1633]">Announcements</h2>
    </div>

    @include('announcements.feed', [
        'announcements' => $announcements,
        'unreadCount' => $unreadCount,
        'heading' => 'School Announcements',
        'context' => 'page',
    ])
</x-layouts.app>
