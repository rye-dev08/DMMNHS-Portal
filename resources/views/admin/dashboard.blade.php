<x-layouts.app :title="'System Administrator Dashboard'">
    {{-- Hero --}}
    <div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] p-6 text-white shadow-[0_12px_30px_-12px_rgba(10,22,51,0.7)]">
        <div class="pointer-events-none absolute inset-0"
             style="background-image: linear-gradient(rgba(148,197,255,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(148,197,255,0.08) 1px, transparent 1px); background-size: 34px 34px;"></div>
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(56,189,248,0.22),transparent_55%)]"></div>
        <div class="relative z-10">
            <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-white/55">System Administrator Dashboard</p>
            <h2 class="m-0 mt-1 text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h2>
            <p class="mt-1.5 text-[13.5px] text-white/70">Manage accounts, enrollment, and portal-wide settings at a glance.</p>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="mb-6 grid grid-cols-2 gap-3.5 lg:grid-cols-4">
        <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_8px_20px_-10px_rgba(0,24,249,0.2)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
            <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $stats->students }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Students</p>
        </div>
        <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_1px_20px_-10px_rgba(0,24,249,0.2)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
            <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $stats->teachers }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Teachers</p>
        </div>
        <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_1px_20px_-10px_rgba(0,24,249,0.2)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
            <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $stats->officeAdmins }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Office Admins</p>
        </div>
        <div class="relative col-span-2 overflow-hidden rounded-xl border border-amber-300/50 bg-white/80 p-4 shadow-[0_1px_20px_-10px_rgba(245,158,11,0.25)] lg:col-span-1">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-amber-400 to-yellow-300"></div>
            <p class="m-0 text-2xl font-bold text-amber-600">{{ $stats->pendingEnrollments }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Pending Enrollments</p>
        </div>
    </div>

    {{-- Organized card grid --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="flex flex-col gap-6">
            <x-card :title="'Quick Actions'">
                <div class="grid gap-2">
                    <a href="{{ route('admin.accounts') }}" class="group flex items-center gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 px-3.5 py-3 no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_8px_18px_-8px_rgba(0,24,249,0.25)]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#0018f9] to-[#38bdf8] text-white shadow-[0_4px_12px_-4px_rgba(0,24,249,0.6)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[14px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">Manage Accounts</span>
                            <span class="block text-[12.5px] text-slate-500">Create, edit, and manage users.</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0018f9]/40 transition group-hover:translate-x-0.5 group-hover:text-[#0018f9]"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="{{ route('admin.accounts.create') }}" class="group flex items-center gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 px-3.5 py-3 no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_8px_18px_-8px_rgba(0,24,249,0.25)]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-400 text-white shadow-[0_4px_12px_-4px_rgba(16,185,129,0.6)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[14px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">Create Account</span>
                            <span class="block text-[12.5px] text-slate-500">Add a new user to the portal.</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0018f9]/40 transition group-hover:translate-x-0.5 group-hover:text-[#0018f9]"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="{{ route('admin.enrollment-settings') }}" class="group flex items-center gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 px-3.5 py-3 no-underline shadow-sm transition hover:-translate-y-0.5 hover:border-[#0018f9]/30 hover:shadow-[0_8px_18px_-8px_rgba(0,24,249,0.25)]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-yellow-400 text-white shadow-[0_4px_12px_-4px_rgba(245,158,11,0.6)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" /></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[14px] font-semibold text-[#0a1633] group-hover:text-[#0018f9]">Enrollment Settings</span>
                            <span class="block text-[12.5px] text-slate-500">Term &amp; school year controls.</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0018f9]/40 transition group-hover:translate-x-0.5 group-hover:text-[#0018f9]"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                </div>
            </x-card>

            <x-card :title="'Office Operations'">
                <p class="m-0 text-[13px] leading-relaxed text-slate-600">
                    Day-to-day academic operations — calendar, announcements, message review, and
                    requirement tracking — are handled by the <strong class="text-[#0a1633]">Office Administrator</strong> role.
                </p>
            </x-card>
        </div>

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
