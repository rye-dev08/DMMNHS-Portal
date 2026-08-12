<x-layouts.app :title="'Student Digital IDs'">
    @php
        $statusBadge = function (string $state) {
            return match ($state) {
                'valid' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'not_enrolled' => 'border-amber-200 bg-amber-50 text-amber-700',
                'inactive' => 'border-red-200 bg-red-50 text-red-700',
                default => 'border-slate-200 bg-slate-100 text-slate-600',
            };
        };
        $statusLabel = function (string $state) {
            return match ($state) {
                'valid' => 'Active',
                'not_enrolled' => 'Not Enrolled',
                'inactive' => 'Inactive',
                default => 'Unknown',
            };
        };
    @endphp

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Student Digital IDs</h2>
        </div>
    </div>

    <form method="GET" action="{{ route('office.digital-ids') }}"
          class="mb-5 rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="grid grid-cols-1 gap-x-3 gap-y-4 sm:grid-cols-2 xl:grid-cols-[1.4fr_1fr_auto]">
            <div class="grid min-w-0 gap-1">
                <label for="q" class="text-[13px] font-medium text-[#475569]">Search Student</label>
                <input id="q" name="q" type="text" value="{{ $q }}" placeholder="Name, username or ID number..."
                       class="futuristic-select w-full min-w-0 px-3 py-2">
            </div>
            <div class="grid min-w-0 gap-1">
                <label for="status" class="text-[13px] font-medium text-[#475569]">Status</label>
                <select id="status" name="status" class="futuristic-select w-full min-w-0 px-3 py-2">
                    <option value="">All Statuses</option>
                    <option value="valid" {{ $statusFilter === 'valid' ? 'selected' : '' }}>Active</option>
                    <option value="not_enrolled" {{ $statusFilter === 'not_enrolled' ? 'selected' : '' }}>Not Enrolled</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2 text-[13px] font-semibold text-white shadow-sm transition hover:brightness-110 sm:w-auto">
                    Filter
                </button>
            </div>
        </div>
        <div class="mt-3 flex justify-end border-t border-[#0018f9]/10 pt-3">
            <a href="{{ route('office.digital-ids') }}"
               class="inline-flex items-center justify-center rounded-lg border border-[#0018f9]/20 bg-white px-4 py-2 text-[13px] font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                Reset
            </a>
        </div>
    </form>

    <div class="mb-5 overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse min-w-[680px] text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Student</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Grade / Section</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Status</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">QR Token</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php $photoUrl = $row->student->photo ? asset('storage/'.$row->student->photo) : null; @endphp
                    <tr class="{{ $loop->even ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-[#0018f9]/15 bg-slate-100">
                                    @if ($photoUrl)
                                        <img src="{{ $photoUrl }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-[13px] font-bold text-[#0018f9]/35">
                                            {{ strtoupper(mb_substr((string) $row->user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-[#0a1633]">{{ $row->user->name }}</p>
                                    <p class="font-mono text-[12px] text-slate-500">{{ $row->student_id_no }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">
                            <span class="font-medium text-[#0a1633]">Grade {{ $row->grade }}</span>
                            @if ($row->section)
                                <span class="text-slate-400">–</span> {{ $row->section }}
                            @endif
                            @if ($row->track)
                                <span class="mt-0.5 block text-[11px] text-slate-400">{{ $row->track }}</span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[12px] font-medium {{ $statusBadge($row->status['state']) }}">
                                {{ $statusLabel($row->status['state']) }}
                            </span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            @if ($row->has_token)
                                <span class="inline-flex items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[12px] font-medium text-emerald-700">Issued</span>
                                @if ($row->token_generated_at)
                                    <span class="mt-1 block text-[11px] text-slate-400">Updated {{ $row->token_generated_at->format('M d, Y g:i A') }}</span>
                                @endif
                            @else
                                <span class="inline-flex items-center rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[12px] font-medium text-slate-500">Revoked / None</span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('office.digital-ids.show', $row->student->id) }}"
                                   class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-3 py-1.5 text-[13px] font-semibold text-white transition hover:brightness-110">
                                    View ID
                                </a>
                                @if ($row->has_token)
                                    <a href="{{ route('verify.student', $row->student->id_token) }}" target="_blank" rel="noopener"
                                       class="rounded-lg border border-[#0018f9]/25 bg-white px-3 py-1.5 text-[13px] font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                                        Verify
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('office.digital-ids.regenerate', $row->student->id) }}" class="m-0">
                                    @csrf
                                    <button type="submit"
                                            data-confirm="Regenerate this verification token? The current QR code will be invalidated immediately."
                                            data-confirm-title="Regenerate Token"
                                            data-confirm-text="Regenerate"
                                            class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-[13px] font-semibold text-sky-700 transition hover:bg-sky-100">
                                        Regenerate
                                    </button>
                                </form>
                                @if ($row->has_token)
                                    <form method="POST" action="{{ route('office.digital-ids.revoke', $row->student->id) }}" class="m-0">
                                        @csrf
                                        <button type="submit"
                                                data-confirm="Revoke this verification token? The ID can no longer be verified until regenerated."
                                                data-confirm-title="Revoke Token"
                                                data-confirm-text="Revoke"
                                                class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-[13px] font-semibold text-red-600 transition hover:bg-red-100">
                                            Revoke
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-[#6b7280]">
                            No student IDs match the current filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>

    <div>
        {{ $rows->links() }}
    </div>
</x-layouts.app>
