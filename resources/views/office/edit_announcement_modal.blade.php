@php
    $modalId = $modalId ?? 'announcement-modal';
    $isEdit = isset($announcement) && $announcement !== null;
    $action = $isEdit ? route('office.announcements.update', $announcement->id) : route('office.announcements.store');
    $years = $years ?? [];
    $sections = $sections ?? [];
    $students = $students ?? collect();
    $teachers = $teachers ?? collect();
    $settings = \App\Models\Setting::find(1);
    $defaultYear = (string) ($settings->current_school_year ?? date('Y') . '-' . (date('Y') + 1));
    $defaultTerm = (int) ($settings->current_term ?? 1);
    $audiences = $isEdit ? ($announcement->audiences ?? collect()) : collect();

    $selectedGrades = array_map('strval', old('grade_levels', $isEdit ? $audiences->where('target_type', 'grade_level')->pluck('target_value')->all() : []));
    $selectedSections = array_map('strval', old('sections', $isEdit ? $audiences->where('target_type', 'section')->pluck('target_value')->all() : []));
    $selectedStudents = array_map('strval', old('students', $isEdit ? $audiences->where('target_type', 'student')->pluck('target_value')->all() : []));
    $selectedTeachers = array_map('strval', old('teachers', $isEdit ? $audiences->where('target_type', 'teacher')->pluck('target_value')->all() : []));

    $inputClass = 'rounded-lg border border-[#0018f9]/20 bg-white px-4 py-2.5 text-[14px] text-[#0a1633] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15';
    $labelClass = 'text-[13px] font-semibold text-[#0a1633]';
    $oldPublishDate = $isEdit && $announcement->publish_date ? $announcement->publish_date->format('Y-m-d') : '';
    $oldExpirationDate = $isEdit && $announcement->expiration_date ? $announcement->expiration_date->format('Y-m-d') : '';
@endphp

