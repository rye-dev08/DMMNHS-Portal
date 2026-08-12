<x-layouts.app :title="$requirement->title">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">{{ $requirement->title }}</h2>
        </div>
        <a href="{{ route('student.requirements') }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">← All Requirements</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-[13px] text-red-700">
            <ul class="m-0 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4 rounded-xl border border-[#0018f9]/15 bg-white p-4 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-[12.5px] text-[#0a1633]/60">
                <span>{{ $requirement->typeLabel() }}</span>
                <span>{{ $requirement->due_date ? 'Due '.$requirement->due_date->format('M d, Y') : 'No due date' }}</span>
                <span>{{ $requirement->section ? 'Section: '.$requirement->section : '' }}</span>
            </div>
            <span class="rounded-full border px-2.5 py-1 text-[12px] font-semibold capitalize {{ App\Models\RequirementSubmission::STATUS_STYLES[$effective_status] ?? '' }}">
                {{ App\Models\RequirementSubmission::STATUS_LABELS[$effective_status] ?? ucfirst($effective_status) }}
            </span>
        </div>
        <p class="m-0 text-[14px] leading-relaxed text-[#0a1633]/80">{{ $requirement->description }}</p>
        @if ($requirement->attachment)
            <a href="{{ route('student.requirements.download', $requirement->id) }}"
               class="mt-3 inline-block rounded-md border border-[#0018f9]/25 bg-[#eef4ff] px-3 py-1.5 text-[13px] font-semibold text-[#0018f9] no-underline transition hover:bg-[#dbe7ff]">📎 {{ $requirement->attachment_name }}</a>
        @endif
    </div>

    @if ($is_overdue && ! ($submission && $submission->isApproved()))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-[13px] font-semibold text-red-700">⚠ This requirement is overdue.</div>
    @endif

    @if ($submission)
        <div class="mb-4 rounded-xl border border-[#0018f9]/15 bg-white p-4 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            <h3 class="mb-2 m-0 flex items-center gap-2 text-[15px] font-semibold text-[#0a1633]">
                <span class="inline-block h-4 w-1 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
                Your Submission
            </h3>
            @if ($submission->response_text)
                <p class="m-0 mb-2 whitespace-pre-wrap text-[13.5px] text-[#0a1633]/80">{{ $submission->response_text }}</p>
            @endif
            @if ($submission->attachment)
                <p class="m-0 text-[13px]">
                    <a href="{{ route('student.requirements.submission-download', $requirement->id) }}" class="font-semibold text-[#0018f9] no-underline">{{ $submission->attachment_name }}</a>
                </p>
            @endif
            <p class="m-0 mt-2 text-[12px] text-[#0a1633]/50">Submitted {{ $submission->submitted_at?->format('M d, Y g:i A') }}</p>
            @if ($submission->feedback)
                <p class="mt-2 rounded-lg border border-red-200 bg-red-50 p-2.5 text-[12.5px] text-red-700">Teacher feedback: {{ $submission->feedback }}</p>
            @endif
        </div>
    @endif

    @if ($requirement->submission_required && $can_submit)
        <div class="rounded-xl border border-[#0018f9]/15 bg-white p-4 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            <h3 class="mb-2 m-0 flex items-center gap-2 text-[15px] font-semibold text-[#0a1633]">
                <span class="inline-block h-4 w-1 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
                {{ $submission ? ($submission->isNeedsRevision() ? 'Resubmit' : 'Update Submission') : 'Submit Requirement' }}
            </h3>
            <form method="POST" action="{{ route('student.requirements.submit', $requirement->id) }}" enctype="multipart/form-data">
                @csrf
                <textarea name="response_text" rows="4" maxlength="20000" placeholder="Type your response here (optional if you attach a file)."
                          class="mb-2.5 w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">{{ old('response_text') }}</textarea>
                <div class="mb-2.5">
                    <input type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip"
                           class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
                    @error('attachment')
                        <p class="m-0 mt-1 text-[12px] font-medium text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="m-0 mt-1 text-[12px] text-[#0a1633]/50">Optional attachment (max 10MB).</p>
                </div>
                <button type="submit"
                        class="rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] px-6 py-2.5 text-[14px] font-semibold text-white shadow-[0_4px_14px_-4px_rgba(16,185,129,0.7)] transition hover:brightness-110">
                    {{ $submission ? 'Resubmit' : 'Submit' }}
                </button>
            </form>
        </div>
    @elseif ($requirement->submission_required && ! $can_submit)
        <div class="rounded-xl border border-[#0018f9]/15 bg-[#f8fbff] p-4 text-[13px] text-[#0a1633]/70 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            Your submission is currently being reviewed. You will be notified once your teacher approves it or requests changes.
        </div>
    @else
        <div class="rounded-xl border border-[#0018f9]/15 bg-[#f8fbff] p-4 text-[13px] text-[#0a1633]/70 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            This is an informational requirement. No submission is needed — just read the details above.
        </div>
    @endif
</x-layouts.app>
