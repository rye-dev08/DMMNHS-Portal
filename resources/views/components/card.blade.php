@props(['title' => '', 'footer' => ''])
<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] backdrop-blur-sm']) }}>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
    <div class="pointer-events-none absolute right-2 top-2 h-4 w-4 rounded-tr-lg border-r border-t border-[#38bdf8]/40"></div>
    <div class="pointer-events-none absolute bottom-2 left-2 h-4 w-4 rounded-bl-lg border-b border-l border-[#38bdf8]/40"></div>

    <div class="relative p-3.5">
        @if ($title !== '')
            <h4 class="mt-0 mb-2 text-[15px] font-semibold text-[#0a1633]">{{ $title }}</h4>
        @endif
        {{ $slot }}
        @if ($footer !== '')
            <div class="mt-2 text-[13px] text-slate-500">{{ $footer }}</div>
        @endif
    </div>
</div>