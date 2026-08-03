<x-layouts.app :title="'Enrollment Request'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Enroll in Class</h2>
        </div>
        <a href="{{ route('student.dashboard') }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Dashboard</a>
    </div>

    @if ($isGraduateOrInactive)
        <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-3.5 text-[13px] text-red-700">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </span>
            <p class="m-0">
                You cannot enroll because you are either <strong>graduated</strong> or <strong>inactive</strong>.
            </p>
        </div>
    @endif

    <div class="mb-6 rounded-2xl border border-[#0018f9]/15 bg-white/80 p-5 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] sm:p-6">
        <form method="POST" action="{{ route('student.enrollment.store') }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-[240px]">
                <label class="mb-1.5 block text-[13px] font-semibold text-[#0a1633]">Teacher</label>
                <select name="teacher_id" required {{ $isGraduateOrInactive ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15 disabled:bg-slate-50 disabled:text-slate-400">
                    <option value="">Select Teacher (Active)</option>
                    @foreach ($teachers as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->name }} {{ $t->advisory_class ? '(' . $t->advisory_class . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="rounded-lg px-5 py-2.5 font-semibold text-white transition {{ $isGraduateOrInactive ? 'cursor-not-allowed bg-slate-400' : 'bg-gradient-to-r from-[#0018f9] to-[#0080fe] shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] hover:brightness-110' }}"
                    {{ $isGraduateOrInactive ? 'disabled' : '' }}>
                {{ $isGraduateOrInactive ? "Can't Enroll" : "Send Enrollment Request" }}
            </button>
        </form>
    </div>

    <h3 class="mb-3 flex items-center gap-2 text-[15px] font-semibold text-[#0a1633]">
        <span class="inline-block h-4 w-1 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
        Your Requests
    </h3>
    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <table class="w-full border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Status</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $i => $r)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 font-medium text-[#0a1633]">{{ $r->name }}</td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="rounded-full px-2.5 py-1 text-[12px] font-semibold capitalize {{ $r->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($r->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $r->status }}
                            </span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $r->date_requested }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-8 text-center text-[#6b7280]">No enrollment requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>