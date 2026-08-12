<x-layouts.app :title="'Important Dates'">
    @php
        $backUrl = url()->previous() !== url()->full()
            ? url()->previous()
            : match (auth()->user()->role) {
                'teacher' => route('teacher.dashboard'),
                'office_admin' => route('office.dashboard'),
                default => route('student.dashboard'),
            };
    @endphp
    <div class="mb-4 flex items-center justify-between gap-3">
        <h3 class="m-0 flex items-center gap-2 text-[18px] font-semibold text-[#0a1633]">
            <span class="inline-block h-5 w-1 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            Important Dates
        </h3>
        <a href="{{ $backUrl }}"
           class="rounded-lg border border-[#0018f9]/15 bg-white/80 px-3 py-1.5 text-[12.5px] font-semibold text-[#0a1633] no-underline shadow-[0_4px_12px_-6px_rgba(0,24,249,0.2)] transition hover:bg-[#0018f9]/5">
            &larr; Back
        </a>
    </div>

    <p class="mb-4 text-[13px] text-slate-500">
        Upcoming events and deadlines gathered from the academic calendar and requirement tracker.
    </p>

    @if ($items->isEmpty())
        <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 px-4 py-10 text-center shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] backdrop-blur-sm">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
            <p class="m-0 text-[14px] font-medium text-slate-600">No upcoming dates.</p>
            <p class="mt-1 text-[12.5px] text-slate-400">New calendar events and requirement deadlines will show up here automatically.</p>
        </div>
    @else
        <div class="grid gap-3">
            @foreach ($items as $item)
                @php
                    $urgencyStyles = [
                        'urgent' => ['dot' => 'bg-rose-500', 'text' => 'text-rose-600', 'date' => 'border-rose-200 bg-rose-50 text-rose-700'],
                        'soon' => ['dot' => 'bg-amber-500', 'text' => 'text-amber-600', 'date' => 'border-amber-200 bg-amber-50 text-amber-700'],
                        'normal' => ['dot' => 'bg-slate-400', 'text' => 'text-slate-500', 'date' => 'border-slate-200 bg-slate-50 text-slate-600'],
                    ];
                    $style = $urgencyStyles[$item->urgency] ?? $urgencyStyles['normal'];
                    $isRequirement = $item->type === 'requirement';
                @endphp
                <a href="{{ $item->url }}"
                   class="group relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 no-underline shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] backdrop-blur-sm transition hover:-translate-y-0.5 hover:shadow-[0_12px_30px_-10px_rgba(0,24,249,0.3)]">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
                    <div class="flex items-start gap-3.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#0018f9]/8 text-[#0018f9]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5">
                                @if ($isRequirement)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                @endif
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="block truncate text-[15px] font-semibold text-[#0a1633] transition group-hover:text-[#2563eb]">{{ $item->title }}</span>
                                <span class="shrink-0 rounded-md border px-2 py-0.5 text-[12px] font-semibold {{ $style['date'] }}">
                                    {{ $item->date->format('M d, Y') }}
                                </span>
                            </div>
                            <span class="mt-1 flex items-center gap-1.5 text-[12px] font-medium {{ $style['text'] }}">
                                <span class="inline-block h-1.5 w-1.5 shrink-0 rounded-full {{ $style['dot'] }}"></span>
                                <span class="truncate">{{ $item->relative }} · {{ $item->subtitle }}</span>
                            </span>
                            @if (! empty($item->detail))
                                <p class="m-0 mt-1.5 line-clamp-2 text-[12.5px] leading-relaxed text-slate-500">{{ $item->detail }}</p>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
