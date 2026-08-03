<x-layouts.app :title="'Submit Grades'">
    <div class="mx-auto mb-6 max-w-[720px]">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
                <h2 class="m-0 text-[#0a1633]">Submit Grade</h2>
            </div>
            <a href="{{ route('teacher.grades-overview') }}"
               class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Grades Overview</a>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3.5 text-[13px] text-red-700">
                <ul class="m-0 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($students->isEmpty())
            <div class="relative overflow-hidden rounded-2xl border border-[#0018f9]/15 bg-white/80 p-10 text-center shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)]">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
                <h3 class="m-0 text-[16px] font-semibold text-[#0a1633]">No Approved Students</h3>
                <p class="mt-1.5 text-[14px] text-slate-500">You need approved enrollment requests before you can submit grades.</p>
            </div>
        @else
            <form method="POST" action="{{ route('teacher.submit-grades.store') }}">
                @csrf

                {{-- Form card --}}
                <div class="relative overflow-hidden rounded-2xl border border-[#0018f9]/15 bg-white/85 shadow-[0_12px_34px_-14px_rgba(0,24,249,0.28)]">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>

                    {{-- Card header --}}
                    <div class="flex items-center gap-3 border-b border-[#0018f9]/10 bg-gradient-to-r from-[#0a1633]/[0.03] to-[#164aa8]/[0.06] px-5 py-4 sm:px-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0018f9] to-[#0080fe] text-white shadow-[0_5px_14px_-5px_rgba(0,24,249,0.7)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="m-0 text-[16px] font-semibold text-[#0a1633]">Record a Grade</h3>
                            <p class="m-0 text-[12.5px] text-slate-500">Select a student and subject, then enter the grade.</p>
                        </div>
                    </div>

                    {{-- Card body --}}
                    <div class="p-5 sm:p-6">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="student_id" class="mb-1.5 block text-[13px] font-semibold text-[#0a1633]">Student</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                    </span>
                                    <select name="student_id" id="student_id" required onchange="loadSubjects()"
                                            class="w-full rounded-lg border border-[#0018f9]/20 bg-white py-2.5 pl-9 pr-3 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                                        <option value="">Select Student</option>
                                        @foreach ($students as $stu)
                                            <option value="{{ $stu->id }}">{{ $stu->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="subject_id" class="mb-1.5 block text-[13px] font-semibold text-[#0a1633]">Subject</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                        </svg>
                                    </span>
                                    <select name="subject_id" id="subject_id" required disabled
                                            class="w-full rounded-lg border border-[#0018f9]/20 bg-white py-2.5 pl-9 pr-3 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15 disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="">Select Subject</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="grade" class="mb-1.5 block text-[13px] font-semibold text-[#0a1633]">Grade <span class="font-normal text-slate-400">(0-100 or N/A)</span></label>
                                <input type="text" id="grade" name="grade" placeholder="85" pattern="[0-9]{1,3}|N/A"
                                       oninput="updatePreview()"
                                       class="w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                            </div>

                            <div>
                                <label for="remarks" class="mb-1.5 block text-[13px] font-semibold text-[#0a1633]">Remarks</label>
                                <input type="text" id="remarks" name="remarks" placeholder="Optional remark"
                                       oninput="updatePreview()"
                                       class="w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                            </div>
                        </div>

                        {{-- Live preview --}}
                        <div class="mt-6 rounded-xl border border-[#0018f9]/10 bg-gradient-to-br from-[#0a1633]/[0.03] to-[#164aa8]/[0.06] p-4">
                            <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-widest text-[#0018f9]/70">Grade Preview</p>
                            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-[13px] text-slate-600">
                                <span>Student: <strong id="pv-student" class="text-[#0a1633]">—</strong></span>
                                <span>Subject: <strong id="pv-subject" class="text-[#0a1633]">—</strong></span>
                                <span>Grade: <strong id="pv-grade" class="text-[#0a1633]">—</strong></span>
                                <span class="hidden" id="pv-current-wrap">Current: <strong id="pv-current" class="text-[#0018f9]"></strong></span>
                            </div>
                        </div>

                        <button type="submit" name="submit_grade"
                                class="mt-5 w-full rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] p-3 text-[15px] font-semibold text-white shadow-[0_6px_18px_-6px_rgba(16,185,129,0.8)] transition hover:brightness-110 active:scale-[0.99]">
                            Save Grade
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const studentSel = document.getElementById('student_id');
            const subjectSel = document.getElementById('subject_id');

            const studentMap = new Map([
                @foreach ($students as $stu)
                    [{{ $stu->id }}, @js($stu->name)],
                @endforeach
            ]);

            function updatePreview() {
                const student = studentMap.get(Number(studentSel.value));
                const subject = subjectSel.selectedOptions[0];
                document.getElementById('pv-student').textContent = student || '—';
                document.getElementById('pv-subject').textContent = subject && subject.value ? subject.textContent : '—';
                document.getElementById('pv-grade').textContent = document.getElementById('grade').value || '—';
                const currentWrap = document.getElementById('pv-current-wrap');
                if (subject && subject.dataset.current && subject.value) {
                    currentWrap.classList.remove('hidden');
                    document.getElementById('pv-current').textContent = subject.dataset.current;
                } else {
                    currentWrap.classList.add('hidden');
                }
            }

            window.loadSubjects = function () {
                const studentId = studentSel.value;
                subjectSel.innerHTML = '<option value="">Loading...</option>';
                subjectSel.disabled = true;
                document.getElementById('pv-subject').textContent = '—';
                document.getElementById('pv-current-wrap').classList.add('hidden');

                if (studentId) {
                    fetch(`/teacher/subjects?student_id=${studentId}&teacher_id={{ $teacherId }}`)
                        .then(r => r.json())
                        .then(data => {
                            subjectSel.innerHTML = '<option value="">Select Subject</option>';
                            data.forEach(s => {
                                const opt = document.createElement('option');
                                opt.value = s.id;
                                opt.dataset.current = s.current_grade && s.current_grade !== 'N/A' ? s.current_grade : '';
                                opt.text = s.name + (s.current_grade && s.current_grade !== 'N/A' ? ` (Current: ${s.current_grade})` : '');
                                subjectSel.appendChild(opt);
                            });
                            subjectSel.disabled = data.length === 0;
                            subjectSel.onchange = updatePreview;
                        }).catch(() => {
                            subjectSel.innerHTML = '<option value="">Error loading</option>';
                        });
                }
            };

            studentSel.onchange = loadSubjects;
            studentSel.addEventListener('change', updatePreview);
        });
    </script>
</x-layouts.app>