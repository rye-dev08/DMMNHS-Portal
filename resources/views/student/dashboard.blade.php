<x-layouts.app :title="'Student Dashboard'">
    {{-- Hero --}}
    <div class="relative mb-5 overflow-hidden rounded-2xl bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] p-6 text-white shadow-[0_12px_30px_-12px_rgba(10,22,51,0.7)]">
        <div class="pointer-events-none absolute inset-0"
             style="background-image: linear-gradient(rgba(148,197,255,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(148,197,255,0.08) 1px, transparent 1px); background-size: 34px 34px;"></div>
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(56,189,248,0.22),transparent_55%)]"></div>
        <div class="relative z-10">
            <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-white/55">Student Dashboard</p>
            <h2 class="m-0 mt-1 text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h2>
            <p class="mt-1.5 text-[13.5px] text-white/70">Don Mariano Marcos National High School Student Portal</p>
        </div>
    </div>

    <div class="mb-5 flex items-start gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 p-3.5 shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)]">
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

    <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
        <x-card :title="'Student Info'">
            <p class="text-[14px] leading-relaxed text-slate-600">View and update your personal information, change password, and check your status.</p>
        </x-card>
        <x-card :title="'Student Grades'">
            <p class="text-[14px] leading-relaxed text-slate-600">Check your current and previous grades with color-coded performance.</p>
        </x-card>
        <x-card :title="'Enrollment Request'">
            <p class="text-[14px] leading-relaxed text-slate-600">Submit enrollment requests to your advisers for approval.</p>
        </x-card>
        <x-card :title="'My Scores'">
            <p class="text-[14px] leading-relaxed text-slate-600">View your Activity, Quiz, and Exam scores submitted by your adviser.</p>
        </x-card>
    </div>
</x-layouts.app>