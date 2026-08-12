<x-layouts.app :title="'Student Dashboard'">
    <div id="poll-dashboard" class="flex flex-col gap-6 lg:gap-7">
        {{-- Hero --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] p-6 text-white shadow-[0_12px_30px_-12px_rgba(10,22,51,0.7)]">
            <div class="pointer-events-none absolute inset-0"
                 style="background-image: linear-gradient(rgba(148,197,255,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(148,197,255,0.08) 1px, transparent 1px); background-size: 34px 34px;"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(56,189,248,0.22),transparent_55%)]"></div>
            <div class="relative z-10">
                <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-white/55">Student Dashboard</p>
                <h2 class="m-0 mt-1 text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h2>
                <p class="mt-1.5 text-[13.5px] text-white/70">Don Mariano Marcos National High School Student Portal</p>
            </div>
        </div>

        <div class="flex items-start gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 p-3.5 shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)]">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#0018f9]/10 text-[#0018f9]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4.5 w-4.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </span>
            <p class="m-0 text-[13px] leading-relaxed text-slate-600">
                <strong class="text-[#0a1633]">Privacy Notice:</strong> Screenshotting, recording, or sharing Grades, Profile
                Info, Scores, and other student records without permission is strictly prohibited for data privacy. The
                administration also commits to protecting each user's private data.
            </p>
        </div>

        {{-- Quick access --}}
        <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('student.info') }}" class="group relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 no-underline shadow-[0_8px_20px_-10px_rgba(0,24,249,0.2)] transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_12px_26px_-10px_rgba(0,24,249,0.3)]">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
                <span class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-[#0018f9] to-[#38bdf8] text-white shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5.5 w-5.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </span>
                <span class="block text-[14.5px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">Student Info</span>
                <span class="mt-0.5 block text-[12.5px] leading-snug text-slate-500">View and update your personal information, change password, and check your status.</span>
            </a>
            <a href="{{ route('student.grades') }}" class="group relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 no-underline shadow-[0_8px_20px_-10px_rgba(0,24,249,0.2)] transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_12px_26px_-10px_rgba(0,24,249,0.3)]">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-emerald-400 to-teal-300"></div>
                <span class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-400 text-white shadow-[0_4px_14px_-4px_rgba(16,185,129,0.6)]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5.5 w-5.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 5.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v14.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V5.625Z" /></svg>
                </span>
                <span class="block text-[14.5px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">Student Grades</span>
                <span class="mt-0.5 block text-[12.5px] leading-snug text-slate-500">Check your current and previous grades with color-coded performance.</span>
            </a>
            <a href="{{ route('student.enrollment') }}" class="group relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 no-underline shadow-[0_8px_20px_-10px_rgba(0,24,249,0.2)] transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_12px_26px_-10px_rgba(0,24,249,0.3)]">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-violet-400 to-purple-300"></div>
                <span class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-400 text-white shadow-[0_4px_14px_-4px_rgba(139,92,246,0.6)]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5.5 w-5.5" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                </span>
                <span class="block text-[14.5px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">Enrollment Request</span>
                <span class="mt-0.5 block text-[12.5px] leading-snug text-slate-500">Submit enrollment requests to your advisers for approval.</span>
            </a>
            <a href="{{ route('student.timeline') }}" class="group relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 no-underline shadow-[0_8px_20px_-10px_rgba(0,24,249,0.2)] transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_12px_26px_-10px_rgba(0,24,249,0.3)]">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-amber-400 to-yellow-300"></div>
                <span class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-yellow-400 text-white shadow-[0_4px_14px_-4px_rgba(245,158,11,0.6)]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5.5 w-5.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 5.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v14.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V5.625Z" /></svg>
                </span>
                <span class="block text-[14.5px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">My Scores</span>
                <span class="mt-0.5 block text-[12.5px] leading-snug text-slate-500">View your Activity, Quiz, and Exam scores submitted by your adviser.</span>
            </a>
        </div>

        {{-- Important dates + Recent activity --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div>
                <x-important-dates :items="$importantDates" :view-all-url="route('student.important-dates')" :limit="5" />
            </div>

            <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] backdrop-blur-sm">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-4 w-4 rounded-md bg-gradient-to-br from-[#0018f9] to-[#38bdf8] p-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z" />
                            </svg>
                        </span>
                        <h3 class="m-0 text-[14.5px] font-semibold text-[#0a1633]">Recent Academic Activity</h3>
                    </div>
                    <a href="{{ route('student.timeline') }}"
                       class="rounded-lg border border-[#0018f9]/15 bg-white px-3 py-1.5 text-[12px] font-semibold text-[#0a1633] no-underline transition hover:bg-[#f4f8ff] hover:text-[#0018f9]">View Full Timeline</a>
                </div>

                @if ($recentTimeline->isEmpty())
                    <p class="m-0 py-4 text-center text-[13px] text-slate-500">My academic journey has not started yet.</p>
                @else
                    <div class="flex flex-col">
                        @foreach ($recentTimeline as $event)
                            <a href="{{ $event->url ?? route('student.timeline') }}"
                               class="group flex items-start gap-3 rounded-lg px-2 py-2.5 no-underline transition hover:bg-[#f4f8ff]">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#0018f9]/10 text-[#0018f9]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13.5px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">{{ $event->title }}</span>
                                    <span class="block truncate text-[12px] text-slate-500">{{ $event->detail }}</span>
                                </span>
                                <span class="mt-0.5 shrink-0 rounded-md border border-[#0018f9]/10 bg-[#f4f8ff] px-2 py-0.5 text-[10.5px] font-semibold uppercase tracking-wide text-[#0018f9]/70">
                                    {{ \App\Services\StudentTimelineService::relativeLabel($event->at) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Announcements --}}
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
</x-layouts.app>
