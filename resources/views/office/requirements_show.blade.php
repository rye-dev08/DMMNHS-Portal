<x-layouts.app :title="$requirement->title">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">{{ $requirement->title }}</h2>
        </div>
        <a href="{{ route('office.requirements') }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">← All Requirements</a>
    </div>

    <div class="mb-4 rounded-xl border border-[#0018f9]/15 bg-white p-4 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <p class="m-0 mb-1 text-[14px] font-medium text-[#0a1633]">{{ $requirement->description }}</p>
        <div class="flex flex-wrap gap-x-4 gap-y-1 text-[12.5px] text-[#0a1633]/60">
            <span>Teacher: <span class="font-semibold text-[#0a1633]">{{ $teacherName }}</span></span>
            <span>{{ $requirement->typeLabel() }}</span>
            <span>{{ $requirement->due_date ? 'Due '.$requirement->due_date->format('M d, Y') : 'No due date' }}</span>
            <span>{{ $requirement->submission_required ? 'Submission required' : 'Info only' }}</span>
            @if ($requirement->attachment)
                <a href="{{ route('office.requirements.download', $requirement->id) }}" class="font-semibold text-[#0018f9] no-underline">📎 {{ $requirement->attachment_name }}</a>
            @endif
        </div>
    </div>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-[#0018f9]/15 bg-white p-3 text-center shadow-[0_4px_14px_-8px_rgba(0,24,249,0.2)]">
            <p class="m-0 text-[20px] font-bold text-[#0a1633]">{{ $progress->total }}</p>
            <p class="m-0 text-[12px] font-medium text-[#0a1633]/55">Students</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-center">
            <p class="m-0 text-[20px] font-bold text-amber-700">{{ $progress->pending }}</p>
            <p class="m-0 text-[12px] font-medium text-amber-700/70">Pending</p>
        </div>
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-3 text-center">
            <p class="m-0 text-[20px] font-bold text-sky-700">{{ $progress->submitted }}</p>
            <p class="m-0 text-[12px] font-medium text-sky-700/70">Submitted</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-center">
            <p class="m-0 text-[20px] font-bold text-emerald-700">{{ $progress->approved }}</p>
            <p class="m-0 text-[12px] font-medium text-emerald-700/70">Approved</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[620px] border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Student</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Status</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Submission</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $i => $row)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 font-medium text-[#0a1633]">{{ $row->student_name }}</td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="rounded-full border px-2.5 py-1 text-[12px] font-semibold capitalize {{ App\Models\RequirementSubmission::STATUS_STYLES[$row->status] ?? '' }}">
                                {{ App\Models\RequirementSubmission::STATUS_LABELS[$row->status] ?? $row->status }}
                            </span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            @if ($row->submission)
                                <div class="flex flex-col gap-0.5 text-[12.5px] text-[#0a1633]/75">
                                    @if ($row->submission->response_text)
                                        <span class="line-clamp-2">{{ $row->submission->response_text }}</span>
                                    @endif
                                    @if ($row->submission->attachment)
                                        <span class="font-medium text-[#0a1633]">📎 {{ $row->submission->attachment_name }}</span>
                                    @endif
                                    @if ($row->submission->feedback)
                                        <span class="text-red-600">Feedback: {{ $row->submission->feedback }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400">Not submitted</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-8 text-center text-[#6b7280]">No approved students assigned yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
