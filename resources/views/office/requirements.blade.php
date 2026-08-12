<x-layouts.app :title="'Requirements'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Requirement &amp; Submission Tracker</h2>
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
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Active Requirements</p>
        </div>
        <div class="rounded-xl border border-sky-200 bg-white/80 p-4 shadow-[0_6px_18px_-8px_rgba(2,132,199,0.15)]">
            <p class="m-0 text-2xl font-bold text-sky-600">{{ $summary->pendingReview }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Awaiting Review</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-white/80 p-4 shadow-[0_6px_18px_-8px_rgba(220,38,38,0.15)]">
            <p class="m-0 text-2xl font-bold text-red-600">{{ $summary->needsRevision }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Needs Revision</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-white/80 p-4 shadow-[0_6px_18px_-8px_rgba(16,185,129,0.15)]">
            <p class="m-0 text-2xl font-bold text-emerald-600">{{ $summary->approved }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Approved</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('office.requirements') }}" class="mb-4 flex flex-wrap items-center gap-2.5">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search title or teacher"
               class="min-w-[220px] flex-1 rounded-lg border border-[#0018f9]/25 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/20">
        <select name="type" class="futuristic-select px-2.5 py-1.5 text-[14px]">
            <option value="">All Types</option>
            @foreach ($types as $key => $label)
                <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">Filter</button>
        @if ($q !== '' || $type !== '')
            <a href="{{ route('office.requirements') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-[14px] text-slate-600 no-underline transition hover:bg-slate-50">Clear</a>
        @endif
    </form>

    @forelse ($requirements as $requirement)
        <div class="mb-4 overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-[#dbe4f0] bg-gradient-to-r from-[#f8fbff] to-[#eef4ff] px-4 py-3">
                <div class="min-w-0">
                    <a href="{{ route('office.requirements.show', $requirement->id) }}" class="no-underline">
                        <h3 class="m-0 text-[15px] font-semibold text-[#0a1633] hover:text-[#0018f9]">{{ $requirement->title }}</h3>
                    </a>
                    <p class="mt-0.5 text-[12.5px] text-[#0a1633]/55">
                        {{ $requirement->typeLabel() }} ·
                        {{ $requirement->due_date ? 'Due '.$requirement->due_date->format('M d, Y') : 'No due date' }} ·
                        {{ $requirement->submission_required ? 'Submission required' : 'Info only' }} ·
                        Teacher: <span class="font-semibold text-[#0a1633]">{{ $requirement->teacher_name }}</span>
                    </p>
                </div>
                <a href="{{ route('office.requirements.show', $requirement->id) }}"
                   class="shrink-0 rounded-md border border-[#0018f9]/25 bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#0018f9] no-underline transition hover:bg-[#eef4ff]">View</a>
            </div>

            <div class="px-4 py-3">
                <div class="mb-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[12.5px] text-[#0a1633]/70">
                    <span class="font-medium text-[#0a1633]">{{ $requirement->progress->percent }}% submitted</span>
                    <span>{{ $requirement->progress->submitted }}/{{ $requirement->progress->total }} students started</span>
                    @if ($requirement->progress->needs_revision > 0)
                        <span class="font-semibold text-red-600">{{ $requirement->progress->needs_revision }} need revision</span>
                    @endif
                    @if ($requirement->progress->approved > 0)
                        <span class="font-semibold text-emerald-600">{{ $requirement->progress->approved }} approved</span>
                    @endif
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-[#e5edf8]">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#0018f9] to-[#38bdf8]"
                         style="width: {{ $requirement->progress->percent }}%"></div>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-[#0018f9]/15 bg-white p-10 text-center shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            <p class="m-0 text-[14px] font-medium text-[#0a1633]">No requirements found.</p>
        </div>
    @endforelse

    @if ($requirements->hasPages())
        <div class="mt-4">{{ $requirements->links() }}</div>
    @endif
</x-layouts.app>
