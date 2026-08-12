<x-layouts.app :title="'Requirements'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Requirements</h2>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full border border-[#0018f9]/20 bg-[#eef4ff] px-3 py-1.5 text-[12.5px] font-semibold text-[#0018f9]">{{ $period->label }}</span>
            <a href="{{ route('teacher.requirements.create') }}"
               class="rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(16,185,129,0.6)] transition hover:brightness-110">+ New Requirement</a>
        </div>
    </div>

    @forelse ($requirements as $requirement)
        <div class="mb-4 overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-[#dbe4f0] bg-gradient-to-r from-[#f8fbff] to-[#eef4ff] px-4 py-3">
                <div class="min-w-0">
                    <a href="{{ route('teacher.requirements.show', $requirement->id) }}" class="no-underline">
                        <h3 class="m-0 text-[15px] font-semibold text-[#0a1633] hover:text-[#0018f9]">{{ $requirement->title }}</h3>
                    </a>
                    <p class="mt-0.5 text-[12.5px] text-[#0a1633]/55">
                        {{ $requirement->typeLabel() }} ·
                        {{ $requirement->due_date ? 'Due '.$requirement->due_date->format('M d, Y') : 'No due date' }} ·
                        {{ $requirement->submission_required ? 'Submission required' : 'Info only' }}
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('teacher.requirements.edit', $requirement->id) }}"
                       class="rounded-md border border-[#0018f9]/25 bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#0018f9] no-underline transition hover:bg-[#eef4ff]">Edit</a>
                    @if ($requirement->can_bump)
                        <form method="POST" action="{{ route('teacher.requirements.bump', $requirement->id) }}" class="m-0">
                            @csrf
                            <button type="submit"
                                    class="rounded-md bg-gradient-to-r from-[#8b5cf6] to-[#7c3aed] px-3 py-1.5 text-[12.5px] font-semibold text-white shadow-[0_3px_10px_-3px_rgba(139,92,246,0.7)] transition hover:brightness-110">Bump Reminders</button>
                        </form>
                    @else
                        <span class="rounded-md bg-[#f4f4f5] px-3 py-1.5 text-[12.5px] font-medium text-zinc-500">{{ $requirement->bump_available_at }}</span>
                    @endif
                    <form method="POST" action="{{ route('teacher.requirements.destroy', $requirement->id) }}" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                data-confirm="Delete this requirement and all its submissions? This cannot be undone."
                                data-confirm-title="Delete Requirement"
                                data-confirm-text="Delete"
                                class="rounded-md border border-[#b91c1c] bg-[#dc2626] px-3 py-1.5 text-[12.5px] font-semibold text-white transition hover:bg-[#b91c1c]">Delete</button>
                    </form>
                </div>
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
                    <a href="{{ route('teacher.requirements.show', $requirement->id) }}" class="ml-auto font-semibold text-[#0018f9] no-underline">View progress →</a>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-[#e5edf8]">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#0018f9] to-[#38bdf8]"
                         style="width: {{ $requirement->progress->percent }}%"></div>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-[#0018f9]/15 bg-white p-10 text-center shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            <p class="m-0 text-[14px] font-medium text-[#0a1633]">No requirements yet for {{ $period->label }}.</p>
            <a href="{{ route('teacher.requirements.create') }}" class="mt-3 inline-block font-semibold text-[#0018f9] no-underline">Create your first requirement →</a>
        </div>
    @endforelse

    @if ($requirements->hasPages())
        <div class="mt-4">{{ $requirements->links() }}</div>
    @endif
</x-layouts.app>
