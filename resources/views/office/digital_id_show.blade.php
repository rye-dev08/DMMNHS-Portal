<x-layouts.app :title="'Digital ID – '.$student->user->name">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Digital ID — {{ $student->user->name }}</h2>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('verify.student', $token) }}" target="_blank" rel="noopener"
               class="rounded-lg border border-[#0018f9]/25 bg-white px-4 py-2 text-[13px] font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                Open Verification Page
            </a>
            <a href="{{ route('office.digital-ids') }}"
               class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2 text-[13px] font-semibold text-white transition hover:brightness-110">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,430px)_1fr] lg:items-start">
        <div>
            @include('digital_id.card', [
                'student' => $student,
                'studentIdNo' => $studentIdNo,
                'advisory' => $advisory,
                'schoolYear' => $schoolYear,
                'term' => $term,
                'status' => $status,
                'qrSvg' => $qrSvg,
            ])
        </div>

        <div class="space-y-5">
            <x-card title="QR Verification Details">
                <div class="grid gap-2 text-[13.5px] text-slate-600">
                    <p><span class="font-semibold text-[#0a1633]">Verification URL:</span></p>
                    <p class="break-all rounded-lg border border-[#0018f9]/10 bg-[#f4f8ff] p-2.5 text-[12px] text-slate-500">{{ $verifyUrl }}</p>
                    <p>
                        <span class="font-semibold text-[#0a1633]">Token generated:</span>
                        {{ $tokenGeneratedAt ? $tokenGeneratedAt->format('M d, Y g:i A') : 'Not set' }}
                    </p>
                    <p>
                        <span class="font-semibold text-[#0a1633]">Status:</span>
                        {{ $status['label'] }}
                    </p>
                </div>
            </x-card>

            <x-card title="Management">
                <p class="text-[13.5px] leading-relaxed text-slate-600">
                    Regenerating creates a new secure token and invalidates the previous QR code.
                    Revoking disables verification until a new token is generated.
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('office.digital-ids.regenerate', $student->id) }}" class="m-0">
                        @csrf
                        <button type="submit"
                                data-confirm="Regenerate this verification token? The current QR code will be invalidated immediately."
                                data-confirm-title="Regenerate Token"
                                data-confirm-text="Regenerate"
                                class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-[13px] font-semibold text-sky-700 transition hover:bg-sky-100">
                            Regenerate Token
                        </button>
                    </form>
                    <form method="POST" action="{{ route('office.digital-ids.revoke', $student->id) }}" class="m-0">
                        @csrf
                        <button type="submit"
                                data-confirm="Revoke this verification token? The ID can no longer be verified until regenerated."
                                data-confirm-title="Revoke Token"
                                data-confirm-text="Revoke"
                                class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-[13px] font-semibold text-red-600 transition hover:bg-red-100">
                            Revoke Token
                        </button>
                    </form>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
