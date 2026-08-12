<x-layouts.app :title="'Grade Submission Monitor'">
    <div id="poll-grade-submissions">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Teacher Grade Submission Monitor</h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('office.dashboard') }}"
               class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Dashboard</a>
        </div>
    </div>

    {{-- Summary --}}
    <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)]">
            <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $summary->total }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Total Teachers</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-white/80 p-4 shadow-[0_6px_18px_-8px_rgba(16,185,129,0.15)]">
            <p class="m-0 text-2xl font-bold text-emerald-600">{{ $summary->submitted }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Submitted</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-white/80 p-4 shadow-[0_6px_18px_-8px_rgba(245,158,11,0.15)]">
            <p class="m-0 text-2xl font-bold text-amber-600">{{ $summary->pending }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Pending</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-white/80 p-4 shadow-[0_6px_18px_-8px_rgba(220,38,38,0.15)]">
            <p class="m-0 text-2xl font-bold text-red-600">{{ $summary->late }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Late</p>
        </div>
    </div>

    {{-- Completion --}}
    <div class="mb-5 rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)]">
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <p class="m-0 text-[13px] font-semibold text-[#0a1633]">
                Overall Completion — {{ $filters['school_year'] }} · Term {{ $filters['term'] }}
            </p>
            <p class="m-0 text-[15px] font-bold text-[#0018f9]">{{ $summary->completion }}%</p>
        </div>
        <div class="h-2.5 w-full overflow-hidden rounded-full bg-[#e5edf8]">
            <div class="h-full rounded-full bg-gradient-to-r from-[#0018f9] to-[#38bdf8] transition-all"
                 style="width: {{ $summary->completion }}%"></div>
        </div>
        <p class="mt-2 m-0 text-[12px] text-slate-500">
            {{ $summary->submitted }} of {{ $summary->total }} teachers fully submitted
            ({{ $units->count() }} subject units monitored).
        </p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('office.grade-submissions') }}"
          class="mb-5 rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)]">
        <div class="flex flex-wrap items-end gap-2.5">
            <label class="block">
                <span class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">School Year</span>
                <select name="school_year" class="futuristic-select min-w-[130px] px-2.5 py-1.5 text-[14px]">
                    @forelse ($schoolYears as $year)
                        <option value="{{ $year }}" {{ $filters['school_year'] === $year ? 'selected' : '' }}>{{ $year }}</option>
                    @empty
                        <option value="{{ $filters['school_year'] }}">{{ $filters['school_year'] }}</option>
                    @endforelse
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Term</span>
                <select name="term" class="futuristic-select px-2.5 py-1.5 text-[14px]">
                    @foreach ($terms as $termOption)
                        <option value="{{ $termOption }}" {{ $filters['term'] === $termOption ? 'selected' : '' }}>Term {{ $termOption }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Grade Level</span>
                <select name="grade_level" class="futuristic-select px-2.5 py-1.5 text-[14px]">
                    <option value="">All Levels</option>
                    @foreach ($gradeLevels as $level)
                        <option value="{{ $level }}" {{ $filters['grade_level'] === $level ? 'selected' : '' }}>Grade {{ $level }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Section</span>
                <select name="section" class="futuristic-select min-w-[140px] px-2.5 py-1.5 text-[14px]">
                    <option value="">All Sections</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section }}" {{ $filters['section'] === $section ? 'selected' : '' }}>{{ $section }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Teacher</span>
                <select name="teacher" class="futuristic-select min-w-[160px] px-2.5 py-1.5 text-[14px]">
                    <option value="">All Teachers</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->user_id }}" {{ $filters['teacher'] === (int) $teacher->user_id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Subject</span>
                <select name="subject" class="futuristic-select min-w-[150px] px-2.5 py-1.5 text-[14px]">
                    <option value="">All Subjects</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject }}" {{ $filters['subject'] === $subject ? 'selected' : '' }}>{{ $subject }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Status</span>
                <select name="status" class="futuristic-select px-2.5 py-1.5 text-[14px]">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="submitted" {{ $filters['status'] === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="late" {{ $filters['status'] === 'late' ? 'selected' : '' }}>Late</option>
                </select>
            </label>
            <button type="submit"
                    class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">Filter</button>
            @if ($filters['grade_level'] !== 0 || $filters['section'] !== '' || $filters['teacher'] !== 0 || $filters['subject'] !== '' || $filters['status'] !== '')
                <a href="{{ route('office.grade-submissions') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-[14px] text-slate-600 no-underline transition hover:bg-slate-50">Clear</a>
            @endif
        </div>
    </form>

    {{-- Remind all --}}
    @if ($units->where('status', '!=', 'submitted')->count() > 0)
        <form method="POST" action="{{ route('office.grade-submissions.remind-all') }}" class="mb-4">
            @csrf
            <input type="hidden" name="school_year" value="{{ $filters['school_year'] }}">
            <input type="hidden" name="term" value="{{ $filters['term'] }}">
            <input type="hidden" name="grade_level" value="{{ $filters['grade_level'] }}">
            <input type="hidden" name="section" value="{{ $filters['section'] }}">
            <input type="hidden" name="teacher" value="{{ $filters['teacher'] }}">
            <input type="hidden" name="subject" value="{{ $filters['subject'] }}">
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
            <button type="submit"
                    data-confirm="Send reminder emails to all pending teachers under the current filter? This cannot be undone."
                    data-confirm-title="Remind All Teachers"
                    data-confirm-text="Send Reminders"
                    class="rounded-lg bg-gradient-to-r from-[#dc2626] to-[#f59e0b] px-4 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(220,38,38,0.5)] transition hover:brightness-110">
                Remind All Pending Teachers
            </button>
        </form>
    @endif

    {{-- Deadlines configuration --}}
    <div class="mb-5 overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#dbe4f0] bg-gradient-to-r from-[#f8fbff] to-[#eef4ff] px-4 py-3">
            <div class="flex items-center gap-2">
                <span class="inline-block h-4 w-1 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
                <h3 class="m-0 text-[15px] font-semibold text-[#0a1633]">Submission Deadlines — {{ $filters['school_year'] }} · Term {{ $filters['term'] }}</h3>
            </div>
            <p class="m-0 text-[12px] text-slate-500">Status automatically becomes <span class="font-semibold text-red-600">Late</span> once the deadline passes without submission.</p>
        </div>

        <div class="grid gap-4 p-4 lg:grid-cols-2">
            {{-- Add deadline --}}
            <form method="POST" action="{{ route('office.grade-submissions.deadlines.store') }}" class="flex flex-wrap items-end gap-2.5">
                @csrf
                <input type="hidden" name="school_year" value="{{ $filters['school_year'] }}">
                <input type="hidden" name="term" value="{{ $filters['term'] }}">
                <label class="block">
                    <span class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Subject</span>
                    <select name="subject_name" class="futuristic-select min-w-[170px] px-2.5 py-1.5 text-[14px]">
                        <option value="">All Subjects</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject }}" {{ $filters['subject'] === $subject ? 'selected' : '' }}>{{ $subject }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Deadline</span>
                    <input type="date" name="deadline" required
                           class="rounded-lg border border-[#0018f9]/25 bg-white px-2.5 py-1.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/20">
                </label>
                <button type="submit"
                        class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">Add Deadline</button>
            </form>

            {{-- Existing deadlines --}}
            <div>
                @forelse ($deadlines as $deadline)
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-[#0018f9]/15 bg-white/70 px-3 py-2">
                        <div class="min-w-0">
                            <p class="m-0 text-[13.5px] font-semibold text-[#0a1633]">
                                {{ $deadline->isGlobal() ? 'All Subjects' : $deadline->subject_name }}
                            </p>
                            <p class="m-0 text-[12px] text-slate-500">Deadline: {{ $deadline->deadline->format('M d, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('office.grade-submissions.deadlines.update', $deadline->id) }}" class="flex items-center gap-1.5">
                                @csrf
                                @method('PUT')
                                <input type="date" name="deadline" value="{{ $deadline->deadline->toDateString() }}" required
                                       class="rounded-lg border border-[#0018f9]/25 bg-white px-2 py-1 text-[13px] shadow-sm outline-none">
                                <button type="submit" class="rounded-md border border-[#0018f9]/25 bg-white px-2.5 py-1 text-[12px] font-semibold text-[#0018f9] transition hover:bg-[#eef4ff]">Update</button>
                            </form>
                            <form method="POST" action="{{ route('office.grade-submissions.deadlines.destroy', $deadline->id) }}" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" data-confirm="Remove this submission deadline?" data-confirm-title="Remove Deadline" data-confirm-text="Remove" class="rounded-md border border-red-200 bg-white px-2.5 py-1 text-[12px] font-semibold text-red-600 transition hover:bg-red-50">Remove</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="m-0 text-[13px] text-slate-500">No deadlines set for this grading period yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Units table --}}
    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#dbe4f0] bg-gradient-to-r from-[#f8fbff] to-[#eef4ff] px-4 py-3">
            <div class="flex items-center gap-2">
                <span class="inline-block h-4 w-1 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
                <h3 class="m-0 text-[15px] font-semibold text-[#0a1633]">Submission Details</h3>
            </div>
            <span class="text-[12.5px] text-slate-500">{{ $units->count() }} subject unit(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-[13px]">
                <thead>
                    <tr class="border-b border-[#dbe4f0] bg-[#f4f8ff] text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-2.5 font-semibold">Teacher</th>
                        <th class="px-4 py-2.5 font-semibold">Subject</th>
                        <th class="px-4 py-2.5 font-semibold">Level</th>
                        <th class="px-4 py-2.5 font-semibold">Section</th>
                        <th class="px-4 py-2.5 font-semibold">Status</th>
                        <th class="px-4 py-2.5 font-semibold">Submission Date</th>
                        <th class="px-4 py-2.5 font-semibold">Deadline</th>
                        <th class="px-4 py-2.5 font-semibold">Last Updated</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($units as $unit)
                        <tr class="border-b border-[#eef3fa] transition hover:bg-[#f8fbff]">
                            <td class="px-4 py-3">
                                <p class="m-0 font-semibold text-[#0a1633]">{{ $unit->teacher_name }}</p>
                                <p class="m-0 text-[11.5px] text-slate-500">{{ $unit->graded }}/{{ $unit->assigned }} students graded</p>
                            </td>
                            <td class="px-4 py-3 font-medium text-[#0a1633]">{{ $unit->subject_name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $unit->grade_level ? 'Grade '.$unit->grade_level : '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $unit->section ?? '—' }}</td>
                            <td class="px-4 py-3">
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
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $unit->submission_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $unit->deadline?->format('M d, Y') ?? '—' }}
                                @if ($unit->status === 'late' && $unit->deadline)
                                    <span class="ml-1 text-[11px] font-semibold text-red-600">(overdue)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $unit->last_updated?->format('M d, Y h:i A') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($unit->status !== 'submitted')
                                    <form method="POST" action="{{ route('office.grade-submissions.remind') }}">
                                        @csrf
                                        <input type="hidden" name="teacher_user_id" value="{{ $unit->teacher_user_id }}">
                                        <input type="hidden" name="subject_name" value="{{ $unit->subject_name }}">
                                        <input type="hidden" name="term" value="{{ $unit->term }}">
                                        <input type="hidden" name="school_year" value="{{ $unit->school_year }}">
                                        <button type="submit"
                                                class="rounded-md border border-[#0018f9]/25 bg-white px-2.5 py-1 text-[12px] font-semibold text-[#0018f9] transition hover:bg-[#eef4ff]">Remind</button>
                                    </form>
                                @else
                                    <span class="text-[12px] text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center">
                                <p class="m-0 text-[14px] font-medium text-[#0a1633]">No grade submissions match the current filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
</x-layouts.app>
