<x-layouts.app :title="'Edit Requirement'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Edit Requirement</h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.requirements.show', $requirement->id) }}"
               class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">← Back</a>
        </div>
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

    <form method="POST" action="{{ route('teacher.requirements.update', $requirement->id) }}" enctype="multipart/form-data"
          class="mx-auto max-w-[900px] rounded-2xl border border-[#0018f9]/15 bg-white/80 p-5 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] sm:p-6">
        @csrf
        @method('PUT')

        <div class="mb-3 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-[#0018f9] to-[#0080fe] text-[13px] font-bold text-white">1</span>
            <h3 class="m-0 text-[15px] font-semibold text-[#0a1633]">Details</h3>
        </div>
        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
            <input type="text" name="title" placeholder="Title" required maxlength="200" value="{{ old('title', $requirement->title) }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
            <select name="requirement_type" class="futuristic-select px-2.5 py-1.5 text-[14px]">
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}" {{ old('requirement_type', $requirement->requirement_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <textarea name="description" rows="4" required maxlength="5000" placeholder="Describe what is needed and any instructions."
                      class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15 sm:col-span-2">{{ old('description', $requirement->description) }}</textarea>
            <input type="date" name="due_date" value="{{ old('due_date', $requirement->due_date?->format('Y-m-d')) }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
            <input type="file" name="attachment"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
        </div>
        <p class="mt-1.5 text-[12.5px] text-[#0a1633]/55">Attachment is optional (max 5MB).</p>

        @if ($requirement->attachment)
            <div class="mt-2 flex flex-wrap items-center gap-3 rounded-xl border border-[#0018f9]/15 bg-[#f8fbff] p-3 text-[13px] text-[#0a1633]/75">
                <span>Current file:</span>
                <a href="{{ route('teacher.requirements.download', $requirement->id) }}" class="font-semibold text-[#0018f9] no-underline">{{ $requirement->attachment_name }}</a>
                <label class="flex cursor-pointer items-center gap-1.5">
                    <input type="checkbox" name="remove_attachment" value="1" class="accent-red-600"> Remove file
                </label>
            </div>
        @endif

        <label class="mt-4 flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 transition {{ old('submission_required', $requirement->submission_required) ? 'border-[#0018f9]/60 bg-[#0018f9]/5' : 'border-slate-200 bg-white hover:border-[#0018f9]/30' }}">
            <input type="checkbox" name="submission_required" value="1" class="h-4 w-4 accent-[#0018f9]" {{ old('submission_required', $requirement->submission_required) ? 'checked' : '' }}>
            <span class="text-[13.5px] font-semibold text-[#0a1633]">Students must submit a response / file</span>
            <span class="ml-auto text-[12px] text-slate-500">Uncheck for announcements/info only.</span>
        </label>

        <div class="mt-5 rounded-xl border border-[#0018f9]/15 bg-[#f8fbff] p-3.5 text-[13px] text-[#0a1633]/75">
            Currently applies to <strong>{{ $studentCount }}</strong> approved student(s). New students approved later are added automatically.
        </div>

        <button type="submit" class="mt-5 rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] px-7 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(16,185,129,0.7)] transition hover:brightness-110">
            Save Changes
        </button>
    </form>
</x-layouts.app>
