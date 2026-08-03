<x-layouts.app :title="'Enrollment Requests'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Enrollment Requests</h2>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full border border-amber-300/50 bg-amber-50 px-3 py-1.5 text-[12.5px] font-semibold text-amber-700">{{ $pendingCount }} pending</span>
            <a href="{{ route('teacher.dashboard') }}"
               class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Dashboard</a>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <table class="w-full border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Student</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Date</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Status</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $i => $r)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 font-medium text-[#0a1633]">{{ $r->student_name }}</td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $r->date_requested }}</td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="rounded-full px-2.5 py-1 text-[12px] font-semibold capitalize {{ $r->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($r->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $r->status }}
                            </span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            @if ($r->status === 'pending')
                                <div class="flex flex-wrap items-center gap-2">
                                    <form method="POST" action="{{ route('teacher.enrollment-requests.approve') }}"
                                          onsubmit="return confirm('Approve this enrollment request?')" class="m-0">
                                        @csrf
                                        <input type="hidden" name="request_id" value="{{ $r->id }}">
                                        <button type="submit"
                                                class="rounded-md bg-gradient-to-r from-[#10b981] to-[#059669] px-3 py-1.5 text-[12.5px] font-semibold text-white shadow-[0_3px_10px_-3px_rgba(16,185,129,0.7)] transition hover:brightness-110">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('teacher.enrollment-requests.reject') }}"
                                          onsubmit="return confirm('Reject this enrollment request?')" class="m-0">
                                        @csrf
                                        <input type="hidden" name="request_id" value="{{ $r->id }}">
                                        <button type="submit"
                                                class="rounded-md bg-gradient-to-r from-[#ef4444] to-[#dc2626] px-3 py-1.5 text-[12.5px] font-semibold text-white shadow-[0_3px_10px_-3px_rgba(239,68,68,0.7)] transition hover:brightness-110">Reject</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-[#6b7280]">No enrollment requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>