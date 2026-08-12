@php
    $filter = request()->query('filter', 'all');
    $filtered = $requirements->filter(function ($r) use ($filter) {
        return match ($filter) {
            'pending' => $r->is_pending,
            'overdue' => $r->is_overdue,
            'submitted' => $r->has_submitted && ! $r->is_approved,
            'needs_revision' => $r->effective_status === App\Models\RequirementSubmission::STATUS_NEEDS_REVISION,
            'approved' => $r->is_approved,
            default => true,
        };
    });

    $counts = [
        'all' => $requirements->count(),
        'pending' => $requirements->where('is_pending', true)->count(),
        'overdue' => $requirements->where('is_overdue', true)->count(),
        'submitted' => $requirements->filter(fn ($r) => $r->has_submitted && ! $r->is_approved)->count(),
        'needs_revision' => $requirements->where('effective_status', App\Models\RequirementSubmission::STATUS_NEEDS_REVISION)->count(),
        'approved' => $requirements->where('is_approved', true)->count(),
    ];
@endphp

<x-layouts.app :title="'Requirements'">
    <div id="poll-dashboard" class="flex flex-col gap-6 lg:gap-7">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Requirements</h2>
        </div>
        <span class="rounded-full border border-[#0018f9]/20 bg-[#eef4ff] px-3 py-1.5 text-[12.5px] font-semibold text-[#0018f9]">{{ $period->label }}</span>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($counts as $key => $count)
            <a href="{{ route('student.requirements', $key === 'all' ? [] : ['filter' => $key]) }}"
               class="rounded-full border px-3 py-1.5 text-[12.5px] font-semibold no-underline transition {{ $filter === $key ? 'border-[#0018f9] bg-[#0018f9] text-white shadow-[0_3px_10px_-3px_rgba(0,24,249,0.6)]' : 'border-[#0018f9]/20 bg-white text-[#0a1633]/70 hover:bg-[#eef4ff]' }}">
                {{ ucwords(str_replace('_', ' ', $key)) }} ({{ $count }})
            </a>
        @endforeach
    </div>

    @forelse ($filtered as $requirement)
        <div class="mb-4 overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-[#dbe4f0] bg-gradient-to-r from-[#f8fbff] to-[#eef4ff] px-4 py-3">
                <div class="min-w-0">
                    <a href="{{ route('student.requirements.show', $requirement->id) }}" class="no-underline">
                        <h3 class="m-0 text-[15px] font-semibold text-[#0a1633] hover:text-[#0018f9]">{{ $requirement->title }}</h3>
                    </a>
                    <p class="mt-0.5 text-[12.5px] text-[#0a1633]/55">
                        {{ $requirement->typeLabel() }} ·
                        {{ $requirement->due_date ? 'Due '.$requirement->due_date->format('M d, Y') : 'No due date' }}
                    </p>
                </div>
                <span class="rounded-full border px-2.5 py-1 text-[12px] font-semibold capitalize {{ App\Models\RequirementSubmission::STATUS_STYLES[$requirement->effective_status] ?? '' }}">
                    {{ App\Models\RequirementSubmission::STATUS_LABELS[$requirement->effective_status] ?? ucfirst($requirement->effective_status) }}
                </span>
            </div>
            <div class="px-4 py-3">
                @if ($requirement->is_overdue && ! $requirement->is_approved)
                    <p class="m-0 mb-2 text-[12.5px] font-semibold text-red-600">⚠ Overdue — please submit as soon as possible.</p>
                @elseif ($requirement->is_due_soon && ! $requirement->is_approved)
                    <p class="m-0 mb-2 text-[12.5px] font-semibold text-amber-600">Due soon — make sure to submit before the deadline.</p>
                @endif
                <p class="m-0 text-[13px] leading-relaxed text-[#0a1633]/75">{{ $requirement->description }}</p>
                <div class="mt-3 flex items-center justify-between gap-3">
                    <a href="{{ route('student.requirements.show', $requirement->id) }}"
                       class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2 text-[13px] font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">
                        {{ $requirement->effective_status === 'approved' ? 'View Submission' : ($requirement->has_submitted ? 'View / Resubmit' : 'Open Requirement') }}
                    </a>
                    @if ($requirement->submission?->feedback)
                        <span class="text-[12px] text-red-600">Feedback: {{ \Illuminate\Support\Str::limit($requirement->submission->feedback, 60) }}</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-[#0018f9]/15 bg-white p-10 text-center shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            <p class="m-0 text-[14px] font-medium text-[#0a1633]">No requirements here.</p>
            <p class="m-0 mt-1 text-[13px] text-[#0a1633]/55">New requirements from your teacher will appear here.</p>
        </div>
    @endforelse
</x-layouts.app>
