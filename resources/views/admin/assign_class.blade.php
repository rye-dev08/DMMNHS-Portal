<x-layouts.app :title="'Assign Class'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.3 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Assign Advisory Class</h2>
        </div>
        <a href="{{ route('admin.teacher-advisory') }}"
           class="rounded-lg bg-gradient-to-r from-[#6b7280] to-[#4b5563] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(75,85,99,0.6)] transition hover:brightness-110">Back to List</a>
    </div>

    <form method="POST" action="{{ route('admin.assign-class.store') }}" data-validate class="m-0">
        @csrf
        <div class="rounded-2xl border border-[#0018f9]/15 bg-white/90 p-6 shadow-[0_10px_30px_-10px_rgba(0,24,249,0.15)]">
            <div class="mb-5 grid grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- Teacher --}}
                <div class="sm:col-span-2">
                    <label for="teacher_user_id" class="block text-[13px] font-semibold text-[#0a1633]">Select Teacher</label>
                    <select id="teacher_user_id" name="teacher_user_id" required
                            class="mt-1 block w-full rounded-lg border border-[#0018f9]/20 bg-white p-3 text-[14px] text-[#0a1633] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                        <option value="" {{ !old('teacher_user_id') && !$selectedTeacher ? 'selected' : '' }}>— Choose a teacher —</option>
                        @foreach ($allTeachers as $t)
                            @php
                                $isSelected = old('teacher_user_id', $selectedTeacher?->user_id) == $t->user_id;
                            @endphp
                            <option value="{{ $t->user_id }}" {{ $isSelected ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Grade Level --}}
                <div>
                    <label for="grade_level" class="block text-[13px] font-semibold text-[#0a1633]">Grade Level</label>
                    <select id="grade_level" name="grade_level" required
                            class="mt-1 block w-full rounded-lg border border-[#0018f9]/20 bg-white p-3 text-[14px] text-[#0a1633] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                        @php
                            $currentGrade = old('grade_level', $selectedTeacher->grade_level ?? 7);
                        @endphp
                        @for ($g = 7; $g <= 12; $g++)
                            <option value="{{ $g }}" {{ $g == $currentGrade ? 'selected' : '' }}>
                                Grade {{ $g }} ({{ $g <= 10 ? 'JHS' : 'SHS' }})
                            </option>
                        @endfor
                    </select>
                </div>

                {{-- Section Name --}}
                <div>
                    <label for="section_name" class="block text-[13px] font-semibold text-[#0a1633]">Section Name</label>
                    <input type="text" id="section_name" name="section_name" required
                           value="{{ old('section_name', $selectedTeacher->section_name ?? '') }}"
                           placeholder="e.g. A, B, C"
                           class="mt-1 block w-full rounded-lg border border-[#0018f9]/20 bg-white p-3 text-[14px] text-[#0a1633] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                </div>
            </div>

            @if ($selectedTeacher && $selectedTeacher->advisory_class)
                <div class="mb-4 rounded-lg border border-[#0018f9]/10 bg-[#eaf3ff]/50 p-3 text-[13px] text-[#0a1633]/70">
                    Current advisory class: <strong>{{ $selectedTeacher->advisory_class }}</strong>
                </div>
            @endif

            <button type="submit"
                    class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-6 py-2.5 font-semibold text-white shadow-[0_4px_12px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110 active:scale-[0.99]">
                Save Advisory Class
            </button>
        </div>
    </form>
</x-layouts.app>
