<x-layouts.app :title="'Announcement Management'">
    @php
        $priorityLabels = \App\Models\Announcement::PRIORITIES;
        $statusBadge = function (string $status, bool $expired): string {
            if ($expired) {
                return 'border-amber-200 bg-amber-50 text-amber-700';
            }
            return $status === 'published'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                : 'border-slate-200 bg-slate-100 text-slate-600';
        };
    @endphp

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Announcement Management</h2>
        </div>
        <button type="button"
                onclick="document.getElementById('create-announcement-modal').showModal()"
                class="rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] px-4 py-2 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(16,185,129,0.7)] transition hover:brightness-110">
            + New Announcement
        </button>
    </div>

    <form method="GET" action="{{ route('office.announcements') }}"
          class="mb-5 rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="grid grid-cols-1 gap-x-3 gap-y-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-[1.35fr_1fr_1fr_1.05fr_0.9fr_1.2fr_1.2fr]">
            <div class="grid min-w-0 gap-1">
                <label for="q" class="text-[13px] font-medium text-[#475569]">Search</label>
                <input id="q" name="q" type="text" value="{{ $q }}" placeholder="Title..."
                       class="futuristic-select w-full min-w-0 px-3 py-2">
            </div>
            <div class="grid min-w-0 gap-1">
                <label for="status" class="text-[13px] font-medium text-[#475569]">Status</label>
                <select id="status" name="status" class="futuristic-select w-full min-w-0 px-3 py-2">
                    <option value="">All</option>
                    <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="unpublished" {{ $status === 'unpublished' ? 'selected' : '' }}>Unpublished</option>
                </select>
            </div>
            <div class="grid min-w-0 gap-1">
                <label for="audience" class="text-[13px] font-medium text-[#475569]">Audience</label>
                <select id="audience" name="audience" class="futuristic-select w-full min-w-0 px-3 py-2">
                    <option value="">All</option>
                    @foreach (\App\Models\Announcement::TARGET_ROLES as $key => $label)
                        <option value="{{ $key }}" {{ $audience === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid min-w-0 gap-1">
                <label for="school_year" class="text-[13px] font-medium text-[#475569]">School Year</label>
                <select id="school_year" name="school_year" class="futuristic-select w-full min-w-0 px-3 py-2">
                    <option value="">All</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" {{ $schoolYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid min-w-0 gap-1">
                <label for="term" class="text-[13px] font-medium text-[#475569]">Term</label>
                <select id="term" name="term" class="futuristic-select w-full min-w-0 px-3 py-2">
                    <option value="">All</option>
                    <option value="1" {{ $term === '1' ? 'selected' : '' }}>Term 1</option>
                    <option value="2" {{ $term === '2' ? 'selected' : '' }}>Term 2</option>
                    <option value="3" {{ $term === '3' ? 'selected' : '' }}>Term 3</option>
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

        <div class="mt-3 flex flex-wrap items-center justify-end gap-2 border-t border-[#0018f9]/10 pt-3">
            <a href="{{ route('office.announcements') }}"
               class="inline-flex items-center justify-center rounded-lg border border-[#0018f9]/20 bg-white px-4 py-2 text-center font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">Reset</a>
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2 text-center font-semibold text-white shadow-[0_4px_14px_-4px_rgba(0,24,249,0.7)] transition hover:brightness-110">
                Apply Filters
            </button>
        </div>
    </form>

    <div class="mb-5 overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[650px] border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Title</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Audience</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Priority</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Status</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Publish Date</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Expires</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">SY / Term</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($announcements as $i => $announcement)
                    @php
                        $expired = $announcement->hasExpired();
                        $refinementCount = $announcement->audiences->count();
                    @endphp
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="block font-semibold text-[#0a1633]">{{ $announcement->title }}</span>
                            @if ($announcement->short_summary)
                                <span class="block max-w-[260px] truncate text-[12px] text-slate-500">{{ $announcement->short_summary }}</span>
                            @endif
                            @if ($announcement->attachment)
                                <span class="mt-0.5 inline-flex items-center gap-1 text-[11px] text-[#0018f9]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3 w-3"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32" /></svg>
                                    {{ $announcement->attachment_name ?? 'attachment' }}
                                </span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="font-medium text-[#0a1633]">{{ $announcement->audienceBaseLabel() }}</span>
                            @if ($refinementCount > 0)
                                <span class="mt-0.5 block text-[11px] text-slate-400">{{ $refinementCount }} refinement{{ $refinementCount === 1 ? '' : 's' }}</span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[12px] font-medium {{ announcement_priority_style((string) $announcement->priority, 'badge') }}">
                                {{ $priorityLabels[$announcement->priority] ?? 'Normal' }}
                            </span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[12px] font-medium {{ $statusBadge((string) $announcement->status, $expired) }}">
                                {{ $expired ? 'Expired' : ucfirst($announcement->status) }}
                            </span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $announcement->publish_date?->format('M d, Y') }}</td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $announcement->expiration_date?->format('M d, Y') ?? '-' }}</td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="text-[13px] text-[#0a1633]/80">{{ $announcement->school_year }}</span>
                            <span class="ml-1 inline-flex items-center rounded-md bg-[#0018f9]/5 px-1.5 py-0.5 text-[11px] font-medium text-[#0018f9]">Term {{ $announcement->term }}</span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button"
                                        onclick="openAnnouncement({{ $announcement->id }})"
                                        class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-3 py-1.5 text-[13px] font-semibold text-white transition hover:brightness-110">
                                    View
                                </button>
                                <form method="POST" action="{{ route('office.announcements.toggle-status', $announcement->id) }}" class="m-0">
                                    @csrf
                                    <button type="submit"
                                            data-confirm="{{ $announcement->isPublished() ? 'Unpublish this announcement? It will no longer appear in the announcements feed.' : 'Publish this announcement? It will become visible to everyone in the feed.' }}"
                                            data-confirm-title="{{ $announcement->isPublished() ? 'Unpublish Announcement' : 'Publish Announcement' }}"
                                            data-confirm-text="{{ $announcement->isPublished() ? 'Unpublish' : 'Publish' }}"
                                            class="rounded-lg border px-3 py-1.5 text-[13px] font-semibold shadow-sm transition {{ $announcement->isPublished()
                                                ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                        {{ $announcement->isPublished() ? 'Unpublish' : 'Publish' }}
                                    </button>
                                </form>
                                <button type="button"
                                        onclick="openEditModal({{ $announcement->id }})"
                                        class="rounded-lg border border-[#0018f9]/25 bg-white px-3 py-1.5 text-[13px] font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('office.announcements.destroy', $announcement->id) }}" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            data-confirm="Delete this announcement? This cannot be undone."
                                            data-confirm-title="Delete Announcement"
                                            data-confirm-text="Delete"
                                            class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-[13px] font-semibold text-red-600 transition hover:bg-red-100">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-[#6b7280]">
                            No announcements match the current filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('office.edit_announcement_modal', [
        'announcement' => null,
        'modalId' => 'create-announcement-modal',
        'years' => $years,
        'sections' => $sections,
        'students' => $students,
        'teachers' => $teachers,
    ])

    @include('announcements.announcement-modal')

    <script>
        window.announcementsData = window.announcementsData || {};
        @foreach ($announcementsData as $announcementData)
            window.announcementsData[{{ $announcementData['id'] }}] = @json($announcementData);
        @endforeach

        function openEditModal(announcementId) {
            fetch("{{ route('office.announcements.edit', ':announcementId') }}".replace(':announcementId', announcementId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server responded with status ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                const existingModal = document.getElementById('edit-announcement-modal');
                if (existingModal) existingModal.remove();

                const modalContainer = document.createElement('div');
                modalContainer.innerHTML = html.trim();
                while (modalContainer.firstChild) {
                    document.body.appendChild(modalContainer.firstChild);
                }

                const modal = document.getElementById('edit-announcement-modal');
                if (modal && typeof modal.showModal === 'function') {
                    modal.showModal();
                } else if (modal) {
                    modal.style.display = 'flex';
                    modal.setAttribute('open', '');
                }

                const roleSelect = modal ? modal.querySelector('select[name="target_role"]') : null;
                if (roleSelect) toggleAnnouncementAudience(roleSelect);
            })
            .catch(err => {
                console.error('Failed to load edit form:', err);
                showToast('Failed to load the edit form. Please refresh the page and try again.', 'error');
            });
        }

        @if (session('edit_announcement_id'))
            document.addEventListener('DOMContentLoaded', function () {
                openEditModal({{ (int) session('edit_announcement_id') }});
            });
        @elseif ($errors->any() && old('title'))
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('create-announcement-modal');
                if (modal && typeof modal.showModal === 'function') {
                    modal.showModal();
                } else if (modal) {
                    modal.style.display = 'flex';
                    modal.setAttribute('open', '');
                }
            });
        @endif
    </script>
</x-layouts.app>
