<x-layouts.app :title="'Admin Dashboard'">
    {{-- Hero --}}
    <div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] p-6 text-white shadow-[0_12px_30px_-12px_rgba(10,22,51,0.7)]">
        <div class="pointer-events-none absolute inset-0"
             style="background-image: linear-gradient(rgba(148,197,255,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(148,197,255,0.08) 1px, transparent 1px); background-size: 34px 34px;"></div>
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(56,189,248,0.22),transparent_55%)]"></div>
        <div class="relative z-10">
            <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-white/55">Admin Dashboard</p>
            <h2 class="m-0 mt-1 text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h2>
            <p class="mt-1.5 text-[13.5px] text-white/70">Manage accounts, teachers, and enrollment at a glance.</p>
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
            <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $stats->admins }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Admins</p>
        </div>
        <div class="relative col-span-2 overflow-hidden rounded-xl border border-amber-300/50 bg-white/80 p-4 shadow-[0_1px_20px_-10px_rgba(245,158,11,0.25)] lg:col-span-1">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-amber-400 to-yellow-300"></div>
            <p class="m-0 text-2xl font-bold text-amber-600">{{ $stats->pendingEnrollments }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Pending Enrollments</p>
        </div>
    </div>

    <h3 class="mb-3 flex items-center gap-2 text-[15px] font-semibold text-[#0a1633]">
        <span class="inline-block h-4 w-1 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
        Quick Actions
    </h3>
    <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.accounts') }}" class="group rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 no-underline shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)] transition hover:-translate-y-0.5 hover:shadow-[0_10px_26px_-10px_rgba(0,24,249,0.3)]">
            <span class="block text-[15px] font-semibold text-[#0a1633]">Manage Accounts</span>
            <span class="mt-0.5 block text-[12.5px] text-slate-500">Create, edit, and manage users.</span>
        </a>
        <a href="{{ route('admin.accounts.create') }}" class="group rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 no-underline shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)] transition hover:-translate-y-0.5 hover:shadow-[0_10px_26px_-10px_rgba(0,24,249,0.3)]">
            <span class="block text-[15px] font-semibold text-[#0a1633]">Create Account</span>
            <span class="mt-0.5 block text-[12.5px] text-slate-500">Add a new user to the portal.</span>
        </a>
        <a href="{{ route('admin.teacher-advisory') }}" class="group rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 no-underline shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)] transition hover:-translate-y-0.5 hover:shadow-[0_10px_26px_-10px_rgba(0,24,249,0.3)]">
            <span class="block text-[15px] font-semibold text-[#0a1633]">Teacher Advisory</span>
            <span class="mt-0.5 block text-[12.5px] text-slate-500">Set advisory classes for teachers.</span>
        </a>
        <a href="{{ route('admin.enrollment-settings') }}" class="rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 no-underline shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)] transition hover:-translate-y-0.5 hover:shadow-[0_10px_26px_-10px_rgba(0,24,249,0.3)]">
            <span class="block text-[15px] font-semibold text-[#0a1633]">Enrollment Settings</span>
            <span class="mt-0.5 block text-[12.5px] text-slate-500">Term &amp; school year controls.</span>
        </a>
    </div>
</x-layouts.app>
