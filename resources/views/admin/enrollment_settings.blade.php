<x-layouts.app :title="'Enrollment Settings'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Enrollment &amp; Term Management</h2>
        </div>
    </div>

    @php
        $period = $settings->period();
    @endphp

    {{-- Current period card --}}
    <div class="mb-5 flex items-center gap-4 rounded-2xl border border-[#0018f9]/15 bg-gradient-to-r from-[#0a1633]/5 to-[#164aa8]/10 p-5 shadow-[0_6px_20px_-10px_rgba(0,24,249,0.25)]">
        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-[#0018f9] to-[#0080fe] text-white shadow-[0_6px_16px_-6px_rgba(0,24,249,0.7)]">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
        </span>
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-widest text-[#0018f9]">Current Period</p>
            <p class="m-0 text-lg font-bold text-[#0a1633]">{{ $period->label }}</p>
            @if ($period->phase === 'enrollment')
                <p class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-[12px] font-semibold text-amber-800">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-600"></span>
                    Enrollment ongoing
                </p>
            @elseif ($period->phase === 'closed')
                <p class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[12px] font-semibold text-emerald-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                    Enrollment closed &middot; ready for new school year
                </p>
            @elseif ($period->term === 3)
                <p class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-sky-100 px-2.5 py-0.5 text-[12px] font-semibold text-sky-800">
                    Final term &middot; end school year when ready
                </p>
            @endif
        </div>
    </div>

    {{-- Lifecycle controls --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        @php
            $actions = [
                [
                    'label' => 'New Term',
                    'desc' => 'Archives current subjects & grades to history, resets enrollments.',
                    'route' => 'admin.enrollment-settings.end-term',
                    'allowed' => $period->can_new_term,
                    'confirm' => 'Start a new term? This archives current subjects and grades to history, then resets enrollments for the next term.',
                    'gradient' => 'from-[#ff9800] to-[#f57c00]',
                    'shadow' => 'rgba(255,152,0,0.9)',
                    'disabled_note' => 'Only during Term 1 or 2',
                    'icon' => 'plus',
                ],
                [
                    'label' => 'End School Year',
                    'desc' => 'Archives year, promotes students, opens enrollment phase.',
                    'route' => 'admin.enrollment-settings.end-school-year',
                    'allowed' => $period->can_end_school_year,
                    'confirm' => 'End the current school year? Students are promoted, subjects and grades are archived, and the enrollment phase opens.',
                    'gradient' => 'from-[#f44336] to-[#d32f2f]',
                    'shadow' => 'rgba(244,67,54,0.9)',
                    'disabled_note' => 'Only during Term 3',
                    'icon' => 'flag',
                ],
                [
                    'label' => 'End Enrollment Phase',
                    'desc' => 'Closes enrollment for the next school year.',
                    'route' => 'admin.enrollment-settings.end-enrollment-phase',
                    'allowed' => $period->can_end_enrollment_phase,
                    'confirm' => 'Close the enrollment phase? Students can no longer send enrollment requests until the new school year begins.',
                    'gradient' => 'from-[#7c3aed] to-[#6d28d9]',
                    'shadow' => 'rgba(124,58,237,0.9)',
                    'disabled_note' => 'Only while enrollment is ongoing',
                    'icon' => 'lock',
                ],
                [
                    'label' => 'New School Year',
                    'desc' => 'Advances the school year and resets to Term 1.',
                    'route' => 'admin.enrollment-settings.new-school-year',
                    'allowed' => $period->can_new_school_year,
                    'confirm' => 'Start the new school year? The school year advances and the system resets to Term 1.',
                    'gradient' => 'from-[#0a1633] to-[#164aa8]',
                    'shadow' => 'rgba(10,22,51,0.9)',
                    'disabled_note' => 'Only after the enrollment phase is closed',
                    'icon' => 'calendar',
                ],
            ];
        @endphp

        @foreach ($actions as $action)
            @php
                $disabled = ! $action['allowed'];
                $icon = $action['icon'];
            @endphp
            <form method="POST" action="{{ route($action['route']) }}" class="m-0">
                @csrf
                <button type="submit" @if (! $disabled) data-confirm="{{ $action['confirm'] }}" @endif
                        @if ($disabled) disabled @endif
                        class="flex w-full flex-col items-center justify-center gap-2 rounded-2xl bg-gradient-to-r {{ $action['gradient'] }} p-5 text-[17px] font-semibold text-white shadow-[0_10px_26px_-10px_{{ $action['shadow'] }}] transition hover:brightness-110 active:scale-[0.99] {{ $disabled ? 'cursor-not-allowed opacity-40 saturate-0 hover:brightness-100' : '' }}">
                    <span class="flex items-center gap-2">
                        @if ($icon === 'plus')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        @elseif ($icon === 'flag')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.286M3 15V4.286" />
                            </svg>
                        @elseif ($icon === 'lock')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        @endif
                        {{ $action['label'] }}
                    </span>
                    <span class="text-[12px] font-normal leading-snug text-white/80">
                        {{ $disabled ? $action['disabled_note'] : $action['desc'] }}
                    </span>
                </button>
            </form>
        @endforeach
    </div>

    {{-- Info banner --}}
    <div class="flex items-start gap-3 rounded-xl border border-[#0018f9]/10 bg-[#eaf3ff]/70 p-4 text-[13px] text-[#0a1633]/80">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="mt-0.5 h-5 w-5 shrink-0 text-[#0018f9]">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
        <p class="m-0">
            <strong>New Term</strong> (Term 1&rarr;2, 2&rarr;3) archives subjects &amp; grades to history and resets enrollments.
            <strong>End School Year</strong> (Term 3 only) archives the year, promotes students, and opens the enrollment phase.
            During the <strong>enrollment phase</strong> students enroll for the next school year; <strong>End Enrollment Phase</strong> closes it.
            <strong>New School Year</strong> advances the year and resets to Term 1.
        </p>
    </div>
</x-layouts.app>