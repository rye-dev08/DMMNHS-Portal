@props([
    'student',
    'studentIdNo',
    'advisory',
    'schoolYear',
    'term',
    'status',
    'qrSvg',
])

@php
    $statusState = $status['state'] ?? 'inactive';
    $statusLabel = $status['label'] ?? 'Inactive';
    $grade = $advisory['grade'] ?? (int) ($student->grade_level ?? 0);
    $section = $advisory['section'] ?? null;
    $track = $advisory['track'] ?? null;
    $photoUrl = $student->photo ? asset('storage/'.$student->photo) : null;
    $initials = collect(explode(' ', trim((string) $student->user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
        ->implode('');

    $statusColors = [
        'valid' => ['text' => 'text-emerald-700', 'bg' => 'bg-emerald-100/80', 'border' => 'border-emerald-300', 'dot' => 'bg-emerald-500'],
        'not_enrolled' => ['text' => 'text-amber-700', 'bg' => 'bg-amber-100/80', 'border' => 'border-amber-300', 'dot' => 'bg-amber-500'],
        'inactive' => ['text' => 'text-red-700', 'bg' => 'bg-red-100/80', 'border' => 'border-red-300', 'dot' => 'bg-red-500'],
    ];
    $sc = $statusColors[$statusState] ?? $statusColors['inactive'];
@endphp

<div class="mx-auto w-full max-w-md overflow-hidden rounded-2xl border border-[#0018f9]/25 bg-white shadow-[0_18px_50px_-12px_rgba(0,24,249,0.35)]">
    {{-- Card header --}}
    <div class="relative flex items-center justify-between bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] px-5 py-4">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_10%_0%,rgba(56,189,248,0.35),transparent_45%)]"></div>
        <div class="relative flex items-center gap-3">
            <img src="{{ asset('images/dmnhs-no-bg.jpg') }}" alt="School Logo"
                 class="h-10 w-10 rounded-lg border border-white/30 bg-white/10 object-cover shadow-[0_0_16px_rgba(45,125,248,0.45)]">
            <div>
                <p class="text-[15px] font-bold leading-tight text-white">DMMNHS</p>
                <p class="text-[10px] tracking-[0.14em] text-white/60">STUDENT ID</p>
            </div>
        </div>
        <span class="relative inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10.5px] font-bold uppercase tracking-wider {{ $sc['border'] }} {{ $sc['bg'] }} {{ $sc['text'] }}">
            <span class="h-1.5 w-1.5 rounded-full {{ $sc['dot'] }}"></span>
            {{ $statusLabel }}
        </span>
    </div>

    {{-- Card body --}}
    <div class="p-5">
        <div class="flex gap-4">
            <div class="h-28 w-24 shrink-0 overflow-hidden rounded-xl border-2 border-[#0018f9]/20 bg-slate-100 shadow-inner">
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Student Photo" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#e8f0ff] to-[#dbe8ff] text-3xl font-bold text-[#0018f9]/35">{{ $initials }}</div>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[16px] font-bold leading-snug text-[#0a1633]">{{ $student->user->name }}</p>
                <p class="mt-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Student ID No.</p>
                <p class="font-mono text-[15px] font-bold tracking-wide text-[#0018f9]">{{ $studentIdNo }}</p>
                <div class="mt-2.5 space-y-1 text-[13px] text-slate-600">
                    <p>
                        <span class="font-semibold text-[#0a1633]">Grade {{ $grade }}</span>
                        @if ($section)
                            <span class="text-slate-400">•</span> {{ $section }}
                        @endif
                    </p>
                    @if ($track)
                        <p class="text-[12.5px]"><span class="font-semibold text-[#0a1633]">Strand:</span> {{ $track }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-2 rounded-lg border border-[#0018f9]/10 bg-[#f4f8ff] p-3 text-center">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">School Year</p>
                <p class="text-[13.5px] font-bold text-[#0a1633]">{{ $schoolYear ?: '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">Semester</p>
                <p class="text-[13.5px] font-bold text-[#0a1633]">Term {{ $term }}</p>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-4 rounded-lg border border-[#0018f9]/10 bg-white p-3">
            <div class="h-[110px] w-[110px] shrink-0 rounded-md border border-[#0018f9]/15 bg-white p-1.5">
                {!! $qrSvg !!}
            </div>
            <div class="min-w-0">
                <p class="text-[12.5px] font-bold text-[#0a1633]">Scan to Verify</p>
                <p class="mt-1 text-[11.5px] leading-relaxed text-slate-500">
                    Present this QR to authorized personnel to verify your current enrollment status.
                </p>
            </div>
        </div>
    </div>
</div>
