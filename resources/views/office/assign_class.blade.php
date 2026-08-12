<x-layouts.app :title="'Assign Advisory Class'">
    @php
        $takenList = collect($takenSections ?? [])->map(fn ($s) => mb_strtolower((string) $s))->all();
    @endphp

    <div class="mb-5 flex items-center gap-2">
        <span class="inline-block h-5 w-1.3 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
        <h2 class="m-0 text-[#0a1633]">Assign Advisory Class</h2>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="border-b border-[#0018f9]/10 bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] px-6 py-4">
            <h3 class="m-0 text-[16px] font-semibold text-white">New Advisory Class</h3>
            <p class="mt-1 text-[13px] text-sky-200">
                Assign an advisory section to a teacher. Section names are unique across all grade levels.
            </p>
        </div>

        <form method="POST" action="{{ route('office.assign-class.store') }}" class="grid gap-5 p-6 sm:p-8">
            @csrf

            <div class="grid gap-2">
                <label for="teacher_user_id" class="text-[13px] font-semibold text-[#0a1633]">Teacher</label>
                <select id="teacher_user_id" name="teacher_user_id" required
                        class="futuristic-select px-4 py-2.5">
                    <option value="">-- Select Teacher --</option>
                @foreach ($teachers as $t)
                    <option value="{{ $t->user_id }}" {{ old('teacher_user_id') == $t->user_id ? 'selected' : '' }}>
                        {{ $t->name }}
                    </option>
                @endforeach
                </select>
                @error('teacher_user_id')
                    <span class="inline-flex items-start gap-1.5 rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-[12px] font-medium leading-snug text-red-600">
                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="grid gap-2">
                <label for="grade_level" class="text-[13px] font-semibold text-[#0a1633]">Grade Level</label>
                <select id="grade_level" name="grade_level" required
                        class="futuristic-select px-4 py-2.5">
                    <option value="">-- Select Grade --</option>
                    <optgroup label="Junior High School">
                        <option value="7" {{ old('grade_level') == '7' ? 'selected' : '' }}>Grade 7</option>
                        <option value="8" {{ old('grade_level') == '8' ? 'selected' : '' }}>Grade 8</option>
                        <option value="9" {{ old('grade_level') == '9' ? 'selected' : '' }}>Grade 9</option>
                        <option value="10" {{ old('grade_level') == '10' ? 'selected' : '' }}>Grade 10</option>
                    </optgroup>
                    <optgroup label="Senior High School">
                        <option value="11" {{ old('grade_level') == '11' ? 'selected' : '' }}>Grade 11</option>
                        <option value="12" {{ old('grade_level') == '12' ? 'selected' : '' }}>Grade 12</option>
                    </optgroup>
                </select>
                @error('grade_level')
                    <span class="text-[12px] font-medium text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>

            <div class="grid gap-2">
                <label for="section_name" class="text-[13px] font-semibold text-[#0a1633]">Section Name</label>
                <input id="section_name" name="section_name" type="text" required maxlength="255"
                       placeholder="e.g. A, B, C, I-A, Rizal..."
                       value="{{ old('section_name') }}"
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

                <span id="section-taken-hint" class="hidden text-[12px] font-medium text-red-600">
                    This section name is already taken by another class.
                </span>

                @if (! empty($takenList))
                    <div class="mt-1 rounded-lg border border-amber-200 bg-amber-50/70 px-3 py-2">
                        <span class="text-[12px] font-semibold text-amber-800">Already in use (cannot be reused):</span>
                        <div class="mt-1.5 flex max-h-24 flex-wrap gap-1.5 overflow-y-auto">
                            @foreach (array_unique($takenList) as $taken)
                                <span class="inline-flex items-center rounded-md bg-white px-2 py-0.5 text-[12px] font-medium text-amber-700 shadow-sm ring-1 ring-amber-200">{{ $taken }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div id="track-field" class="grid gap-2" style="display: none;">
                <label for="track" class="text-[13px] font-semibold text-[#0a1633]">Track (SHS Only)</label>
                <select id="track" name="track"
                        class="futuristic-select track-select px-4 py-2.5">
                    <option value="">-- Select Track --</option>
                    <option value="Academic" {{ old('track') == 'Academic' ? 'selected' : '' }}>Academic</option>
                    <option value="TVL" {{ old('track') == 'TVL' ? 'selected' : '' }}>T-V-L</option>
                </select>
                @error('track')
                    <span class="text-[12px] font-medium text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-[#0018f9]/10">
                <a href="{{ route('office.teacher-advisory') }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white px-6 py-2.5 font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                    Cancel
                </a>
                <button type="submit"
                        class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-6 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">
                    Save Assignment
                </button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var gradeSelect = document.getElementById('grade_level');
            var trackField = document.getElementById('track-field');
            var sectionInput = document.getElementById('section_name');
            var takenHint = document.getElementById('section-taken-hint');
            var takenSections = @json($takenList);

            function toggleTrack() {
                if (!gradeSelect || !trackField) return;
                var val = gradeSelect.value;
                var isSHS = val === '11' || val === '12';
                trackField.style.display = isSHS ? 'grid' : 'none';
            }

            function checkSection() {
                if (!sectionInput || !takenHint || !Array.isArray(takenSections)) return;
                var value = (sectionInput.value || '').trim().toLowerCase();
                var isTaken = value !== '' && takenSections.indexOf(value) !== -1;
                takenHint.classList.toggle('hidden', !isTaken);
            }

            if (gradeSelect) gradeSelect.addEventListener('change', toggleTrack);
            if (sectionInput) sectionInput.addEventListener('input', checkSection);

            toggleTrack();
            checkSection();
        })();
    </script>
</x-layouts.app>
