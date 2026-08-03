<x-layouts.app :title="'Teacher Dashboard'">
    {{-- Hero --}}
    <div class="relative mb-5 overflow-hidden rounded-2xl bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] p-6 text-white shadow-[0_12px_30px_-12px_rgba(10,22,51,0.7)]">
        <div class="pointer-events-none absolute inset-0"
             style="background-image: linear-gradient(rgba(148,197,255,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(148,197,255,0.08) 1px, transparent 1px); background-size: 34px 34px;"></div>
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(56,189,248,0.22),transparent_55%)]"></div>
        <div class="relative z-10">
            <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-white/55">Teacher Dashboard</p>
            <h2 class="m-0 mt-1 text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h2>
            <p class="mt-1.5 text-[13.5px] text-white/70">Advisory: <strong class="text-white">{{ $advisory !== '' ? $advisory : 'Not set' }}</strong></p>
        </div>
    </div>

    <div class="mb-5 flex items-start gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 p-3.5 shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)]">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#0018f9]/10 text-[#0018f9]">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4.5 w-4.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </span>
        <p class="m-0 text-[13px] leading-relaxed text-slate-600">
            <strong class="text-[#0a1633]">Privacy Notice:</strong> Screenshotting, recording, or sharing student Grades,
            Profile Info, Scores, and similar records without authorization is strictly prohibited for data privacy. The
            administration also maintains privacy and confidentiality of all user records.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-3">
        <x-card :title="'Advisory Class'">
            <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $advisory !== '' ? $advisory : 'Not set' }}</p>
            <p class="mt-1 text-[12.5px] text-slate-500">Assigned advisory section</p>
        </x-card>
        <x-card :title="'Subject Entries'">
            <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $subjectsCount }}</p>
            <p class="mt-1 text-[12.5px] text-slate-500">Subjects defined in your portal</p>
        </x-card>
        <x-card :title="'Approved Students'">
            <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $approvedCount }}</p>
            <p class="mt-1 text-[12.5px] text-slate-500">Enrollment requests approved</p>
        </x-card>
    </div>

    <x-card :title="'Assessment Scores Module'" class="mt-3.5">
        <p class="m-0 text-[14px] leading-relaxed text-slate-600">
            Use <strong class="text-[#0a1633]">Assessment Scores</strong> to add Activity, Quiz, and Exam items per
            student with auto-increment item numbers, then update or delete entries when needed.
        </p>
    </x-card>
</x-layouts.app>