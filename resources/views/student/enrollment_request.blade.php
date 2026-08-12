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
    @elseif ($enrollmentState === 'pending')
        <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-[14px] text-amber-800">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m-3-3H9.5" />
                </svg>
            </span>
            <p class="m-0">
                <strong>Your enrollment is on process, please wait.</strong><br>
                Your request is pending approval from your teacher.
            </p>
        </div>
    @elseif ($enrollmentState === 'approved')
        <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-[14px] text-emerald-800">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15l3.75-2.25M9 6.75h6a2 2 0 012 2v1.25m-8.5 6.5h.007v.008H9v-.008Z" />
                </svg>
            </span>
            <p class="m-0">
                <strong>You're now enrolled.</strong><br>
                Your enrollment has been approved. You can now access your schedule and subjects.
            </p>
        </div>
    @else
        <div class="mb-6 rounded-2xl border border-[#0018f9]/15 bg-white/80 p-5 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] sm:p-6">
            @if ($enrollmentState === 'rejected')
                <div class="mb-3 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-3 text-[13px] text-red-700">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-red-100 text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3 w-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </span>
                    <p class="m-0">Your previous request was <strong>rejected</strong>. Please choose another teacher below.</p>
                </div>
            @endif
            <form method="POST" action="{{ route('student.enrollment.store') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[240px]">
                    <label class="mb-1.5 block text-[13px] font-semibold text-[#0a1633]">Teacher</label>
                    <select name="teacher_id" required {{ $isGraduateOrInactive ? 'disabled' : '' }}
                            class="futuristic-select w-full px-3 py-2 text-[14px] disabled:opacity-50 disabled:cursor-not-allowed">
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
    @endif

    <h3 class="mb-3 flex items-center gap-2 text-[15px] font-semibold text-[#0a1633]">
        <span class="inline-block h-4 w-1 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
        Your Requests
    </h3>
    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse min-w-[480px] text-[14px]">
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
    </div>
</x-layouts.app>