<x-layouts.app :title="'Blocked Senders'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Blocked Senders</h2>
        </div>
        <a href="{{ route('office.message-center') }}"
           class="rounded-lg border border-[#0018f9]/20 bg-white px-4 py-2 text-[13.5px] font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
            Back to Message Center
        </a>
    </div>

    <div class="mb-5 overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[550px] border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">User</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Role</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Reason</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Blocked By</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Blocked At</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($blocks as $i => $block)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="block font-semibold text-[#0a1633]">{{ $block->user?->name ?? 'Unknown user' }}</span>
                            <span class="block text-[11.5px] text-slate-500">{{ $block->user?->email }}</span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[12px] font-medium capitalize {{ $block->user?->role === 'teacher'
                                ? 'border-violet-200 bg-violet-50 text-violet-700'
                                : 'border-sky-200 bg-sky-50 text-sky-700' }}">
                                {{ $block->user?->role ?? '-' }}
                            </span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-[12.5px] text-slate-600">
                            {{ $block->reason ?: 'No reason provided' }}
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-[12.5px] text-slate-600">
                            {{ $block->blocker?->name ?? 'Administrator' }}
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-[12.5px] text-slate-600">
                            {{ $block->blocked_at?->format('M d, Y g:i A') }}
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <form method="POST" action="{{ route('office.message-sender-blocks.unblock', $block->id) }}">
                                @csrf
                                <button type="submit"
                                        data-confirm="Unblock {{ $block->user?->name }}? They will be able to send messages again."
                                        data-confirm-title="Unblock Sender"
                                        data-confirm-text="Unblock"
                                        class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[12.5px] font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                    Unblock
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-[#6b7280]">
                            No blocked senders.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($blocks->hasPages())
            <div class="border-t border-[#dbe4f0] bg-white/70 px-4 py-3">
                {{ $blocks->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
