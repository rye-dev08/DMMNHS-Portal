<x-layouts.app :title="'Grades Overview'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Grades Matrix</h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.submit-grades') }}"
               class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">Submit / Edit Grades</a>
            <a href="{{ route('teacher.dashboard') }}"
               class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Dashboard</a>
        </div>
    </div>

    <p class="mb-4 text-[13px] text-[#0a1633]/60">{{ count($studentsData) }} Students &times; {{ count($subjects) }} Unique Subjects</p>

    @if ($subjects->isEmpty())
        <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-10 text-center shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
            <h3 class="m-0 text-[16px] font-semibold text-[#0a1633]">No Subjects Yet</h3>
            <p class="mt-1.5 text-[14px] text-slate-500">Add subjects in <a href="{{ route('teacher.advisory-portal') }}" class="font-medium text-[#0018f9] hover:underline">Advisory Portal</a> first.</p>
        </div>
    @elseif (count($studentsData) === 0)
        <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-10 text-center shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
            <h3 class="m-0 text-[16px] font-semibold text-[#0a1633]">No Students</h3>
            <p class="mt-1.5 text-[14px] text-slate-500">Approve enrollment requests first.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[14px]" style="min-width:800px;">
                    <thead>
                        <tr>
                            <th style="position:sticky; left:0; background:linear-gradient(180deg,#0a1633,#164aa8); color:#fff;"
                                class="border border-[#0a1633] p-2.5 text-left text-[13px] font-semibold tracking-wide">Student</th>
                            @foreach ($subjects as $subj)
                                <th class="whitespace-nowrap border border-[#0a1633] bg-gradient-to-r from-[#0d2450] to-[#164aa8] p-2.5 text-left text-[13px] font-semibold tracking-wide text-white" style="min-width:120px;">
                                    {{ $subj->subject_name }}
                                    @if ($subj->course_code) <span class="font-normal text-white/60">({{ $subj->course_code }})</span> @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($studentsData as $sid => $student)
                            <tr class="{{ $loop->index % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                                <td style="position:sticky; left:0; background:#f9fafb; font-weight:600; min-width:200px;"
                                    class="border border-[#dbe4f0] p-2.5 text-[#0a1633]">
                                    {{ $student['name'] }}
                                </td>
                                @foreach ($subjects as $subj)
                                    @php
                                        $grade = $student['grades'][$subj->subject_name] ?? 'N/A';
                                        $mapped = \App\Support\GradeFormatter::display($grade);
                                    @endphp
                                    <td class="p-3 text-center" style="border:1px solid #dbe2ea;">
                                        <span style="background: {{ $mapped['color'] }}; color:#fff;"
                                              class="rounded-md px-3 py-2 font-bold shadow-sm">{{ $mapped['label'] }}</span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-layouts.app>