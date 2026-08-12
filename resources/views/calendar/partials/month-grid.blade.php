@php
    $weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $rows = array_chunk($grid, 7);
    if (!empty($rows)) {
        $rows[count($rows) - 1] = array_pad($rows[count($rows) - 1], 7, null);
    }
@endphp

<div class="overflow-x-auto">
    <div class="min-w-[640px]">
        <div class="grid grid-cols-7 gap-1.5">
            @foreach ($weekDays as $wd)
                <div class="py-1 text-center text-[11px] font-bold uppercase tracking-wider text-[#0a1633]/55">{{ $wd }}</div>
            @endforeach
        </div>

        @foreach ($rows as $row)
            <div class="grid grid-cols-7 gap-1.5">
                @foreach ($row as $cell)
                    @php
                        $hasEvents = $cell !== null
                            && isset($dayEvents[$cell['key']])
                            && $dayEvents[$cell['key']]->count() > 0;
                    @endphp

                    @if ($cell === null)
                        <div class="min-h-[92px] rounded-lg border border-dashed border-[#0018f9]/10 bg-white/30"></div>
                    @else
                        <button type="button"
                                onclick="openDay('{{ $cell['key'] }}')"
                                title="{{ $hasEvents ? 'View events' : 'No update yet' }}"
                                class="group relative flex min-h-[92px] flex-col items-start gap-1 rounded-lg border p-1.5 text-left transition hover:border-[#0018f9]/50 hover:shadow-sm
                                       {{ $cell['isToday']
                                            ? 'border-[#0018f9] bg-[#0018f9]/5 ring-1 ring-[#0018f9]/40'
                                            : 'border-[#0018f9]/12 bg-white/70 hover:bg-[#f4f8ff]' }}">
                            <span class="absolute right-1.5 top-1.5 flex h-6 min-w-6 items-center justify-center rounded-full px-1 text-[11px] font-bold {{ $cell['isToday'] ? 'bg-gradient-to-br from-[#0018f9] to-[#0080fe] text-white shadow-[0_0_8px_rgba(0,24,249,0.45)]' : 'text-[#0a1633]/55' }}">
                                {{ $cell['day'] }}
                            </span>

                            @if ($hasEvents)
                                <div class="mt-7 flex w-full flex-col gap-1">
                                    @foreach ($dayEvents[$cell['key']]->take(2) as $event)
                                        <span class="flex items-center gap-1 truncate text-[11px] font-medium text-[#0a1633]/80">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ academic_calendar_category_style((string) $event->category, 'dot') }}"></span>
                                            <span class="truncate">{{ $event->title }}</span>
                                        </span>
                                    @endforeach
                                    @if ($dayEvents[$cell['key']]->count() > 2)
                                        <span class="text-[10.5px] font-semibold text-[#0018f9]">+{{ $dayEvents[$cell['key']]->count() - 2 }} more</span>
                                    @endif
                                </div>
                            @endif
                        </button>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
</div>