<dialog id="{{ $modalId }}" class="modal-modal announcement-form-modal">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="grid gap-4 p-6 max-w-2xl">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="grid gap-1.5">
            <label for="title-{{ $modalId }}" class="{{ $labelClass }}">Title *</label>
            <input id="title-{{ $modalId }}" name="title" type="text" required maxlength="200"
                   value="{{ old('title', $announcement->title ?? '') }}"
                   placeholder="e.g. Parent-Teacher Conference on Friday"
                   class="{{ $inputClass }}">
            @error('title')
                <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid gap-1.5">
            <label class="{{ $labelClass }}">Short Summary</label>
            <input type="text" name="short_summary" maxlength="255" value="{{ old('short_summary', $announcement->short_summary ?? '') }}"
                   placeholder="One-liner shown in the announcement list" class="{{ $inputClass }}">
            @error('short_summary')
                <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid gap-1.5">
            <label class="{{ $labelClass }}">Full Details</label>
            <textarea name="content" rows="4" placeholder="Write the full announcement content here..."
                      class="{{ $inputClass }}">{{ old('content', $announcement->content ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Priority *</label>
                <select name="priority" required class="futuristic-select px-4 py-2.5">
                    @foreach (\App\Models\Announcement::PRIORITIES as $key => $label)
                        <option value="{{ $key }}" {{ old('priority', $announcement->priority ?? 'normal') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Status *</label>
                <select name="status" required class="futuristic-select px-4 py-2.5">
                    <option value="published" {{ old('status', $announcement->status ?? 'published') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="unpublished" {{ old('status', $announcement->status ?? 'published') === 'unpublished' ? 'selected' : '' }}>Unpublished (Draft)</option>
                </select>
            </div>
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Audience *</label>
                <select name="target_role" required class="futuristic-select px-4 py-2.5" onchange="toggleAnnouncementAudience(this)">
                    @foreach (\App\Models\Announcement::TARGET_ROLES as $key => $label)
                        <option value="{{ $key }}" {{ old('target_role', $announcement->target_role ?? 'all') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="announcement-students-group hidden grid gap-3 rounded-xl border border-[#0018f9]/15 bg-[#f4f8ff]/70 p-4">
            <p class="m-0 text-[12px] font-semibold text-[#0018f9]">
                Refine student audience — leave blank to reach all students. Matching is additive (grade OR section OR student).
            </p>

            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Grade Levels</label>
                <div class="flex flex-wrap gap-2">
                    @for ($g = 7; $g <= 12; $g++)
                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-[#0018f9]/15 bg-white px-2.5 py-1.5 text-[13px] font-medium text-[#0a1633] transition hover:bg-[#eaf3ff]">
                            <input type="checkbox" name="grade_levels[]" value="{{ $g }}" class="h-4 w-4 accent-[#0018f9]"
                                   {{ in_array((string) $g, $selectedGrades, true) ? 'checked' : '' }}>
                            Grade {{ $g }}
                        </label>
                    @endfor
                </div>
            </div>

            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Sections</label>
                @forelse ($sections as $section)
                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-[#0018f9]/15 bg-white px-2.5 py-1.5 text-[13px] font-medium text-[#0a1633] transition hover:bg-[#eaf3ff]">
                        <input type="checkbox" name="sections[]" value="{{ $section }}" class="h-4 w-4 accent-[#0018f9]"
                               {{ in_array((string) $section, $selectedSections, true) ? 'checked' : '' }}>
                        {{ $section }}
                    </label>
                @empty
                    <p class="m-0 text-[12.5px] text-slate-500">No advisory sections assigned yet.</p>
                @endforelse
            </div>

            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Specific Students</label>
                <select name="students[]" multiple size="5" class="futuristic-select px-4 py-2.5">
                    @forelse ($students as $student)
                        <option value="{{ $student->student_id }}"
                                {{ in_array((string) $student->student_id, $selectedStudents, true) ? 'selected' : '' }}>
                            {{ $student->student_name }} (Grade {{ $student->grade_level ?? 'N/A' }})
                        </option>
                    @empty
                        <option disabled>No active students</option>
                    @endforelse
                </select>
                <p class="m-0 text-[11.5px] text-slate-400">Hold Ctrl/Cmd (Windows) or Cmd (Mac) to select multiple students.</p>
            </div>
        </div>

        <div class="announcement-teachers-group hidden grid gap-3 rounded-xl border border-[#0018f9]/15 bg-[#f4f8ff]/70 p-4">
            <p class="m-0 text-[12px] font-semibold text-[#0018f9]">
                Refine teacher audience — leave blank to reach all teachers.
            </p>
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Specific Teachers</label>
                <select name="teachers[]" multiple size="6" class="futuristic-select px-4 py-2.5">
                    @forelse ($teachers as $teacher)
                        <option value="{{ $teacher->teacher_id }}"
                                {{ in_array((string) $teacher->teacher_id, $selectedTeachers, true) ? 'selected' : '' }}>
                            {{ $teacher->teacher_name }}
                        </option>
                    @empty
                        <option disabled>No active teachers</option>
                    @endforelse
                </select>
                <p class="m-0 text-[11.5px] text-slate-400">Hold Ctrl/Cmd (Windows) or Cmd (Mac) to select multiple teachers.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Publish Date *</label>
                <input type="date" name="publish_date" required value="{{ old('publish_date', $oldPublishDate ?: date('Y-m-d')) }}" class="{{ $inputClass }}">
                @error('publish_date')
                    <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Expiration Date</label>
                <input type="date" name="expiration_date" value="{{ old('expiration_date', $oldExpirationDate) }}" class="{{ $inputClass }}">
                @error('expiration_date')
                    <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">School Year *</label>
                <select name="school_year" required class="futuristic-select px-4 py-2.5">
                    @forelse ($years as $year)
                        <option value="{{ $year }}" {{ old('school_year', $announcement->school_year ?? $defaultYear) === $year ? 'selected' : '' }}>{{ $year }}</option>
                    @empty
                        <option value="{{ $defaultYear }}">{{ $defaultYear }}</option>
                    @endforelse
                </select>
                @error('school_year')
                    <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Term *</label>
                <select name="term" required class="futuristic-select px-4 py-2.5">
                    @for ($t = 1; $t <= 3; $t++)
                        <option value="{{ $t }}" {{ (int) old('term', $announcement->term ?? $defaultTerm) === $t ? 'selected' : '' }}>Term {{ $t }}</option>
                    @endfor
                </select>
                @error('term')
                    <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid gap-1.5">
            <label class="{{ $labelClass }}">Attachment (optional, max 5MB)</label>
            @if ($isEdit && $announcement->attachment)
                <div class="flex flex-wrap items-center gap-3 rounded-lg border border-[#0018f9]/15 bg-[#f4f8ff]/70 px-3 py-2.5">
                    <a href="{{ asset('storage/' . $announcement->attachment) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#0018f9] hover:underline">
                        {{ $announcement->attachment_name ?? 'Current attachment' }}
                    </a>
                    <label class="inline-flex cursor-pointer items-center gap-1.5 text-[12px] font-medium text-red-600">
                        <input type="checkbox" name="remove_attachment" value="1" class="h-4 w-4 accent-red-600"> Remove existing attachment
                    </label>
                </div>
            @endif
            <input type="file" name="attachment" class="block w-full text-[13px] text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-[#0018f9]/10 file:px-3 file:py-2 file:text-[13px] file:font-semibold file:text-[#0018f9] hover:file:bg-[#0018f9]/20">
            @error('attachment')
                <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-3 pt-2 justify-end">
            <button type="button"
                    onclick="document.getElementById('{{ $modalId }}').close()"
                    class="rounded-lg border border-[#0018f9]/20 bg-white px-6 py-2.5 font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                Cancel
            </button>
            <button type="submit"
                    class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-6 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">
                {{ $isEdit ? 'Update Announcement' : 'Publish Announcement' }}
            </button>
        </div>
    </form>
</dialog>

<style>
    .announcement-form-modal {
        border: none;
        border-radius: 12px;
        box-shadow: 0 20px 50px -12px rgba(2, 6, 23, 0.35);
        background: white;
        max-width: 640px;
        width: 94%;
        padding: 0;
        margin: auto;
        inset: 0;
        position: fixed;
        align-items: center;
        justify-content: center;
    }
    .announcement-form-modal::backdrop {
        background: rgba(10, 22, 51, 0.5);
        backdrop-filter: blur(4px);
    }
    .announcement-form-modal[open] {
        display: flex;
    }
    .announcement-form-modal form {
        width: 100%;
        max-height: 88vh;
        overflow-y: auto;
    }
</style>

<script>
    function toggleAnnouncementAudience(select) {
        var role = select ? select.value : '';
        var dialog = select ? select.closest('dialog') : null;
        var studentGroup = dialog ? dialog.querySelector('.announcement-students-group') : null;
        var teacherGroup = dialog ? dialog.querySelector('.announcement-teachers-group') : null;
        if (studentGroup) studentGroup.classList.toggle('hidden', role !== 'students');
        if (teacherGroup) teacherGroup.classList.toggle('hidden', role !== 'teachers');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('select[name="target_role"]').forEach(function (select) {
            toggleAnnouncementAudience(select);
        });
    });
</script>
