<x-layouts.app :title="'Submit Grades'">
    <div class="mx-auto w-full max-w-[1020px] pb-4 pt-2">
        {{-- Header --}}
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3">
            <div class="group relative flex items-center gap-2.5">
                <span class="inline-block h-6 w-6 rounded-full bg-gradient-to-br from-[#0018f9] to-[#38bdf8] shadow-[0_0_12px_rgba(0,24,249,0.5)]"></span>
                <div class="relative">
                    <h2 class="m-0 bg-gradient-to-r from-[#0a1633] via-[#0018f9] to-[#0080fe] bg-clip-text text-[22px] font-extrabold tracking-tight text-transparent">SUBMIT GRADE</h2>
                    <div class="mt-1 h-[3px] w-full rounded-full bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-transparent"></div>
                </div>
            </div>
            <a href="{{ route('teacher.grades-overview') }}"
               class="relative inline-flex items-center gap-2 overflow-hidden rounded-lg border border-[#0018f9]/30 bg-white/60 px-4 py-2 font-semibold text-[#0a1633] no-underline shadow-[0_0_0_rgba(0,24,249,0)] transition-all duration-300 hover:border-[#38bdf8]/70 hover:bg-white hover:text-[#0018f9] hover:shadow-[0_0_20px_rgba(56,189,248,0.45)]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                Grades Overview
            </a>
        </div>

        @if ($errors->any())
            <div class="relative mb-6 overflow-hidden rounded-xl border border-red-400/40 bg-red-500/5 p-4 text-[13px] text-red-600 backdrop-blur-sm">
                <div class="absolute inset-x-0 top-0 h-[2px] bg-gradient-to-r from-red-500 to-orange-400"></div>
                <ul class="m-0 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($students->isEmpty())
            <div class="relative overflow-hidden rounded-2xl border border-[#38bdf8]/25 bg-[#0a1633]/[0.03] p-10 text-center backdrop-blur-sm">
                <div class="pointer-events-none absolute inset-0 opacity-[0.35]"
                     style="background-image: linear-gradient(rgba(0,24,249,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(0,24,249,0.08) 1px, transparent 1px); background-size: 28px 28px;"></div>
                <div class="pointer-events-none absolute left-3 top-3 h-7 w-7 rounded-tl-lg border-l-2 border-t-2 border-[#0018f9]/70"></div>
                <div class="pointer-events-none absolute bottom-3 right-3 h-7 w-7 rounded-br-lg border-b-2 border-r-2 border-[#38bdf8]/60"></div>
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>

                <div class="relative mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-[#0018f9]/25 bg-white/70 text-[#0018f9] shadow-[0_0_24px_rgba(0,24,249,0.25)]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-7 w-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.621-3.029m0 0a5.995 5.995 0 0 0-1.242-2.102 5.986 5.986 0 0 0-1.57-1.059m0 0A3.982 3.982 0 0 1 12 12.004c-.689 0-1.339.19-1.897.524m4.253-2.524A5.963 5.963 0 0 0 12 10.004a5.962 5.962 0 0 0-3.823 1.524m4.253-2.524a3.98 3.98 0 0 1-.378.776m-.378-.776c-.133.303-.258.602-.377.776" />
                    </svg>
                </div>
                <h3 class="relative m-0 text-[17px] font-bold text-[#0a1633]">NO APPROVED STUDENTS</h3>
                <p class="relative mt-2 text-[13.5px] text-slate-500">You need approved enrollment requests before you can submit grades.</p>
            </div>
        @else
            <form method="POST" action="{{ route('teacher.submit-grades.store') }}" class="group">
                @csrf

                {{-- ====== Form card: holographic glass panel ====== --}}
                <div class="fut-card relative overflow-hidden rounded-2xl">
                    {{-- Animated gradient border --}}
                    <div class="fut-border"></div>

                    {{-- Ambient glows --}}
                    <div class="pointer-events-none absolute -left-16 -top-16 h-48 w-48 rounded-full bg-[#0018f9]/15 blur-[70px]"></div>
                    <div class="pointer-events-none absolute -bottom-16 -right-16 h-48 w-48 rounded-full bg-[#38bdf8]/15 blur-[70px]"></div>

                    {{-- Inner glass surface --}}
                    <div class="fut-card-inner relative m-[1.5px] rounded-[15px] bg-gradient-to-br from-white/85 via-white/90 to-[#eef4ff]/90 backdrop-blur-md">
                        {{-- Scanline overlay --}}
                        <div class="fut-scanline pointer-events-none absolute inset-0"></div>

                        {{-- Grid backdrop --}}
                        <div class="pointer-events-none absolute inset-0 opacity-[0.4]"
                             style="background-image: linear-gradient(rgba(10,22,51,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(10,22,51,0.05) 1px, transparent 1px); background-size: 26px 26px;"></div>

                        {{-- Corner brackets (background decoration only) --}}
                        <div class="corner-bracket corner-bracket-tl"></div>
                        <div class="corner-bracket corner-bracket-tr"></div>
                        <div class="corner-bracket corner-bracket-bl"></div>
                        <div class="corner-bracket corner-bracket-br"></div>

                        {{-- Card header (content layer, sits above decoration) --}}
                        <header class="record-grade-header">
                            <div class="record-grade-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5.5 w-5.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                </svg>
                            </div>

                            <div class="record-grade-text">
                                <h3>Record a Grade</h3>
                                <p>Select a student and subject, then input the grade value.</p>
                            </div>
                        </header>

                        {{-- Card body --}}
                        <div class="relative px-6 pb-6 pt-7 sm:px-8 sm:pb-8 sm:pt-8">
                            <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
                                {{-- Student select --}}
                                <div class="fut-field">
                                    <label for="student_id" class="fut-label">Student</label>
                                    <div class="fut-input-wrap">
                                        <span class="fut-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                            </svg>
                                        </span>
                                        <select name="student_id" id="student_id" required onchange="loadSubjects()"
                                                class="fut-input fut-select">
                                            <option value="">Select Student</option>
                                            @foreach ($students as $stu)
                                                <option value="{{ $stu->id }}">{{ $stu->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="fut-line"></span>
                                    </div>
                                </div>

                                {{-- Subject select --}}
                                <div class="fut-field">
                                    <label for="subject_id" class="fut-label">Subject</label>
                                    <div class="fut-input-wrap">
                                        <span class="fut-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                            </svg>
                                        </span>
                                        <select name="subject_id" id="subject_id" required disabled
                                                class="fut-input fut-select disabled:opacity-50 disabled:cursor-not-allowed">
                                            <option value="">Select Subject</option>
                                        </select>
                                        <span class="fut-line"></span>
                                    </div>
                                </div>

                                {{-- Grade input --}}
                                <div class="fut-field">
                                    <label for="grade" class="fut-label">Grade <span class="font-normal text-slate-400">(0-100 or N/A)</span></label>
                                    <div class="fut-input-wrap">
                                        <span class="fut-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                                            </svg>
                                        </span>
                                        <input type="text" id="grade" name="grade" placeholder="85" pattern="(?:100|[0-9]{1,2}|N/A)"
                                               oninput="updatePreview()"
                                               class="fut-input">
                                        <span class="fut-line"></span>
                                    </div>
                                </div>

                                {{-- Remarks input --}}
                                <div class="fut-field">
                                    <label for="remarks" class="fut-label">Remarks</label>
                                    <div class="fut-input-wrap">
                                        <span class="fut-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </span>
                                        <input type="text" id="remarks" name="remarks" placeholder="Optional remark"
                                               oninput="updatePreview()"
                                               class="fut-input">
                                        <span class="fut-line"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- ====== Live preview: HUD panel ====== --}}
                            <div class="fut-hud relative mt-8 overflow-hidden rounded-xl">
                                <div class="pointer-events-none absolute inset-x-0 top-0 h-[2px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
                                <div class="pointer-events-none absolute inset-0 opacity-40"
                                     style="background-image: radial-gradient(circle at 1px 1px, rgba(0,24,249,0.18) 1px, transparent 0); background-size: 14px 14px;"></div>

                                <div class="relative flex items-center justify-between gap-2 px-6 pt-5">
                                    <p class="m-0 flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.18em] text-[#0018f9]/80">
                                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-[#0080fe]"></span>
                                        Grade Preview
                                    </p>
                                </div>

                                <div class="relative grid grid-cols-1 gap-3 px-6 py-4 sm:grid-cols-2 sm:gap-4 sm:py-5">
                                    <div class="flex items-center gap-3 rounded-lg border border-[#0018f9]/10 bg-white/50 px-4 py-3 backdrop-blur-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0080fe]">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                        <span class="min-w-0 flex-1">
                                            <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Student</span>
                                            <strong id="pv-student" class="block truncate text-[13px] text-[#0a1633]">—</strong>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 rounded-lg border border-[#0018f9]/10 bg-white/50 px-4 py-3 backdrop-blur-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0080fe]">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                        </svg>
                                        <span class="min-w-0 flex-1">
                                            <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Subject</span>
                                            <strong id="pv-subject" class="block truncate text-[13px] text-[#0a1633]">—</strong>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 rounded-lg border border-[#0018f9]/10 bg-white/50 px-4 py-3 backdrop-blur-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0080fe]">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                                        </svg>
                                        <span class="min-w-0 flex-1">
                                            <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Grade</span>
                                            <strong id="pv-grade" class="block text-[13px] text-[#0018f9]">—</strong>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 rounded-lg border border-[#0018f9]/10 bg-white/50 px-4 py-3 backdrop-blur-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 shrink-0 text-[#0080fe]">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        <span class="min-w-0 flex-1">
                                            <span class="mb-1 block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Current</span>
                                            <strong id="pv-current" class="block text-[13px] text-[#10b981]">—</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- ====== Submit button: neon launch button ====== --}}
                            <button type="submit" name="submit_grade"
                                    class="fut-submit relative mt-8 flex w-full items-center justify-center gap-3 overflow-hidden rounded-xl p-4 text-[16px] font-bold tracking-wide text-white">
                                <span class="fut-shine pointer-events-none absolute inset-0"></span>
                                <span class="relative">Save Grade</span>
                                <span class="fut-submit-icon relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <style>
        /* ============================================================
           Futuristic Grade Form
           ============================================================ */

        .fut-card {
            --fut-blue: #0018f9;
            --fut-cyan: #38bdf8;
            animation: fut-rise 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* ============================================================
           Record a Grade header — content layer
           Sits above the background decoration; keeps its own safe area.
           ============================================================ */
        .record-grade-header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 24px 28px;
            border-bottom: 1px solid rgba(0, 24, 249, 0.10);
        }

        .record-grade-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0018f9 0%, #0056ff 55%, #38bdf8 100%);
            color: #ffffff;
            box-shadow: 0 0 20px rgba(0, 24, 249, 0.5);
        }

        .record-grade-icon svg {
            width: 22px;
            height: 22px;
            flex: none;
        }

        .record-grade-text {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }

        .record-grade-text h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.25;
            color: #0a1633;
        }

        .record-grade-text p {
            margin: 0;
            font-size: 13px;
            line-height: 1.55;
            color: #64748b;
        }

        /* ============================================================
           Corner brackets — background decoration only
           Purely visual; never participates in content layout.
           ============================================================ */
        .corner-bracket {
            position: absolute;
            width: 20px;
            height: 20px;
            pointer-events: none;
        }

        .corner-bracket-tl {
            top: 8px;
            left: 8px;
            border-top: 2px solid rgba(0, 24, 249, 0.55);
            border-left: 2px solid rgba(0, 24, 249, 0.55);
            border-top-left-radius: 8px;
        }

        .corner-bracket-tr {
            top: 8px;
            right: 8px;
            border-top: 2px solid rgba(0, 24, 249, 0.55);
            border-right: 2px solid rgba(0, 24, 249, 0.55);
            border-top-right-radius: 8px;
        }

        .corner-bracket-bl {
            bottom: 8px;
            left: 8px;
            border-bottom: 2px solid rgba(56, 189, 248, 0.5);
            border-left: 2px solid rgba(56, 189, 248, 0.5);
            border-bottom-left-radius: 8px;
        }

        .corner-bracket-br {
            bottom: 8px;
            right: 8px;
            border-bottom: 2px solid rgba(56, 189, 248, 0.5);
            border-right: 2px solid rgba(56, 189, 248, 0.5);
            border-bottom-right-radius: 8px;
        }

        .fut-border {
            position: absolute;
            inset: 0;
            border-radius: 16px;
            padding: 1px;
            background: linear-gradient(135deg, #0018f9, #38bdf8 35%, rgba(56, 189, 248, 0.15) 55%, #0018f9);
            background-size: 250% 250%;
            animation: fut-border-flow 6s ease infinite;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        @keyframes fut-border-flow {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes fut-rise {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .fut-scanline {
            background: repeating-linear-gradient(
                to bottom,
                transparent 0px,
                transparent 3px,
                rgba(0, 24, 249, 0.025) 3px,
                rgba(0, 24, 249, 0.025) 4px
            );
            opacity: 0.6;
        }

        /* ---- Fields ---- */
        .fut-field {
            position: relative;
        }

        .fut-label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #0a1633;
            transition: color 0.3s ease;
        }

        .fut-label::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 999px;
            background: linear-gradient(135deg, #0018f9, #38bdf8);
            box-shadow: 0 0 8px rgba(0, 24, 249, 0.8);
        }

        .fut-field:focus-within .fut-label {
            color: #0018f9;
        }

        .fut-input-wrap {
            position: relative;
        }

        .fut-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 2;
            color: #64748b;
            pointer-events: none;
            transition: color 0.3s ease, transform 0.3s ease, filter 0.3s ease;
        }

        .fut-field:focus-within .fut-icon {
            color: #0018f9;
            filter: drop-shadow(0 0 5px rgba(0, 24, 249, 0.5));
            transform: translateY(-50%) scale(1.1);
        }

        .fut-input {
            position: relative;
            width: 100%;
            border: 1px solid rgba(0, 24, 249, 0.18);
            border-radius: 10px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(248, 250, 252, 0.92));
            padding: 13px 34px 13px 38px;
            font-size: 14px;
            font-weight: 500;
            color: #0a1633;
            box-shadow: inset 0 1px 2px rgba(10, 22, 51, 0.04), 0 1px 2px rgba(0, 0, 0, 0.04);
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
        }

        .fut-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .fut-input:focus {
            border-color: #0018f9;
            background: linear-gradient(180deg, #ffffff, #f4f8ff);
            box-shadow:
                0 0 0 3.5px rgba(0, 24, 249, 0.13),
                0 0 18px -2px rgba(0, 24, 249, 0.35),
                inset 0 1px 2px rgba(10, 22, 51, 0.03);
        }

        .fut-input:hover:not(:focus) {
            border-color: #38bdf8;
        }

        /* futuristic select — reuse system styles + tweak */
        .fut-select {
            appearance: none;
            -webkit-appearance: none;
            background-image:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%230018f9' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"),
                linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(248, 250, 252, 0.92));
            background-repeat: no-repeat;
            background-position: right 12px center, center;
            background-size: 14px, cover;
            padding-right: 36px;
            cursor: pointer;
        }

        .fut-select:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .fut-select option {
            background-color: #ffffff;
            color: #0a1633;
        }

        /* Animated bottom line on focus */
        .fut-line {
            position: absolute;
            left: 36px;
            right: 12px;
            bottom: 0;
            height: 2px;
            border-radius: 999px;
            background: linear-gradient(90deg, #0018f9, #38bdf8);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
        }

        .fut-field:focus-within .fut-line {
            transform: scaleX(1);
        }

        /* ---- HUD preview panel ---- */
        .fut-hud {
            background: linear-gradient(160deg, rgba(250, 253, 255, 0.9), rgba(236, 244, 255, 0.85));
            border: 1px solid rgba(0, 24, 249, 0.14);
            box-shadow:
                inset 0 0 30px rgba(0, 24, 249, 0.05),
                0 8px 24px -14px rgba(0, 24, 249, 0.25);
            animation: fut-rise 0.5s 0.08s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* ---- Submit button ---- */
        .fut-submit {
            border: none;
            background: linear-gradient(120deg, #0018f9, #0056ff 50%, #0ea5e9);
            background-size: 200% 100%;
            background-position: 0% 0%;
            box-shadow:
                0 0 0 1px rgba(56, 189, 248, 0.35),
                0 10px 26px -8px rgba(0, 24, 249, 0.75),
                inset 0 1px 0 rgba(255, 255, 255, 0.25);
            cursor: pointer;
            transition: background-position 0.45s ease, transform 0.12s ease, box-shadow 0.3s ease;
        }

        .fut-submit:hover {
            background-position: 100% 0%;
            box-shadow:
                0 0 0 1px rgba(56, 189, 248, 0.6),
                0 0 32px -4px rgba(0, 24, 249, 0.85),
                0 12px 30px -8px rgba(0, 24, 249, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .fut-submit:active {
            transform: scale(0.985);
        }

        .fut-shine {
            background: linear-gradient(105deg, transparent 40%, rgba(255, 255, 255, 0.45) 50%, transparent 60%);
            background-size: 250% 100%;
            background-position: 100% 0;
            animation: fut-shine-sweep 3.2s ease infinite;
        }

        @keyframes fut-shine-sweep {
            0%   { background-position: 200% 0; }
            60%  { background-position: -60% 0; }
            100% { background-position: -60% 0; }
        }

        .fut-submit-icon {
            display: inline-flex;
            opacity: 0.85;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .fut-submit:hover .fut-submit-icon {
            transform: translateX(4px);
            opacity: 1;
        }

        @media (prefers-reduced-motion: reduce) {
            .fut-border,
            .fut-scanline,
            .fut-shine {
                animation: none !important;
            }
            .fut-card {
                animation: none;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const studentSel = document.getElementById('student_id');
            const subjectSel = document.getElementById('subject_id');

            // No form is rendered when the teacher has no approved students.
            if (!studentSel || !subjectSel) {
                return;
            }

            const studentMap = new Map([
                @foreach ($students as $stu)
                    [{{ $stu->id }}, @js($stu->name)],
                @endforeach
            ]);

            function updatePreview() {
                const student = studentMap.get(Number(studentSel.value));
                const subject = subjectSel.selectedOptions[0];
                document.getElementById('pv-student').textContent = student || '—';
                document.getElementById('pv-subject').textContent =
                    subject && subject.value ? (subject.dataset.name || subject.textContent) : '—';
                document.getElementById('pv-grade').textContent = document.getElementById('grade').value || '—';
                const currentWrap = document.getElementById('pv-current');
                if (subject && subject.value) {
                    currentWrap.textContent = subject.dataset.current && subject.dataset.current !== 'N/A'
                        ? subject.dataset.current
                        : 'N/A';
                } else {
                    currentWrap.textContent = '—';
                }
            }

            window.loadSubjects = function () {
                const studentId = studentSel.value;
                subjectSel.innerHTML = '<option value="">Loading...</option>';
                subjectSel.disabled = true;
                document.getElementById('pv-subject').textContent = '—';
                document.getElementById('pv-current').textContent = '—';

                if (studentId) {
                    fetch(`{{ route('teacher.subjects.json') }}?student_id=${studentId}&teacher_id={{ $teacherId }}`)
                        .then(r => {
                            if (!r.ok) {
                                throw new Error('Server responded with status ' + r.status);
                            }
                            return r.json();
                        })
                        .then(data => {
                            subjectSel.innerHTML = '<option value="">Select Subject</option>';
                            data.forEach(s => {
                                const opt = document.createElement('option');
                                opt.value = s.id;
                                opt.dataset.name = s.name;
                                opt.dataset.current = s.current_grade || 'N/A';
                                opt.text = s.name + (s.current_grade && s.current_grade !== 'N/A' ? ` (Current: ${s.current_grade})` : '');
                                subjectSel.appendChild(opt);
                            });
                            subjectSel.disabled = data.length === 0;
                            subjectSel.onchange = updatePreview;
                            if (data.length === 0) {
                                subjectSel.innerHTML = '<option value="">No subjects available</option>';
                            }
                        }).catch(() => {
                            subjectSel.innerHTML = '<option value="">Error loading subjects</option>';
                            showToast('Unable to load subjects for this student. Please try again.', 'error');
                        });
                }
            };

            studentSel.addEventListener('change', loadSubjects);
            studentSel.addEventListener('change', updatePreview);
        });
    </script>
</x-layouts.app>
