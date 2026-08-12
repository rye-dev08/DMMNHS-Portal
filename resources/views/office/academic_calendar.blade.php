@php
    $categoryLabels = \App\Models\AcademicCalendarEvent::CATEGORIES;
    $previewEventsJson = \App\Http\Controllers\AcademicCalendarController::eventsJson($previewEvents);
@endphp

<x-layouts.app :title="'Academic Calendar Management'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Academic Calendar</h2>
        </div>
        <button type="button"
                onclick="document.getElementById('create-calendar-event-modal').showModal()"
                class="rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] px-4 py-2 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(16,185,129,0.7)] transition hover:brightness-110">
            + Add Event
        </button>
    </div>

    <form method="GET" action="{{ route('office.academic-calendar') }}"
          class="mb-5 flex flex-wrap items-end gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="grid gap-1">
            <label for="school_year" class="text-[13px] font-medium text-[#475569]">School Year</label>
            <select id="school_year" name="school_year" class="futuristic-select px-3 py-2" onchange="this.form.submit()">
                @foreach ($years as $year)
                    <option value="{{ $year }}" {{ $filterYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid gap-1">
            <label for="term" class="text-[13px] font-medium text-[#475569]">Term</label>
            <select id="term" name="term" class="futuristic-select px-3 py-2" onchange="this.form.submit()">
                <option value="">All Terms</option>
                <option value="1" {{ $filterTerm === '1' ? 'selected' : '' }}>Term 1</option>
                <option value="2" {{ $filterTerm === '2' ? 'selected' : '' }}>Term 2</option>
                <option value="3" {{ $filterTerm === '3' ? 'selected' : '' }}>Term 3</option>
            </select>
        </div>
        <div class="grid gap-1">
            <label for="category" class="text-[13px] font-medium text-[#475569]">Category</label>
            <select id="category" name="category" class="futuristic-select px-3 py-2" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" {{ $filterCategory === $cat ? 'selected' : '' }}>{{ $categoryLabels[$cat] }}</option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('office.academic-calendar') }}"
           class="rounded-lg border border-[#0018f9]/20 bg-white px-4 py-2 font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">Reset</a>
    </form>

    <div class="mb-5 rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="m-0 text-[15px] font-bold text-[#0a1633]">Month Preview — {{ $previewMonthLabel }}</p>
                <p class="m-0 text-[12.5px] text-[#0a1633]/55">School Year {{ $filterYear }} &middot; click a day to view its events</p>
            </div>
            <div class="flex items-center gap-2">
                @if ($previewPrevUrl)
                    <a href="{{ $previewPrevUrl }}" class="rounded-lg border border-[#0018f9]/20 bg-white px-3 py-1.5 text-[13px] font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">← Prev</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-[#0018f9]/10 bg-white/50 px-3 py-1.5 text-[13px] font-semibold text-[#0a1633]/35">← Prev</span>
                @endif
                @if ($previewNextUrl)
                    <a href="{{ $previewNextUrl }}" class="rounded-lg border border-[#0018f9]/20 bg-white px-3 py-1.5 text-[13px] font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">Next →</a>
                @else
                    <span class="cursor-not-allowed rounded-lg border border-[#0018f9]/10 bg-white/50 px-3 py-1.5 text-[13px] font-semibold text-[#0a1633]/35">Next →</span>
                @endif
            </div>
        </div>

        @include('calendar.partials.month-grid', ['grid' => $previewGrid, 'dayEvents' => $previewEvents])
    </div>

    <div class="mb-5 overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <div class="overflow-x-auto">
        <table class="w-full border-collapse min-w-[750px] text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Date</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Event</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Category</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Time</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Location</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">SY / Term</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($events as $i => $event)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 font-semibold text-[#0a1633]">
                            {{ \Illuminate\Support\Carbon::parse($event->event_date)->format('M d, Y') }}
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="flex items-center gap-1.5 font-medium text-[#0a1633]">
                                <span class="h-2 w-2 shrink-0 rounded-full {{ academic_calendar_category_style((string) $event->category, 'dot') }}"></span>
                                {{ $event->title }}
                            </span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[12px] font-medium {{ academic_calendar_category_style((string) $event->category, 'badge') }}">
                                {{ $categoryLabels[$event->category] ?? $event->category }}
                            </span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">
                            @if ($event->start_time)
                                {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }}
                                @if ($event->end_time)
                                    – {{ \Illuminate\Support\Carbon::parse($event->end_time)->format('g:i A') }}
                                @endif
                            @else
                                <span class="text-[#94a3b8]">-</span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $event->location ?? '-' }}</td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="text-[13px] text-[#0a1633]/80">{{ $event->school_year }}</span>
                            <span class="ml-1 inline-flex items-center rounded-md bg-[#0018f9]/5 px-1.5 py-0.5 text-[11px] font-medium text-[#0018f9]">Term {{ $event->term }}</span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button"
                                        onclick="openEventDetails([{ title: {{ json_encode($event->title) }}, category: {{ json_encode($event->category) }}, badge: {{ json_encode(academic_calendar_category_style((string) $event->category, 'badge')) }}, dot: {{ json_encode(academic_calendar_category_style((string) $event->category, 'dot')) }}, start: {{ json_encode($event->start_time ? \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') : null) }}, end: {{ json_encode($event->end_time ? \Illuminate\Support\Carbon::parse($event->end_time)->format('g:i A') : null) }}, location: {{ json_encode($event->location) }}, short: {{ json_encode($event->short_description) }}, full: {{ json_encode($event->full_description) }}, school_year: {{ json_encode($event->school_year) }}, term: {{ (int) $event->term }} }], 'Event Details')"
                                        class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-3 py-1.5 text-[13px] font-semibold text-white transition hover:brightness-110">
                                    View
                                </button>
                                <button type="button"
                                        onclick="openEditModal({{ $event->id }})"
                                        class="rounded-lg border border-[#0018f9]/25 bg-white px-3 py-1.5 text-[13px] font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('office.academic-calendar.destroy', $event->id) }}" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            data-confirm="Delete this calendar event? This cannot be undone."
                                            data-confirm-title="Delete Calendar Event"
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
                        <td colspan="7" class="p-8 text-center text-[#6b7280]">
                            No calendar events match the current filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @include('office.edit_calendar_event_modal', ['event' => null, 'modalId' => 'create-calendar-event-modal', 'categories' => $categories, 'years' => $years])

    @include('calendar.partials.event-modal')

    <script>
        window.calendarEvents = {!! $previewEventsJson !!};

        function openEditModal(eventId) {
            fetch("{{ route('office.academic-calendar.edit', ':eventId') }}".replace(':eventId', eventId), {
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
                const existingModal = document.getElementById('edit-calendar-event-modal');
                if (existingModal) existingModal.remove();

                const modalContainer = document.createElement('div');
                modalContainer.innerHTML = html.trim();
                while (modalContainer.firstChild) {
                    document.body.appendChild(modalContainer.firstChild);
                }

                const modal = document.getElementById('edit-calendar-event-modal');
                if (modal && typeof modal.showModal === 'function') {
                    modal.showModal();
                } else if (modal) {
                    modal.style.display = 'flex';
                    modal.setAttribute('open', '');
                }
            })
            .catch(err => {
                console.error('Failed to load edit form:', err);
                showToast('Failed to load the edit form. Please refresh the page and try again.', 'error');
            });
        }

        @if (session('edit_event_id'))
            document.addEventListener('DOMContentLoaded', function () {
                openEditModal({{ (int) session('edit_event_id') }});
            });
        @elseif ($errors->any() && old('title'))
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('create-calendar-event-modal');
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
