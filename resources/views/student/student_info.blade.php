@php
    $fields = [
        'Full Name' => $student->name ?? 'N/A',
        'Username' => $student->username ?? 'N/A',
        'Email' => $student->email ?? 'N/A',
        'Account Status' => $student->status ?? 'N/A',
        'Sex' => $student->sex ?? 'N/A',
        'Birthday' => $student->birthday ?? 'N/A',
        'Age' => (string) ($student->age ?? 'N/A'),
        'Grade Level' => (string) ($student->grade_level ?? 'N/A'),
        'Adviser' => $advisory->teacher_name ?? 'N/A',
        'Advisory Class' => $advisory->advisory_class ?? 'N/A',
        'Max Subjects Allowed' => (string) ($advisory->max_subjects ?? 'N/A'),
    ];
@endphp

<x-layouts.app :title="'Student Info'">
    <h2 class="text-center">Student Info</h2>

    @if (! $hasProfile)
        <p class="text-red-600">Your student profile is not yet complete. Contact admin to complete your personal info.</p>
    @endif

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

    <div class="mx-auto mt-4 flex w-fit items-center gap-3">
        <a href="{{ route('student.info.edit') }}"
           class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">Edit Info</a>
        <a href="{{ route('student.dashboard') }}"
           class="rounded-lg border border-[#0018f9]/25 bg-white px-4 py-2 font-semibold text-[#0a1633] no-underline shadow-sm transition hover:bg-[#eaf3ff]">Back to Dashboard</a>
    </div>
</x-layouts.app>