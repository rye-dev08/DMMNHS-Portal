<x-layouts.app :title="'Enrollment Settings'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Enrollment &amp; Semester Management</h2>
        </div>
        <a href="{{ route('admin.dashboard') }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Dashboard</a>
    </div>

    @php
        $currentSemester = $settings->current_semester ?? 1;
        $currentYear = $settings->current_school_year ?? 'N/A';
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
            <p class="m-0 text-lg font-bold text-[#0a1633]">Sem {{ (int) $currentSemester }} &middot; {{ $currentYear }}</p>
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <form method="POST" action="{{ route('admin.enrollment-settings.end-semester') }}" class="m-0">
            @csrf
            <button type="submit" name="end_semester"
                    onclick="return confirm('Reset to new semester?')"
                    class="flex w-full items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-[#ff9800] to-[#f57c00] p-5 text-[18px] font-semibold text-white shadow-[0_10px_26px_-10px_rgba(255,152,0,0.9)] transition hover:brightness-110 active:scale-[0.99]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                New Semester
            </button>
        </form>
        <form method="POST" action="{{ route('admin.enrollment-settings.end-school-year') }}" class="m-0">
            @csrf
            <button type="submit" name="end_school_year"
                    onclick="return confirm('Reset new school year?')"
                    class="flex w-full items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-[#f44336] to-[#d32f2f] p-5 text-[18px] font-semibold text-white shadow-[0_10px_26px_-10px_rgba(244,67,54,0.9)] transition hover:brightness-110 active:scale-[0.99]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                </svg>
                New School Year
            </button>
        </form>
    </div>

    <h3 class="mb-3 mt-2 flex items-center gap-2 text-[15px] font-semibold text-[#0a1633]">
        <span class="inline-block h-4 w-1 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
        Teachers Advisory
    </h3>
    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <table class="w-full border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Advisory Class</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teachers as $i => $t)
                    <form method="POST" action="{{ route('admin.enrollment-settings.advisory') }}"
                          id="advisory-form-{{ $t->user_id }}" class="hidden">
                        @csrf
                        <input type="hidden" name="teacher_user_id" value="{{ $t->user_id }}">
                    </form>
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 font-medium text-[#0a1633]">{{ $t->name }}</td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <input type="text" form="advisory-form-{{ $t->user_id }}" name="advisory_class"
                                   value="{{ $t->advisory_class }}" placeholder="e.g. Grade 11-A" size="15"
                                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <button type="submit" form="advisory-form-{{ $t->user_id }}"
                                    class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2 font-semibold text-white shadow-[0_4px_12px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">Save</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-8 text-center text-[#6b7280]">No active teachers.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>