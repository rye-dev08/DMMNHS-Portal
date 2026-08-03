@php
    $info = $user->teacher;
    $fields = [
        'Full Name' => $user->name ?? 'N/A',
        'Username' => $user->username ?? 'N/A',
        'Email' => $user->email ?? 'N/A',
        'Account Status' => $user->status ?? 'N/A',
        'Advisory Class' => $info->advisory_class ?: 'Not set',
        'Max Students' => ((int) ($info->max_students ?? 0)) > 0 ? (int) $info->max_students : 'Not set',
        'Max Subjects Per Student' => ((int) ($info->max_subjects ?? 0)) > 0 ? (int) $info->max_subjects : 'Not set',
    ];
@endphp

<x-layouts.app :title="'Teacher Info'">
    <h2 class="text-center">Teacher Info</h2>

    <section class="relative mx-auto my-4 max-w-[640px] overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)]">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
        <div class="relative">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ($fields as $label => $value)
                    <div class="rounded-lg border border-[#0018f9]/10 bg-white p-3 shadow-sm">
                        <span class="block text-[12px] font-semibold uppercase tracking-wide text-[#0018f9]/70">{{ $label }}</span>
                        <span class="block text-[15px] font-medium text-slate-800">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <a href="{{ route('teacher.dashboard') }}"
       class="mx-auto mt-4 block w-fit rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">Back to Dashboard</a>
</x-layouts.app>