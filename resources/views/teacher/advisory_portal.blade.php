<x-layouts.app :title="'Class Subjects'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Class Subjects</h2>
        </div>
        <a href="{{ route('teacher.dashboard') }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Dashboard</a>
    </div>

    <p class="mb-4 text-[13px] text-[#0a1633]/60">Applies to {{ $approvedCount }} approved student(s).</p>

    <form method="POST" action="{{ route('teacher.advisory-portal.store') }}" class="mb-5">
        @csrf
        <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Subject</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Course Code</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher Code</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Room</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Add</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white/90">
                        <td class="border border-[#dbe4f0] p-2.5">
                            <input type="text" name="subject_name" required placeholder="Mathematics" style="width:150px;"
                                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <input type="text" name="course_code" placeholder="MATH101" class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <input type="text" name="teacher_code" placeholder="T001" class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <input type="text" name="room_no" placeholder="Rm 101" class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <button type="submit" class="rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] px-4 py-2 font-semibold text-white shadow-[0_4px_12px_-4px_rgba(16,185,129,0.7)] transition hover:brightness-110">Add Subject</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>

    <h3 class="mb-3 flex items-center gap-2 text-[15px] font-semibold text-[#0a1633]">
        <span class="inline-block h-4 w-1 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
        Current Subjects
    </h3>
    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <table class="w-full border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Subject</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Code</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Room</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Delete?</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subjects as $i => $s)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 font-medium text-[#0a1633]">{{ $s->subject_name }}</td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $s->course_code }}</td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $s->teacher_code }}</td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $s->room_no }}</td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <form method="POST" action="{{ route('teacher.advisory-portal.destroy', $s->id) }}"
                                  style="display:inline;"
                                  onsubmit="return confirm('Delete &amp; remove from ALL students?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-md border border-[#b91c1c] bg-[#dc2626] px-2.5 py-1 text-[12px] font-semibold text-white transition hover:bg-[#b91c1c]">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-[#6b7280]">No subjects yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>