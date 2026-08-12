<x-layouts.app :title="'Digital Student ID'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Digital Student ID</h2>
        </div>
        <a href="{{ route('student.info') }}"
           class="rounded-lg border border-[#0018f9]/20 bg-white px-4 py-2 text-[13.5px] font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
            My Information
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,430px)_1fr] lg:items-start">
        {{-- ID card --}}
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

        {{-- Side panels --}}
        <div class="space-y-5">
            <x-card title="ID Status">
                @php
                    $stateNote = match ($status['state']) {
                        'valid' => 'Your digital ID is active and can be verified by scanning the QR code.',
                        'not_enrolled' => 'You do not have an approved enrollment for the current academic period, so your ID cannot be verified as active yet.',
                        default => 'This ID is currently inactive. Contact your administrator to reactivate it.',
                    };
                @endphp
                <p class="text-[13.5px] leading-relaxed text-slate-600">{{ $stateNote }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#0018f9]/15 bg-[#0018f9]/5 px-3 py-1 text-[12px] font-semibold text-[#0018f9]">
                        {{ $status['label'] }}
                    </span>
                    @if ($tokenGeneratedAt)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[12px] font-medium text-slate-500">
                            QR updated: {{ $tokenGeneratedAt->format('M d, Y g:i A') }}
                        </span>
                    @endif
                </div>
            </x-card>

            <x-card title="Verification Link">
                <p class="text-[13.5px] leading-relaxed text-slate-600">
                    Your QR code points to the secure verification page below. Anyone can scan it to confirm your
                    current academic status.
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <input type="text" readonly value="{{ $verifyUrl }}"
                           class="futuristic-select w-full min-w-0 px-3 py-2 text-[12px]">
                    <button type="button" data-copy-url="{{ $verifyUrl }}"
                            class="shrink-0 rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-3 py-2 text-[12.5px] font-semibold text-white transition hover:brightness-110">
                        Copy
                    </button>
                </div>
            </x-card>

            <x-card title="Profile Photo">
                <p class="text-[13.5px] leading-relaxed text-slate-600">
                    Upload a recent, clear headshot. It will appear on your digital ID card.
                </p>
                <form method="POST" action="{{ route('student.digital-id.photo') }}" enctype="multipart/form-data" class="mt-3 grid gap-3">
                    @csrf
                    <input type="file" name="photo" accept="image/*" required
                           class="block w-full rounded-lg border border-slate-200 bg-white p-2 text-[13px] text-slate-600 shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#0018f9]/10 file:px-3 file:py-1.5 file:text-[12.5px] file:font-semibold file:text-[#0018f9]">
                    @error('photo')
                        <span class="text-[12px] text-red-500">{{ $message }}</span>
                    @enderror
                    <button type="submit"
                            class="justify-self-start rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] px-4 py-2 text-[13px] font-semibold text-white shadow-[0_4px_14px_-4px_rgba(16,185,129,0.7)] transition hover:brightness-110">
                        Upload Photo
                    </button>
                </form>
            </x-card>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-copy-url]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    navigator.clipboard.writeText(btn.dataset.copyUrl)
                        .then(function () {
                            showToast('Verification link copied.', 'success');
                        })
                        .catch(function () {
                            showToast('Could not copy link.', 'error');
                        });
                });
            });
        });
    </script>
</x-layouts.app>
