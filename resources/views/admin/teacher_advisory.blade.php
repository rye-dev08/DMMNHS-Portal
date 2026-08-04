<x-layouts.app :title="'Teacher Advisory'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Teacher Advisory</h2>
        </div>
        <div class="flex items-center gap-2.5">
            <form method="GET" action="{{ route('admin.teacher-advisory') }}" class="flex items-center gap-2">
                <label for="filter" class="text-[12px] font-medium text-[#0a1633]/60">Filter:</label>
                <select name="filter" id="filter" onchange="this.form.submit()"
                        class="rounded-lg border border-[#0018f9]/20 bg-white px-3 py-1.5 text-[13px] text-[#0a1633] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All</option>
                    <option value="jhs" {{ $filter === 'jhs' ? 'selected' : '' }}>Junior High (7-10)</option>
                    <option value="shs" {{ $filter === 'shs' ? 'selected' : '' }}>Senior High (11-12)</option>
                </select>
            </form>
            <a href="{{ route('admin.assign-class') }}"
               class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Assign Class</a>
            <a href="{{ route('admin.enrollment-settings') }}"
               class="rounded-lg bg-gradient-to-r from-[#6b7280] to-[#4b5563] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(75,85,99,0.6)] transition hover:brightness-110">Term &amp; Enrollment</a>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <table class="w-full border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Level</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Section</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Advisory Class</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teachers as $t)
                    @php
                        $badgeColor = $t->level === 'JHS' ? 'bg-blue-100 text-blue-800' : ($t->level === 'SHS' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-600');
                    @endphp
                    <tr class="{{ $loop->even ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 font-medium text-[#0a1633]">{{ $t->name }}</td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            @if ($t->grade_level)
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[12px] font-semibold {{ $badgeColor }}">
                                    {{ $t->level }} {{ $t->grade_level }}
                                </span>
                            @else
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[12px] font-semibold {{ $badgeColor }}">
                                    Unassigned
                                </span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-[#0a1633]/70">
                            {{ $t->section_name ?? '—' }}
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-[#0a1633]/90">
                            {{ $t->advisory_class ?? '—' }}
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-center">
                            <a href="{{ route('admin.assign-class', ['teacher_id' => $t->user_id]) }}"
                               class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-3 py-1.5 text-[12px] font-semibold text-white no-underline shadow-[0_2px_8px_-2px_rgba(0,24,249,0.5)] transition hover:brightness-110">
                                Assign
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-[#6b7280]">No active teachers.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
