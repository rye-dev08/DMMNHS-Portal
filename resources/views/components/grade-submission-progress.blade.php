@props(['summary', 'viewAllUrl'])
<div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] backdrop-blur-sm">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>

    <div class="mb-3 flex items-center justify-between gap-2">
        <h4 class="m-0 text-[15px] font-semibold text-[#0a1633]">Grade Submission Progress</h4>
        <span class="text-[15px] font-bold text-[#0018f9]">{{ $summary->completion }}%</span>
    </div>

    <div class="mb-3 h-2.5 w-full overflow-hidden rounded-full bg-[#e5edf8]">
        <div class="h-full rounded-full bg-gradient-to-r from-[#0018f9] to-[#38bdf8] transition-all"
             style="width: {{ $summary->completion }}%"></div>
    </div>

    <div class="mb-3 grid grid-cols-3 gap-2 text-center">
        <div class="rounded-lg bg-emerald-50 px-2 py-2">
            <p class="m-0 text-[17px] font-bold text-emerald-600">{{ $summary->submitted }}</p>
            <p class="m-0 text-[10.5px] font-semibold uppercase tracking-wide text-slate-500">Submitted</p>
        </div>
        <div class="rounded-lg bg-amber-50 px-2 py-2">
            <p class="m-0 text-[17px] font-bold text-amber-600">{{ $summary->pending }}</p>
            <p class="m-0 text-[10.5px] font-semibold uppercase tracking-wide text-slate-500">Pending</p>
        </div>
        <div class="rounded-lg bg-red-50 px-2 py-2">
            <p class="m-0 text-[17px] font-bold text-red-600">{{ $summary->late }}</p>
            <p class="m-0 text-[10.5px] font-semibold uppercase tracking-wide text-slate-500">Late</p>
        </div>
    </div>

    <a href="{{ $viewAllUrl }}"
       class="block rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2 text-center text-[13px] font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">View Details</a>
</div>
