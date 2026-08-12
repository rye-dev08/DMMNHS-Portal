<x-layouts.app :title="'My Grade Submissions'">
    <div id="poll-grade-submissions">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">My Grade Submissions</h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.dashboard') }}"
               class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Dashboard</a>
        </div>
    </div>

    {{-- Completion summary --}}
    <div class="mb-5 rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)]">
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <p class="m-0 text-[13px] font-semibold text-[#0a1633]">
                Submission Progress — {{ $school_year }} · Term {{ $term }}
                @if ($teacher?->advisory_class)
                    · {{ $teacher->advisory_class }}
                @endif
            </p>
            <p class="m-0 text-[15px] font-bold text-[#0018f9]">{{ $completion }}%</p>
        </div>
        <div class="h-2.5 w-full overflow-hidden rounded-full bg-[#e5edf8]">
            <div class="h-full rounded-full bg-gradient-to-r from-[#0018f9] to-[#38bdf8] transition-all"
                 style="width: {{ $completion }}%"></div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[12px] font-semibold text-emerald-700">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> {{ $submitted }} Submitted
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-[12px] font-semibold text-amber-700">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> {{ $pending }} Pending
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-[12px] font-semibold text-red-700">
                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> {{ $late }} Late
            </span>
        </div>
    </div>

    {{-- Units --}}
    <div class="grid grid-cols-1 gap-3.5 md:grid-cols-2">
        @forelse ($units as $unit)
            <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] backdrop-blur-sm">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px]
                    {{ $unit->status === 'submitted' ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : ($unit->status === 'late' ? 'bg-gradient-to-r from-red-500 to-amber-400' : 'bg-gradient-to-r from-amber-400 to-yellow-300') }}"></div>

                <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="m-0 text-[15px] font-semibold text-[#0a1633]">{{ $unit->subject_name }}</p>
                        <p class="mt-0.5 m-0 text-[12px] text-slate-500">
                            {{ $unit->school_year }} · Term {{ $unit->term }}
                            @if ($unit->grade_level || $unit->section)
                                · {{ $unit->grade_level ? 'Grade '.$unit->grade_level : '' }}{{ $unit->section ? ' - '.$unit->section : '' }}
                            @endif
                        </p>
                    </div>
                    @if ($unit->status === 'submitted')
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[11.5px] font-semibold text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Submitted
                        </span>
                    @elseif ($unit->status === 'late')
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-[11.5px] font-semibold text-red-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Late
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-[11.5px] font-semibold text-amber-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Pending
                        </span>
                    @endif
                </div>

                <div class="mb-3 h-1.5 w-full overflow-hidden rounded-full bg-[#e5edf8]">
                    <div class="h-full rounded-full
                        {{ $unit->status === 'submitted' ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : ($unit->status === 'late' ? 'bg-gradient-to-r from-red-500 to-amber-400' : 'bg-gradient-to-r from-amber-400 to-yellow-300') }}"
                         style="width: {{ $unit->assigned > 0 ? round($unit->graded / $unit->assigned * 100) : 0 }}%"></div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[12.5px]">
                    <div>
                        <p class="m-0 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Students Graded</p>
                        <p class="m-0 font-medium text-[#0a1633]">{{ $unit->graded }}/{{ $unit->assigned }}</p>
                    </div>
                    <div>
                        <p class="m-0 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Deadline</p>
                        <p class="m-0 font-medium text-[#0a1633]">{{ $unit->deadline?->format('M d, Y') ?? '—' }}</p>
                    </div>
                    @if ($unit->status === 'submitted')
                        <div>
                            <p class="m-0 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Submitted On</p>
                            <p class="m-0 font-medium text-emerald-600">{{ $unit->submission_date?->format('M d, Y') ?? '—' }}</p>
                        </div>
                    @else
                        <div>
                            <p class="m-0 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Last Updated</p>
                            <p class="m-0 font-medium text-[#0a1633]">{{ $unit->last_updated?->format('M d, Y') ?? '—' }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="m-0 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Section</p>
                        <p class="m-0 font-medium text-[#0a1633]">{{ $unit->section ?? '—' }}</p>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('teacher.grades-overview') }}"
                       class="rounded-md border border-[#0018f9]/25 bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#0018f9] no-underline transition hover:bg-[#eef4ff]">Open Grades Overview</a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-[#0018f9]/15 bg-white p-10 text-center shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)] md:col-span-2">
                <p class="m-0 text-[14px] font-medium text-[#0a1633]">No grade submissions found for your advisory yet.</p>
                <p class="mt-1 m-0 text-[12.5px] text-slate-500">Add subjects and submit grades from your Advisory Portal.</p>
            </div>
        @endforelse
    </div>
    </div>
</x-layouts.app>
