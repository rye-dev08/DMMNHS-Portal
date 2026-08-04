<x-layouts.app :title="'Grades'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">My Grades</h2>
        </div>
        <a href="{{ route('student.dashboard') }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Dashboard</a>
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="m-0 text-[13px] text-[#0a1633]/60">
            @if ($viewingHistory)
                Viewing archived grades from <strong>{{ $schoolYear }} &middot; Term {{ $currentTerm }}</strong>.
            @else
                Term <strong>{{ $currentTerm }}</strong> &middot; subjects and grades shown for the active/current term only.
            @endif
        </p>

        @if ($archivedPeriods->isNotEmpty())
            <form method="GET" action="{{ route('student.grades') }}" class="flex items-center gap-2">
                <select name="term" id="archive-term"
                        class="rounded-lg border border-[#0018f9]/20 bg-white px-3 py-1.5 text-[13px] text-[#0a1633] focus:outline-none focus:ring-2 focus:ring-[#0018f9]/40">
                    <option value="" {{ ! $viewingHistory ? 'selected' : '' }}>Current term</option>
                    @foreach ($archivedPeriods as $ap)
                        <option value="{{ $ap->t }}" {{ $viewingHistory && (int) $selectedTerm === (int) $ap->t && $selectedYear === $ap->y ? 'selected' : '' }}
                                data-year="{{ $ap->y }}">
                            {{ $ap->y }} &middot; Term {{ $ap->t }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="year" id="archive-year" value="{{ $viewingHistory ? $selectedYear : '' }}">
            </form>
        @endif
    </div>

    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <table class="w-full border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Subject</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Grade</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $i => $g)
                    @php $mapped = \App\Support\GradeFormatter::display($g->grade); @endphp
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 font-medium text-[#0a1633]">{{ $g->subject_name }}</td>
                        <td class="border border-[#dbe4f0] p-2.5 text-center">
                            <span style="background-color: {{ $mapped['color'] }}; color: white;"
                                  class="inline-block min-w-[50px] rounded-md px-3 py-1.5 font-bold shadow-sm">{{ $mapped['label'] }}</span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $g->remarks }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-10 text-center text-[#6b7280]">
                            @if ($viewingHistory)
                                No archived grades for {{ $schoolYear }} &middot; Term {{ $currentTerm }}.<br>
                                <small>This term has no recorded grades yet.</small>
                            @else
                                No subjects or grades for Term {{ $currentTerm }} yet.<br>
                                <small>Ask your teacher/adviser to assign subjects first.</small>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="relative mt-5 overflow-hidden rounded-xl border border-[#0018f9]/15 bg-gradient-to-r from-[#0a1633]/5 to-[#164aa8]/10 p-5 text-center shadow-[0_8px_24px_-10px_rgba(0,24,249,0.2)]">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
        <h3 class="m-0 text-[14px] font-semibold uppercase tracking-widest text-[#0018f9]">General Weighted Average</h3>
        <p class="mt-2 m-0 text-4xl font-bold">
            @if ($gwa !== null)
                <span class="text-[#0a1633]">{{ $gwa }}</span>
            @else
                <span class="text-[#6b7280]">No grades available</span>
            @endif
        </p>
    </div>

    <script>
        (function () {
            var yearInput = document.getElementById('archive-year');
            var select = document.getElementById('archive-term');
            if (!yearInput || !select) return;
            select.addEventListener('change', function () {
                var opt = select.options[select.selectedIndex];
                yearInput.value = opt.getAttribute('data-year') || '';
                select.form.submit();
            });
        })();
    </script>
</x-layouts.app>