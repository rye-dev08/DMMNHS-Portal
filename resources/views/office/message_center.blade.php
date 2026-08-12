<x-layouts.app :title="'Message Center'">
    <div id="poll-messages">
        @php
            $statusStyle = function (string $status): string {
            return match ($status) {
                \App\Models\ContactMessage::STATUS_PENDING => 'border-amber-200 bg-amber-50 text-amber-700',
                \App\Models\ContactMessage::STATUS_VALID => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                default => 'border-red-200 bg-red-50 text-red-700',
            };
        };
        $roleStyle = function (?string $role): string {
            return match ($role) {
                'student' => 'border-sky-200 bg-sky-50 text-sky-700',
                'teacher' => 'border-violet-200 bg-violet-50 text-violet-700',
                default => 'border-slate-200 bg-slate-100 text-slate-600',
            };
        };
    @endphp

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Message Center</h2>
        </div>
        <a href="{{ route('office.message-center.blocked') }}"
           class="rounded-lg border border-[#0018f9]/20 bg-white px-4 py-2 text-[13.5px] font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
            Blocked Senders ({{ $summary->blockedSenders }})
        </a>
    </div>

    {{-- Summary cards --}}
    <div class="mb-6 grid grid-cols-2 gap-3.5 lg:grid-cols-4">
        <div class="relative overflow-hidden rounded-xl border border-amber-300/50 bg-white/80 p-4 shadow-[0_8px_20px_-10px_rgba(245,158,11,0.25)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-amber-400 to-yellow-300"></div>
            <p class="m-0 text-2xl font-bold text-amber-600">{{ $summary->pending }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Pending Review</p>
            <p class="mt-0.5 text-[11.5px] text-slate-400">{{ $summary->pending }} waiting for review</p>
        </div>
        <div class="relative overflow-hidden rounded-xl border border-emerald-300/50 bg-white/80 p-4 shadow-[0_8px_20px_-10px_rgba(16,185,129,0.25)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-emerald-400 to-teal-300"></div>
            <p class="m-0 text-2xl font-bold text-emerald-600">{{ $summary->valid }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Valid Messages</p>
        </div>
        <div class="relative overflow-hidden rounded-xl border border-red-300/50 bg-white/80 p-4 shadow-[0_8px_20px_-10px_rgba(239,68,68,0.25)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-red-400 to-rose-300"></div>
            <p class="m-0 text-2xl font-bold text-red-600">{{ $summary->invalid }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Invalid Messages</p>
        </div>
        <div class="relative overflow-hidden rounded-xl border border-slate-300/60 bg-white/80 p-4 shadow-[0_8px_20px_-10px_rgba(15,23,42,0.2)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-slate-500 to-slate-400"></div>
            <p class="m-0 text-2xl font-bold text-slate-700">{{ $summary->blockedSenders }}</p>
            <p class="mt-0.5 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Blocked Senders</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('office.message-center') }}"
          class="mb-5 rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="mb-3 flex flex-wrap items-center justify-end gap-2">
            <a href="{{ route('office.message-center') }}"
               class="inline-flex items-center justify-center whitespace-nowrap rounded-md border border-[#0018f9]/20 bg-white px-2.5 py-1.5 text-[12.5px] font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                Reset
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center whitespace-nowrap rounded-md bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-2.5 py-1.5 text-[12.5px] font-semibold text-white shadow-sm transition hover:brightness-110">
                Apply Filters
            </button>
        </div>
        <div class="grid grid-cols-1 gap-x-3 gap-y-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-[1.3fr_0.85fr_0.9fr_1fr_1fr_1fr]">
            <div class="grid min-w-0 gap-1">
                <label for="q" class="text-[13px] font-medium text-[#475569]">Search</label>
                <input id="q" name="q" type="text" value="{{ $q }}" placeholder="Sender, subject, message..."
                       class="futuristic-select w-full min-w-0 px-3 py-2">
            </div>
            <div class="grid min-w-0 gap-1">
                <label for="status" class="text-[13px] font-medium text-[#475569]">Status</label>
                <select id="status" name="status" class="futuristic-select w-full min-w-0 px-3 py-2">
                    <option value="">All</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="valid" {{ $status === 'valid' ? 'selected' : '' }}>Valid</option>
                    <option value="invalid" {{ $status === 'invalid' ? 'selected' : '' }}>Invalid</option>
                </select>
            </div>
            <div class="grid min-w-0 gap-1">
                <label for="role" class="text-[13px] font-medium text-[#475569]">Sender</label>
                <select id="role" name="role" class="futuristic-select w-full min-w-0 px-3 py-2">
                    <option value="">All</option>
                    <option value="student" {{ $role === 'student' ? 'selected' : '' }}>Student</option>
                    <option value="teacher" {{ $role === 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="guest" {{ $role === 'guest' ? 'selected' : '' }}>Guest / Visitor</option>
                </select>
            </div>
            <div class="grid min-w-0 gap-1">
                <label for="blocked" class="text-[13px] font-medium text-[#475569]">Blocked Sender</label>
                <select id="blocked" name="blocked" class="futuristic-select w-full min-w-0 px-3 py-2">
                    <option value="">All</option>
                    <option value="1" {{ $blockedOnly ? 'selected' : '' }}>Only from blocked senders</option>
                </select>
            </div>
            <div class="grid min-w-0 gap-1">
                <label for="date_from" class="text-[13px] font-medium text-[#475569]">From</label>
                <input id="date_from" name="date_from" type="date" value="{{ $dateFrom }}"
                       class="futuristic-select w-full min-w-0 px-3 py-2">
            </div>
            <div class="grid min-w-0 gap-1">
                <label for="date_to" class="text-[13px] font-medium text-[#475569]">To</label>
                <input id="date_to" name="date_to" type="date" value="{{ $dateTo }}"
                       class="futuristic-select w-full min-w-0 px-3 py-2">
            </div>
        </div>
    </form>

    {{-- Inbox --}}
    <div class="mb-5 overflow-x-auto rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <table class="w-full border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Sender</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Subject / Message</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Date Sent</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Status</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Retention</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $i => $message)
                    @php
                        $blocked = $message->user && in_array((int) $message->user_id, $blockedUserIds, true);
                    @endphp
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 align-top">
                            <span class="block font-semibold text-[#0a1633]">{{ $message->name }}</span>
                            <span class="mt-0.5 inline-flex items-center rounded-md border px-1.5 py-0.5 text-[11px] font-medium {{ $roleStyle($message->sender_role) }}">
                                {{ ucfirst($message->sender_role ?? 'guest') }}
                            </span>
                            @if (($senderInfo[(int) $message->user_id] ?? '') !== '')
                                <span class="mt-0.5 block text-[11.5px] text-slate-500">{{ $senderInfo[(int) $message->user_id] }}</span>
                            @endif
                            @if ($blocked)
                                <span class="mt-0.5 inline-flex items-center rounded-md border border-slate-700 bg-slate-800 px-1.5 py-0.5 text-[11px] font-semibold text-white">BLOCKED</span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 align-top">
                            <span class="block font-semibold text-[#0a1633]">{{ $message->subject ?: 'No subject' }}</span>
                            <span class="block max-w-[300px] truncate text-[12.5px] text-slate-500">{{ $message->message }}</span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 align-top text-[12.5px] text-slate-600">
                            {{ $message->created_at->format('M d, Y') }}
                            <span class="block text-[11px] text-slate-400">{{ $message->created_at->format('g:i A') }}</span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 align-top">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[12px] font-medium {{ $statusStyle((string) $message->status) }}">
                                {{ strtoupper($message->status) }}
                            </span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 align-top text-[12px] text-slate-600">
                            @if ($message->isInvalid() && $message->expires_at)
                                <span class="block font-medium text-red-600">Expires {{ $message->expires_at->format('M d, Y g:i A') }}</span>
                                <span class="block text-[11px] text-slate-400">Marked {{ $message->moderated_at?->format('M d, Y g:i A') }}</span>
                            @elseif ($message->moderated_at)
                                <span class="block text-[11px] text-slate-400">Moderated {{ $message->moderated_at->format('M d, Y g:i A') }}</span>
                            @else
                                <span class="text-[11px] text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 align-top">
                            <div class="grid w-full min-w-[190px] gap-1.5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <button type="button" onclick="openMessage({{ $message->id }})"
                                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-3 py-1.5 text-[12.5px] font-semibold text-white transition hover:brightness-110">
                                        View
                                    </button>
                                    @if ($message->isPending())
                                        <form method="POST" action="{{ route('office.messages.valid', $message->id) }}" class="m-0">
                                            @csrf
                                            <button type="submit"
                                                    data-confirm="Mark this message as valid? It will be delivered to the school administration inbox."
                                                    data-confirm-title="Mark as Valid"
                                                    data-confirm-text="Mark Valid"
                                                    class="inline-flex items-center justify-center whitespace-nowrap rounded-md border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[12.5px] font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                Mark Valid
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('office.messages.invalid', $message->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    data-confirm="Mark this message as invalid? It will be kept for 1 day, then removed from the active Message Center."
                                                    data-confirm-title="Mark as Invalid"
                                                    data-confirm-text="Mark Invalid"
                                                    data-danger
                                                    class="inline-flex items-center justify-center whitespace-nowrap rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-[12.5px] font-semibold text-red-600 transition hover:bg-red-100">
                                                Mark Invalid
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if ($message->user)
                                        <button type="button" data-block-id="{{ $message->id }}"
                                                data-block-name="{{ $message->name }}"
                                                class="inline-flex items-center justify-center whitespace-nowrap rounded-md border border-slate-300 bg-slate-100 px-3 py-1.5 text-[12.5px] font-semibold text-slate-700 transition hover:bg-slate-200">
                                            Block Sender
                                        </button>
                                    @endif
                                    <form method="POST" action="{{ route('office.messages.destroy', $message->id) }}" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                data-confirm="Delete this message? This cannot be undone."
                                                data-confirm-title="Delete Message"
                                                data-confirm-text="Delete"
                                                class="inline-flex items-center justify-center whitespace-nowrap rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-[12.5px] font-semibold text-red-600 transition hover:bg-red-100">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-[#6b7280]">
                            No messages match the current filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($messages->hasPages())
            <div class="border-t border-[#dbe4f0] bg-white/70 px-4 py-3">
                {{ $messages->links() }}
            </div>
        @endif
    </div>

    {{-- Message detail modal --}}
    <dialog id="message-detail-modal"
            class="m-auto w-[min(92vw,560px)] rounded-2xl border border-[#0018f9]/15 bg-white p-0 text-[#0a1633] shadow-[0_24px_60px_-20px_rgba(10,22,51,0.5)] backdrop:bg-slate-900/60 backdrop:backdrop-blur-sm">
        <div class="rounded-t-2xl border-b border-[#dbe4f0] bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] px-5 py-4 text-white">
            <div class="flex items-center justify-between gap-3">
                <h3 class="m-0 text-[16px] font-bold" id="modal-message-title">Message</h3>
                <button type="button" onclick="document.getElementById('message-detail-modal').close()"
                        class="rounded-lg bg-white/10 px-2.5 py-1 text-[12px] font-semibold text-white transition hover:bg-white/20">
                    Close
                </button>
            </div>
        </div>
        <div class="grid gap-4 px-5 py-4">
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">From</span>
                <p class="mt-0.5 text-[14px] font-semibold text-[#0a1633]" id="modal-message-sender"></p>
                <p class="text-[12.5px] text-slate-500" id="modal-message-meta"></p>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Subject</span>
                <p class="mt-0.5 text-[14px] font-medium text-[#0a1633]" id="modal-message-subject"></p>
            </div>
            <div>
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-400">Message</span>
                <p class="mt-0.5 whitespace-pre-wrap text-[14px] leading-relaxed text-slate-700" id="modal-message-body"></p>
            </div>
            <div class="flex items-center justify-between gap-3 rounded-lg border border-[#dbe4f0] bg-[#f8fbff] px-3.5 py-2.5">
                <span class="text-[12.5px] text-slate-600" id="modal-message-date"></span>
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[12px] font-semibold" id="modal-message-status"></span>
            </div>
        </div>
    </dialog>

    {{-- Block sender dialog --}}
    <dialog id="block-sender-modal"
            class="m-auto w-[min(92vw,460px)] rounded-2xl border border-[#0018f9]/15 bg-white p-0 text-[#0a1633] shadow-[0_24px_60px_-20px_rgba(10,22,51,0.5)] backdrop:bg-slate-900/60 backdrop:backdrop-blur-sm">
        <div class="rounded-t-2xl border-b border-[#dbe4f0] bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] px-5 py-4 text-white">
            <div class="flex items-center justify-between gap-3">
                <h3 class="m-0 text-[16px] font-bold">Block Sender</h3>
                <button type="button" onclick="document.getElementById('block-sender-modal').close()"
                        class="rounded-lg bg-white/10 px-2.5 py-1 text-[12px] font-semibold text-white transition hover:bg-white/20">
                    Close
                </button>
            </div>
        </div>
        <form method="POST" action="" id="block-sender-form" class="grid gap-4 px-5 py-4">
            @csrf
            <p class="m-0 text-[13.5px] leading-relaxed text-slate-600">
                This will block <span class="font-semibold text-[#0a1633]" id="block-sender-name"></span> from sending
                messages to the administration. Blocking is separate from marking a message invalid.
            </p>
            <div class="grid gap-1.5">
                <label for="block-reason" class="text-[13px] font-semibold text-[#0a1633]">Reason (optional)</label>
                <textarea id="block-reason" name="reason" rows="3" placeholder="Why is this sender being blocked?"
                          class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('block-sender-modal').close()"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" data-confirm="Block this sender from sending messages?"
                        data-confirm-title="Block Sender"
                        data-confirm-text="Block Sender"
                        data-danger
                        class="rounded-lg bg-gradient-to-r from-red-600 to-rose-600 px-4 py-2 text-[13px] font-semibold text-white shadow-[0_4px_14px_-4px_rgba(220,38,38,0.7)] transition hover:brightness-110">
                    Block Sender
                </button>
            </div>
        </form>
    </dialog>

    <script>
        window.messageData = window.messageData || {};
        @foreach ($messages as $message)
            @php
                $msgStatus = strtoupper((string) $message->status);
                $statusClasses = $statusStyle((string) $message->status);
            @endphp
            window.messageData[{{ $message->id }}] = {
                subject: @json($message->subject ?: 'No subject'),
                message: @json($message->message),
                sender: @json($message->name),
                role: @json(ucfirst($message->sender_role ?? 'guest')),
                email: @json($message->email),
                info: @json($senderInfo[(int) $message->user_id] ?? ''),
                date: @json($message->created_at->format('M d, Y g:i A')),
                status: @json($msgStatus),
                statusClasses: @json($statusClasses),
            };
        @endforeach

        function openMessage(id) {
            const data = window.messageData[id];
            if (!data) return;

            document.getElementById('modal-message-title').textContent = 'Message from ' + data.sender;
            document.getElementById('modal-message-sender').textContent = data.sender;
            document.getElementById('modal-message-meta').textContent = [
                data.role,
                data.info,
                data.email ? 'Email: ' + data.email : null
            ].filter(Boolean).join(' · ');
            document.getElementById('modal-message-subject').textContent = data.subject;
            document.getElementById('modal-message-body').textContent = data.message;
            document.getElementById('modal-message-date').textContent = 'Sent ' + data.date;

            const statusEl = document.getElementById('modal-message-status');
            statusEl.textContent = data.status;
            statusEl.className = 'inline-flex items-center rounded-md border px-2 py-0.5 text-[12px] font-semibold ' + data.statusClasses;

            const modal = document.getElementById('message-detail-modal');
            if (typeof modal.showModal === 'function') {
                modal.showModal();
            } else {
                modal.style.display = 'block';
                modal.setAttribute('open', '');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('block-sender-modal');
            document.querySelectorAll('[data-block-id]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = btn.dataset.blockId;
                    document.getElementById('block-sender-form').setAttribute('action',
                        "{{ route('office.messages.block', ':id') }}".replace(':id', id));
                    document.getElementById('block-sender-name').textContent = btn.dataset.blockName;
                    if (typeof modal.showModal === 'function') {
                        modal.showModal();
                    } else {
                        modal.style.display = 'block';
                        modal.setAttribute('open', '');
                    }
                });
            });
        });
    </script>
    </div>
</x-layouts.app>
