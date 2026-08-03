@props(['tagline' => 'Shaping Minds. Building Futures.'])
<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <img src="{{ asset('images/dmnhs-no-bg.jpg') }}" alt="School logo"
         class="h-10 w-10 shrink-0 rounded-md bg-white/90 object-contain p-0.5 shadow-sm">
    <span class="text-[10px] font-light uppercase tracking-[0.28em] text-white/70">{{ $tagline }}</span>
</div>
