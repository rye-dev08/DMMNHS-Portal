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

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-[13px] text-red-700">
            <ul class="m-0 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.advisory-portal.store') }}" class="mb-5" data-validate>
        @csrf
        <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[650px] border-collapse text-[14px]">
                <thead>
                    <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Subject</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Course Code</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher Code</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Room</th>
                        @if ($isSHS)
                            <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Subject Type</th>
                        @endif
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Add</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white/90">
                        <td class="border border-[#dbe4f0] p-2.5">
                            <input type="text" name="subject_name" required placeholder="Mathematics" value="{{ old('subject_name') }}" style="width:150px;"
                                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <input type="text" name="course_code" placeholder="MATH101" value="{{ old('course_code') }}" class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <input type="text" name="teacher_code" placeholder="T001" value="{{ old('teacher_code') }}" class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <input type="text" name="room_no" placeholder="Rm 101" value="{{ old('room_no') }}" class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
                        </td>
                        @if ($isSHS)
                            <td class="border border-[#dbe4f0] p-2.5">
                                <select name="subject_type"
                                        class="futuristic-select px-3 py-1.5 text-[13px]">
                                    <option value="Major">Major Subject</option>
                                    <option value="Applied">Applied Subject</option>
                                </select>
                            </td>
                        @else
                            <input type="hidden" name="subject_type" value="Major">
                        @endif
                        <td class="border border-[#dbe4f0] p-2.5">
                            <button type="submit" class="rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] px-4 py-2 font-semibold text-white shadow-[0_4px_12px_-4px_rgba(16,185,129,0.7)] transition hover:brightness-110">Add</button>
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
    <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Subject</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Code</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Room</th>
                    @if ($isSHS)
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Type</th>
                    @endif
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
                        @if ($isSHS)
                            <td class="border border-[#dbe4f0] p-2.5">
                                @php
                                    $st = $s->subject_type ?? 'Major';
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[12px] font-medium
                                    {{ $st === 'Applied' ? 'bg-orange-100/50 text-orange-700' : 'bg-sky-100/50 text-sky-700' }}">
                                    {{ $st === 'Applied' ? 'Applied' : 'Major' }}
                                </span>
                            </td>
                        @endif
                        <td class="border border-[#dbe4f0] p-2.5">
                            <form method="POST" action="{{ route('teacher.advisory-portal.destroy', $s->id) }}" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        data-confirm="Delete this subject? It will be removed from ALL students in your advisory."
                                        data-confirm-title="Delete Subject"
                                        data-confirm-text="Delete"
                                        class="rounded-md border border-[#b91c1c] bg-[#dc2626] px-2.5 py-1 text-[12px] font-semibold text-white transition hover:bg-[#b91c1c]">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isSHS ? 6 : 5 }}" class="p-8 text-center text-[#6b7280]">No subjects yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>