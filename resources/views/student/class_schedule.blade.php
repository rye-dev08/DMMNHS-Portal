<x-layouts.app :title="'Class Schedule'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">My Class Schedule</h2>
        </div>
        <a href="{{ route('student.dashboard') }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Dashboard</a>
    </div>

    <p class="mb-4 text-[13px] text-[#0a1633]/60">
        Term <strong>{{ $currentTerm }}</strong> &middot; schedule shown for the active/current term only.
    </p>

    @if ($schedule->isEmpty())
        <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-10 text-center shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
            <h3 class="m-0 text-[16px] font-semibold text-[#0a1633]">No Classes for Term {{ $currentTerm }} Yet</h3>
            <p class="mt-1.5 text-[14px] text-slate-500">Await teacher/adviser enrollment. <a href="{{ route('student.enrollment') }}" class="font-medium text-[#0018f9] hover:underline">Submit Request</a>.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] border-collapse text-[14px]">
                <thead>
                    <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Subject</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Course Code</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher Code</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Room</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedule as $i => $row)
                        <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                            <td class="border border-[#dbe4f0] p-2.5 font-semibold text-[#0a1633]">{{ $row->subject_name }}</td>
                            <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $row->course_code }}</td>
                            <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $row->teacher_code }}</td>
                            <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $row->room_no }}</td>
                            <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $row->teacher_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.app>