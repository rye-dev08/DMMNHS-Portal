<x-layouts.guest :title="'Student ID Verification'">
    @php
        $state = $data['state'] ?? 'invalid';
        $states = [
            'valid' => [
                'title' => '✓ Verified Student',
                'message' => 'This student ID is currently valid.',
                'text' => 'text-emerald-700',
                'banner' => 'border-emerald-300 bg-emerald-50',
                'icon' => 'check',
            ],
            'inactive' => [
                'title' => 'ID Inactive',
                'message' => 'This student ID is no longer active.',
                'text' => 'text-red-700',
                'banner' => 'border-red-300 bg-red-50',
                'icon' => 'cross',
            ],
            'not_enrolled' => [
                'title' => 'Not Currently Enrolled',
                'message' => 'This student does not have an active enrollment for the current academic period.',
                'text' => 'text-amber-700',
                'banner' => 'border-amber-300 bg-amber-50',
                'icon' => 'alert',
            ],
            'invalid' => [
                'title' => 'Invalid ID',
                'message' => 'This student ID could not be verified.',
                'text' => 'text-slate-600',
                'banner' => 'border-slate-300 bg-slate-100',
                'icon' => 'question',
            ],
        ];
        $cfg = $states[$state] ?? $states['invalid'];
        $student = $data['student'] ?? null;
        $verifiedAt = isset($data['verified_at']) ? $data['verified_at']->format('F j, Y g:i A') : now()->format('F j, Y g:i A');
        $photoUrl = $student?->photo ? asset('storage/'.$student->photo) : null;
    @endphp

    <div class="flex min-h-screen items-center justify-center bg-[#f6f8fc] px-4 py-10">
        <div class="w-full max-w-sm">
            {{-- Header --}}
            <div class="mb-6 text-center">
                <img src="{{ asset('images/dmnhs-no-bg.jpg') }}" alt="School Logo"
                     class="mx-auto h-16 w-16 rounded-2xl border border-[#0018f9]/15 object-cover shadow-[0_0_24px_rgba(0,24,249,0.25)]">
                <h1 class="mt-3 text-[15px] font-bold text-[#0a1633]">DMMNHS Student Portal</h1>
                <p class="text-[12px] font-medium text-slate-500">Official Student ID Verification</p>
            </div>

            {{-- Result card --}}
            <div class="overflow-hidden rounded-2xl border border-[#0018f9]/15 bg-white shadow-[0_18px_50px_-12px_rgba(2,6,23,0.25)]">
                <div class="{{ $cfg['banner'] }} px-6 py-5 text-center">
                    @if ($cfg['icon'] === 'check')
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500 shadow-[0_0_20px_rgba(16,185,129,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="white" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </div>
                    @elseif ($cfg['icon'] === 'cross')
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-500 shadow-[0_0_20px_rgba(239,68,68,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="white" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </div>
                    @elseif ($cfg['icon'] === 'alert')
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-500 shadow-[0_0_20px_rgba(245,158,11,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                        </div>
                    @else
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-500 shadow-[0_0_20px_rgba(100,116,139,0.5)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg>
                        </div>
                    @endif
                    <h2 class="mt-3 text-[18px] font-extrabold tracking-wide {{ $cfg['text'] }}">{{ $cfg['title'] }}</h2>
                    <p class="mt-1 text-[13px] leading-relaxed text-slate-600">{{ $cfg['message'] }}</p>
                </div>

                @if ($state !== 'invalid' && $student)
                    <div class="px-6 py-5 text-center">
                        <div class="mx-auto flex h-24 w-20 items-center justify-center overflow-hidden rounded-xl border-2 border-[#0018f9]/20 bg-slate-100">
                            @if ($photoUrl)
                                <img src="{{ $photoUrl }}" alt="Student Photo" class="h-full w-full object-cover">
                            @else
                                <span class="text-2xl font-bold text-[#0018f9]/35">{{ strtoupper(mb_substr((string) $student->user->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <p class="mt-3 text-[16px] font-bold text-[#0a1633]">{{ $student->user->name }}</p>
                        <p class="mt-0.5 font-mono text-[13px] font-semibold tracking-wide text-[#0018f9]">
                            Student ID: {{ $student->student_id_no ?: '—' }}
                        </p>

                        <div class="mx-auto mt-4 grid max-w-[260px] gap-1.5 text-[13px] text-slate-600">
                            <p>
                                Grade <span class="font-semibold text-[#0a1633]">{{ $data['grade'] }}</span>
                                @if (! empty($data['section']))
                                    – <span class="font-semibold text-[#0a1633]">{{ $data['section'] }}</span>
                                @endif
                            </p>
                            @if (! empty($data['track']))
                                <p class="text-[12.5px]"><span class="font-semibold text-[#0a1633]">Strand:</span> {{ $data['track'] }}</p>
                            @endif
                            <p><span class="font-semibold text-[#0a1633]">School Year:</span> {{ $data['school_year'] ?: '—' }}</p>
                            <p><span class="font-semibold text-[#0a1633]">Semester:</span> Term {{ $data['term'] }}</p>
                        </div>

                        <span class="mt-4 inline-flex items-center gap-1.5 rounded-full border border-[#0018f9]/15 bg-[#0018f9]/5 px-3 py-1 text-[12px] font-bold uppercase tracking-wider text-[#0018f9]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#0018f9]"></span>
                            {{ $data['status_label'] }}
                        </span>
                    </div>
                @endif

                <div class="border-t border-slate-100 bg-[#fafbfe] px-4 py-3 text-center text-[11.5px] text-slate-400">
                    Verified: {{ $verifiedAt }}
                </div>
            </div>

            <p class="mt-5 text-center text-[11.5px] leading-relaxed text-slate-400">
                For identity verification purposes only. Scan the QR code on a student's digital ID to confirm their status.
            </p>
        </div>
    </div>
</x-layouts.guest>
