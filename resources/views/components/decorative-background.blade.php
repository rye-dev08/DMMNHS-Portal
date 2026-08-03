@props(['gridSize' => '44px'])
<div {{ $attributes->merge(['class' => 'pointer-events-none absolute inset-0 overflow-hidden']) }}>
    <div class="absolute inset-0"
         style="background-image: linear-gradient(rgba(148, 197, 255, 0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(148, 197, 255, 0.06) 1px, transparent 1px); background-size: {{ $gridSize }} {{ $gridSize }};">
    </div>

    <div class="absolute left-[12%] top-[18%] h-1.5 w-1.5 rounded-full bg-[#7db4ff] opacity-50 shadow-[0_0_14px_4px_rgba(125,180,255,0.5)]"></div>
    <div class="absolute right-[16%] top-[38%] h-1 w-1 rounded-full bg-[#7db4ff] opacity-40 shadow-[0_0_12px_3px_rgba(125,180,255,0.45)]"></div>
    <div class="absolute bottom-[30%] left-[22%] h-1 w-1 rounded-full bg-[#7db4ff] opacity-35 shadow-[0_0_10px_2px_rgba(125,180,255,0.4)]"></div>
    <div class="absolute right-[28%] top-[62%] h-1.5 w-1.5 rounded-full bg-[#7db4ff] opacity-40 shadow-[0_0_14px_4px_rgba(125,180,255,0.45)]"></div>
    <div class="absolute bottom-[16%] right-[10%] h-1 w-1 rounded-full bg-[#7db4ff] opacity-45 shadow-[0_0_12px_3px_rgba(125,180,255,0.5)]"></div>

    <div class="absolute left-[8%] bottom-[38%] h-px w-24 bg-gradient-to-r from-transparent to-white/25"></div>
    <div class="absolute right-[6%] top-[30%] h-px w-32 bg-gradient-to-l from-transparent to-white/20"></div>
    <div class="absolute left-[40%] top-[8%] h-px w-16 bg-gradient-to-r from-transparent to-white/20"></div>
    <div class="absolute left-[60%] top-[72%] h-20 w-px bg-gradient-to-b from-transparent to-white/20"></div>

    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(37,99,235,0.16),transparent_55%)]"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(56,189,248,0.10),transparent_50%)]"></div>
</div>
