@props(['items' => [], 'viewAllUrl' => '', 'limit' => 5])

@php
    $urgencyStyles = [
        'urgent' => ['dot' => 'bg-rose-500', 'text' => 'text-rose-600', 'date' => 'border-rose-200 bg-rose-50 text-rose-700'],
        'soon' => ['dot' => 'bg-amber-500', 'text' => 'text-amber-600', 'date' => 'border-amber-200 bg-amber-50 text-amber-700'],
        'normal' => ['dot' => 'bg-slate-400', 'text' => 'text-slate-500', 'date' => 'border-slate-200 bg-slate-50 text-slate-600'],
    ];
    $iconFor = function (string $type): string {
        if ($type === 'requirement') {
            return '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />';
        }
        return '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />';
    };
@endphp

<div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] backdrop-blur-sm">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
    <div class="pointer-events-none absolute right-2 top-2 h-4 w-4 rounded-tr-lg border-r border-t border-[#38bdf8]/40"></div>
    <div class="pointer-events-none absolute bottom-2 left-2 h-4 w-4 rounded-bl-lg border-b border-l border-[#38bdf8]/40"></div>

    <div class="relative p-3.5">
        <div class="mb-2 flex items-center justify-between gap-2">
            <h4 class="m-0 flex items-center gap-1.5 text-[15px] font-semibold text-[#0a1633]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 text-[#0018f9]">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                Important Dates
            </h4>
            @if ($viewAllUrl !== '' && $items->count() > $limit)
                <a href="{{ $viewAllUrl }}" class="text-[12px] font-semibold text-[#2563eb] no-underline transition hover:text-[#1d4ed8] hover:underline">
                    View All →
                </a>
            @endif
        </div>

        @if ($items->isEmpty())
            <p class="m-0 py-2 text-center text-[13px] text-slate-500">No upcoming dates.</p>
        @else
            <ul class="grid gap-1.5">
                @foreach ($items->take($limit) as $item)
                    @php
                        $style = $urgencyStyles[$item->urgency] ?? $urgencyStyles['normal'];
                    @endphp
                    <li>
                        <a href="{{ $item->url }}" class="group flex items-center gap-3 rounded-lg px-2 py-1.5 no-underline transition hover:bg-[#0018f9]/5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#0018f9]/8 text-[#0018f9]">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4">
                                    {!! $iconFor($item->type) !!}
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[13px] font-semibold text-[#0a1633] transition group-hover:text-[#2563eb]">{{ $item->title }}</span>
                                <span class="mt-0.5 flex items-center gap-1.5 text-[11.5px] {{ $style['text'] }}">
                                    <span class="inline-block h-1.5 w-1.5 shrink-0 rounded-full {{ $style['dot'] }}"></span>
                                    <span class="truncate">{{ $item->relative }} · {{ $item->subtitle }}</span>
                                </span>
                            </span>
                            <span class="shrink-0 rounded-md border px-1.5 py-0.5 text-[11px] font-semibold {{ $style['date'] }}">
                                {{ $item->date->format('M d') }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
