<dialog id="edit-advisory-modal" class="modal-modal">
    <form id="edit-advisory-form" method="POST"
          action="{{ route('office.advisory.update', $teacher->user_id) }}" class="grid gap-4 p-6 max-w-md"
          data-ajax="1">
        @csrf
        @method('PUT')
        
        <div class="grid gap-2">
            <label class="text-[13px] font-semibold text-[#0a1633]">Teacher</label>
            <input type="text" readonly
                   value="{{ $teacher->name }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-[#f4f8ff] px-4 py-2.5 text-[14px] text-[#0a1633] shadow-sm outline-none">
            <input type="hidden" name="teacher_user_id" value="{{ $teacher->user_id }}">
        </div>

        <div class="grid gap-2">
            <label for="grade_level" class="text-[13px] font-semibold text-[#0a1633]">Grade Level</label>
            <select id="grade_level" name="grade_level" required
                    class="futuristic-select px-4 py-2.5">
                <option value="">-- Select Grade --</option>
                <optgroup label="Junior High School">
                    <option value="7" {{ old('grade_level', $parsed['grade'] ?? null) == 7 ? 'selected' : '' }}>Grade 7</option>
                    <option value="8" {{ old('grade_level', $parsed['grade'] ?? null) == 8 ? 'selected' : '' }}>Grade 8</option>
                    <option value="9" {{ old('grade_level', $parsed['grade'] ?? null) == 9 ? 'selected' : '' }}>Grade 9</option>
                    <option value="10" {{ old('grade_level', $parsed['grade'] ?? null) == 10 ? 'selected' : '' }}>Grade 10</option>
                </optgroup>
                <optgroup label="Senior High School">
                    <option value="11" {{ old('grade_level', $parsed['grade'] ?? null) == 11 ? 'selected' : '' }}>Grade 11</option>
                    <option value="12" {{ old('grade_level', $parsed['grade'] ?? null) == 12 ? 'selected' : '' }}>Grade 12</option>
                </optgroup>
            </select>
            @error('grade_level')
                <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid gap-2">
            <label for="section_name" class="text-[13px] font-semibold text-[#0a1633]">Section Name</label>
            <input id="section_name" name="section_name" type="text" required maxlength="255"
                   placeholder="e.g. A, B, C, I-A, Rizal..."
                   value="{{ old('section_name', $parsed['section'] ?? '') }}"
                   class="rounded-lg border px-4 py-2.5 text-[14px] text-[#0a1633] shadow-sm outline-none transition focus:ring-2 {{ $errors->has('section_name')
                        ? 'border-red-400 bg-red-50/40 focus:border-red-500 focus:ring-red-500/15'
                        : 'border-[#0018f9]/20 bg-white focus:border-[#0018f9] focus:ring-[#0018f9]/15' }}">
            @error('section_name')
                <span class="inline-flex items-start gap-1.5 rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-[12px] font-medium leading-snug text-red-600">
                    <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div id="track-field" class="grid gap-2" style="display: {{ (old('grade_level', $parsed['grade'] ?? null) >= 11) ? 'grid' : 'none' }};">
            <label for="track" class="text-[13px] font-semibold text-[#0a1633]">Track (SHS Only)</label>
            <select id="track" name="track"
                    class="futuristic-select track-select px-4 py-2.5">
                <option value="">-- Select Track --</option>
                <option value="Academic" {{ old('track', $parsed['track'] ?? null) == 'Academic' ? 'selected' : '' }}>Academic</option>
                <option value="TVL" {{ old('track', $parsed['track'] ?? null) == 'TVL' ? 'selected' : '' }}>T-V-L</option>
            </select>
            @error('track')
                <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-3 pt-2 justify-end">
            <button type="button"
                    onclick="closeEditModal()"
                    class="rounded-lg border border-[#0018f9]/20 bg-white px-6 py-2.5 font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                Cancel
            </button>
            <button type="submit"
                    class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-6 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">
                Update Assignment
            </button>
        </div>
    </form>
</dialog>

<style>
    .modal-modal {
        border: none;
        border-radius: 12px;
        box-shadow: 0 20px 50px -12px rgba(2, 6, 23, 0.35);
        background: white;
        max-width: 480px;
        width: 90%;
        padding: 0;
        margin: auto;
        inset: 0;
        position: fixed;
        align-items: center;
        justify-content: center;
    }
    .modal-modal::backdrop {
        background: rgba(10, 22, 51, 0.5);
        backdrop-filter: blur(4px);
    }
    .modal-modal[open] {
        display: flex;
    }
    .modal-modal form {
        width: 100%;
    }
</style>