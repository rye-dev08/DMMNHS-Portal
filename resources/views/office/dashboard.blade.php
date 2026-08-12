<x-layouts.app :title="'Office Administrator Dashboard'">
    <div id="poll-dashboard" class="flex flex-col gap-6 lg:gap-7">
        {{-- Hero --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] p-6 text-white shadow-[0_12px_30px_-12px_rgba(10,22,51,0.7)]">
            <div class="pointer-events-none absolute inset-0"
                 style="background-image: linear-gradient(rgba(148,197,255,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(148,197,255,0.08) 1px, transparent 1px); background-size: 34px 34px;"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(56,189,248,0.22),transparent_55%)]"></div>
            <div class="relative z-10">
                <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-white/55">Office Administrator Dashboard</p>
                <h2 class="m-0 mt-1 text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h2>
                <p class="mt-1.5 text-[13.5px] text-white/70">Academic calendar, announcements, and school-wide requirement tracking.</p>
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-2 gap-3.5 lg:grid-cols-4">
            <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_8px_20px_-10px_rgba(0,24,249,0.2)]">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
                <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $stats->requirements }}</p>
                <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Active Requirements</p>
            </div>
            <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_1px_20px_-10px_rgba(0,24,249,0.2)]">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
                <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $stats->pendingSubmissions }}</p>
                <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Awaiting Review</p>
            </div>
            <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_1px_20px_-10px_rgba(0,24,249,0.2)]">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
                <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $stats->upcomingEvents }}</p>
                <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Upcoming Events</p>
            </div>
            <div class="relative col-span-2 overflow-hidden rounded-xl border border-amber-300/50 bg-white/80 p-4 shadow-[0_1px_20px_-10px_rgba(245,158,11,0.25)] lg:col-span-1">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-amber-400 to-yellow-300"></div>
                <p class="m-0 text-2xl font-bold text-amber-600">{{ $stats->pendingMessages }}</p>
                <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Pending Messages</p>
            </div>
        </div>

        {{-- Quick Actions + Announcements --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-card :title="'Quick Actions'">
                <div class="grid gap-2">
                    <a href="{{ route('office.academic-calendar') }}" class="group flex items-center gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 px-3.5 py-3 no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_8px_18px_-8px_rgba(0,24,249,0.25)]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#0018f9] to-[#38bdf8] text-white shadow-[0_4px_12px_-4px_rgba(0,24,249,0.6)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[14px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">Academic Calendar</span>
                            <span class="block text-[12.5px] text-slate-500">Manage school events &amp; schedules.</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0018f9]/40 transition group-hover:translate-x-0.5 group-hover:text-[#0018f9]"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="{{ route('office.announcements') }}" class="group flex items-center gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 px-3.5 py-3 no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_8px_18px_-8px_rgba(0,24,249,0.25)]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-400 text-white shadow-[0_4px_12px_-4px_rgba(139,92,246,0.6)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[14px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">Announcements</span>
                            <span class="block text-[12.5px] text-slate-500">Publish notices for students &amp; teachers.</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0018f9]/40 transition group-hover:translate-x-0.5 group-hover:text-[#0018f9]"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="{{ route('office.message-center') }}" class="group flex items-center gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 px-3.5 py-3 no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_8px_18px_-8px_rgba(0,24,249,0.25)]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-sky-500 to-cyan-400 text-white shadow-[0_4px_12px_-4px_rgba(14,165,233,0.6)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[14px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">Message Center</span>
                            <span class="block text-[12.5px] text-slate-500">Review contact &amp; moderation requests.</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0018f9]/40 transition group-hover:translate-x-0.5 group-hover:text-[#0018f9]"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="{{ route('office.teacher-advisory') }}" class="group flex items-center gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 px-3.5 py-3 no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_8px_18px_-8px_rgba(0,24,249,0.25)]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-yellow-400 text-white shadow-[0_4px_12px_-4px_rgba(245,158,11,0.6)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[14px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">Teacher Advisory</span>
                            <span class="block text-[12.5px] text-slate-500">Assign advisory classes to teachers.</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0018f9]/40 transition group-hover:translate-x-0.5 group-hover:text-[#0018f9]"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="{{ route('office.grade-submissions') }}" class="group flex items-center gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 px-3.5 py-3 no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_8px_18px_-8px_rgba(0,24,249,0.25)]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-400 text-white shadow-[0_4px_12px_-4px_rgba(16,185,129,0.6)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[14px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">Grade Submission Monitor</span>
                            <span class="block text-[12.5px] text-slate-500">Track teacher grade submissions.</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0018f9]/40 transition group-hover:translate-x-0.5 group-hover:text-[#0018f9]"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                </div>
            </x-card>

            <div>
                @include('announcements.feed', [
                    'announcements' => $announcements,
                    'unreadCount' => $announcementUnread,
                    'heading' => 'Announcements',
                    'context' => 'dashboard',
                    'limit' => 6,
                ])
            </div>
        </div>

        {{-- Progress + Important dates --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div>
                <x-grade-submission-progress :summary="$gradeSummary" :view-all-url="route('office.grade-submissions')" />
            </div>
            <div>
                <x-important-dates :items="$importantDates" :view-all-url="route('office.important-dates')" :limit="5" />
            </div>
        </div>
    </div>
</x-layouts.app>
