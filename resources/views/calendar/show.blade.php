@php
    $role = auth()->user()->role;
    $backUrl = $role === 'teacher' ? route('teacher.dashboard') : route('student.dashboard');
    $categories = \App\Models\AcademicCalendarEvent::CATEGORIES;
@endphp

<x-layouts.app :title="'Academic Calendar'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Academic Calendar</h2>
        </div>
        <a href="{{ $backUrl }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Dashboard</a>
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="m-0 text-[16px] font-bold text-[#0a1633]">{{ $monthLabel }}</p>
            <p class="m-0 text-[13px] text-[#0a1633]/60">School Year <strong>{{ $schoolYear }}</strong> &middot; showing events for the active academic year</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($prevUrl)
                <a href="{{ $prevUrl }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white px-4 py-2 font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">← Prev</a>
            @else
                <span class="cursor-not-allowed rounded-lg border border-[#0018f9]/10 bg-white/50 px-4 py-2 font-semibold text-[#0a1633]/35">← Prev</span>
            @endif
            @if ($nextUrl)
                <a href="{{ $nextUrl }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white px-4 py-2 font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">Next →</a>
            @else
                <span class="cursor-not-allowed rounded-lg border border-[#0018f9]/10 bg-white/50 px-4 py-2 font-semibold text-[#0a1633]/35">Next →</span>
            @endif
        </div>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-2">
        @foreach ($categories as $value => $label)
            <span class="inline-flex items-center gap-1.5 rounded-lg border border-[#0018f9]/10 bg-white/70 px-2.5 py-1 text-[12px] text-[#0a1633]/70">
                <span class="h-2 w-2 rounded-full {{ academic_calendar_category_style($value, 'dot') }}"></span>
                {{ $label }}
            </span>
        @endforeach
    </div>

    <div class="rounded-xl border border-[#0018f9]/15 bg-white/80 p-3 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)]">
        @include('calendar.partials.month-grid', ['grid' => $grid, 'dayEvents' => $dayEvents])
    </div>

    @include('calendar.partials.event-modal')

    <script>
        window.calendarEvents = {!! $eventsJson !!};
    </script>
</x-layouts.app>
