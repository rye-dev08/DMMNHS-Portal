<x-layouts.app :title="$requirement->title">
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="m-0 list-inside list-disc text-[13px] text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">{{ $requirement->title }}</h2>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('teacher.requirements.edit', $requirement->id) }}"
               class="rounded-md border border-[#0018f9]/25 bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#0018f9] no-underline transition hover:bg-[#eef4ff]">Edit</a>
            @if ($canBump)
                <form method="POST" action="{{ route('teacher.requirements.bump', $requirement->id) }}" class="m-0">
                    @csrf
                    <button type="submit"
                            class="rounded-md bg-gradient-to-r from-[#8b5cf6] to-[#7c3aed] px-3 py-1.5 text-[12.5px] font-semibold text-white shadow-[0_3px_10px_-3px_rgba(139,92,246,0.7)] transition hover:brightness-110">Bump All Reminders</button>
                </form>
            @else
                <span class="rounded-md bg-[#f4f4f5] px-3 py-1.5 text-[12.5px] font-medium text-zinc-500">{{ $bumpAvailableAt }}</span>
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

    <div class="mb-4 rounded-xl border border-[#0018f9]/15 bg-white p-4 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <p class="m-0 mb-1 text-[14px] font-medium text-[#0a1633]">{{ $requirement->description }}</p>
        <div class="flex flex-wrap gap-x-4 gap-y-1 text-[12.5px] text-[#0a1633]/60">
            <span>{{ $requirement->typeLabel() }}</span>
            <span>{{ $requirement->due_date ? 'Due '.$requirement->due_date->format('M d, Y') : 'No due date' }}</span>
            <span>{{ $requirement->submission_required ? 'Submission required' : 'Info only' }}</span>
            @if ($requirement->attachment)
                <a href="{{ route('teacher.requirements.download', $requirement->id) }}" class="font-semibold text-[#0018f9] no-underline">📎 {{ $requirement->attachment_name }}</a>
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
        <table class="w-full min-w-[550px] border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Student</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Status</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Submission</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Action</th>
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
                                        <a href="{{ route('teacher.submissions.download', $row->submission->id) }}" class="font-semibold text-[#0018f9] no-underline">{{ $row->submission->attachment_name }}</a>
                                    @endif
                                    @if ($row->submission->feedback)
                                        <span class="text-red-600">Feedback: {{ $row->submission->feedback }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400">Not submitted</span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            @if ($row->submission === null)
                                <form method="POST" action="{{ route('teacher.requirements.remind', [$requirement->id, $row->student_id]) }}" class="m-0">
                                    @csrf
                                    <button type="submit"
                                            class="rounded-md border border-[#8b5cf6]/40 bg-[#8b5cf6]/10 px-3 py-1.5 text-[12px] font-semibold text-[#7c3aed] transition hover:bg-[#8b5cf6]/20">Remind</button>
                                </form>
                            @elseif ($row->submission->isApproved())
                                <span class="text-[12.5px] text-emerald-600">Approved</span>
                            @else
                                <div class="flex flex-wrap items-center gap-2">
                                    <form method="POST" action="{{ route('teacher.submissions.approve', $row->submission->id) }}" class="m-0">
                                        @csrf
                                        <button type="submit"
                                                data-confirm="Approve this submission? The student will be notified."
                                                data-confirm-title="Approve Submission"
                                                data-confirm-text="Approve"
                                                class="rounded-md bg-gradient-to-r from-[#10b981] to-[#059669] px-3 py-1.5 text-[12px] font-semibold text-white shadow-[0_3px_10px_-3px_rgba(16,185,129,0.7)] transition hover:brightness-110">Approve</button>
                                    </form>
                                    <button type="button" data-feedback-open data-student="{{ $row->student_name }}"
                                            data-url="{{ route('teacher.submissions.revision', $row->submission->id) }}"
                                            class="rounded-md border border-[#b91c1c]/40 bg-[#dc2626]/10 px-3 py-1.5 text-[12px] font-semibold text-[#b91c1c] transition hover:bg-[#dc2626]/20">Request Revision</button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-[#6b7280]">No approved students assigned yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Revision modal --}}
    <div id="revision-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-xl">
            <h3 class="m-0 mb-1 text-[16px] font-semibold text-[#0a1633]">Request Revision</h3>
            <p id="revision-student" class="mb-3 text-[13px] text-[#0a1633]/60"></p>
            <form id="revision-form" method="POST" action="">
                @csrf
                <textarea id="revision-feedback" name="feedback" rows="4" required maxlength="2000" placeholder="Explain what needs to be fixed..."
                          class="mb-3 w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]"></textarea>
                <p id="revision-error" class="mb-2 hidden text-[12.5px] font-medium text-red-600"></p>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" data-feedback-close class="rounded-md border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-600 transition hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="rounded-md bg-gradient-to-r from-[#ef4444] to-[#dc2626] px-4 py-2 text-[13px] font-semibold text-white shadow-[0_3px_10px_-3px_rgba(239,68,68,0.7)] transition hover:brightness-110">Send Feedback</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('revision-modal');
            var form = document.getElementById('revision-form');
            var student = document.getElementById('revision-student');
            var feedback = document.getElementById('revision-feedback');
            var errorBox = document.getElementById('revision-error');

            document.querySelectorAll('[data-feedback-open]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    form.action = btn.dataset.url;
                    student.textContent = btn.dataset.student;
                    feedback.value = '';
                    errorBox.classList.add('hidden');
                    errorBox.textContent = '';
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });
            });

            document.querySelectorAll('[data-feedback-close]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });

            form.addEventListener('submit', function (e) {
                if (!feedback.value.trim()) {
                    e.preventDefault();
                    errorBox.textContent = 'Feedback is required.';
                    errorBox.classList.remove('hidden');
                    feedback.focus();
                    return;
                }
                errorBox.classList.add('hidden');
                var btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    setButtonLoading(btn, true);
                }
            });
        });
    </script>
</x-layouts.app>
