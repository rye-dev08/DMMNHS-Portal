<dialog id="event-details-modal" class="modal-modal event-details-modal">
    <div class="grid gap-4 p-6 max-w-lg">
        <div class="flex items-center justify-between gap-3">
            <h3 id="event-details-heading" class="m-0 text-[15px] font-semibold text-[#0a1633]">Event Details</h3>
            <button type="button" onclick="closeEventModal()" aria-label="Close"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#0018f9]/15 bg-white text-[#0a1633]/60 transition hover:bg-[#f4f8ff] hover:text-[#0a1633]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="event-details-body" class="grid gap-3 max-h-[60vh] overflow-y-auto pr-1"></div>
        <div class="flex justify-end">
            <button type="button" onclick="closeEventModal()"
                    class="rounded-lg border border-[#0018f9]/20 bg-white px-6 py-2 font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                Close
            </button>
        </div>
    </div>
</dialog>

<style>
    .event-details-modal {
        border: none;
        border-radius: 14px;
        box-shadow: 0 20px 50px -12px rgba(2, 6, 23, 0.35);
        background: white;
        width: 95vw;
        max-width: 560px;
        max-height: 90vh;
        padding: 0;
        margin: auto;
        inset: 0;
        position: fixed;
        align-items: center;
        justify-content: center;
        overflow-y: auto;
    }
    .event-details-modal::backdrop {
        background: rgba(10, 22, 51, 0.5);
        backdrop-filter: blur(4px);
    }
    .event-details-modal[open] {
        display: flex;
    }
</style>

<script>
    function closeEventModal() {
        var modal = document.getElementById('event-details-modal');
        if (modal && typeof modal.close === 'function') {
            modal.close();
        } else if (modal) {
            modal.style.display = 'none';
            modal.removeAttribute('open');
        }
    }

    function openEventDetails(events, heading) {
        var modal = document.getElementById('event-details-modal');
        var body = document.getElementById('event-details-body');
        var head = document.getElementById('event-details-heading');
        if (!modal || !body) return;

        head.textContent = heading || 'Event Details';

        var list = events && events.length ? events : [];

        if (!list.length) {
            body.innerHTML =
                '<div class="rounded-xl border border-[#0018f9]/15 bg-[#f4f8ff] px-5 py-10 text-center">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto h-10 w-10 text-[#0018f9]/40">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />' +
                    '</svg>' +
                    '<p class="mt-3 mb-0 text-[15px] font-semibold text-[#0a1633]">No update yet</p>' +
                    '<p class="mt-1 mb-0 text-[13px] text-slate-500">There are no calendar events posted for this date.</p>' +
                '</div>';
        } else {
            body.innerHTML = list.map(renderEventCard).join('');
        }

        if (typeof modal.showModal === 'function') {
            modal.showModal();
        } else {
            modal.style.display = 'flex';
            modal.setAttribute('open', '');
        }
    }

    function openDay(dayKey) {
        var list = (window.calendarEvents && window.calendarEvents[dayKey]) || [];
        var heading = '';
        if (dayKey) {
            var parts = dayKey.split('-');
            var d = new Date(parts[0], parts[1] - 1, parts[2]);
            heading = d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        }
        openEventDetails(list, heading);
    }

    function renderEventCard(e) {
        var time = '';
        if (e.start) {
            time = e.end ? e.start + ' – ' + e.end : e.start;
        }
        return [
            '<div class="rounded-xl border border-[#0018f9]/15 bg-white p-4 shadow-sm">',
                '<div class="flex flex-wrap items-center gap-2">',
                    '<span class="text-[13.5px] font-bold text-[#0a1633]">' + esc(e.title) + '</span>',
                    '<span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-semibold ' + (e.badge || '') + '">' + esc(e.category) + '</span>',
                '</div>',
                (time ? '<p class="mt-1.5 mb-0 text-[12.5px] text-[#0a1633]/70"><strong>Time:</strong> ' + esc(time) + '</p>' : ''),
                (e.location ? '<p class="mt-0.5 mb-0 text-[12.5px] text-[#0a1633]/70"><strong>Location:</strong> ' + esc(e.location) + '</p>' : ''),
                (e.school_year ? '<p class="mt-0.5 mb-0 text-[12.5px] text-[#0a1633]/70"><strong>School Year:</strong> ' + esc(e.school_year) + ' &middot; Term ' + e.term + '</p>' : ''),
                (e.short ? '<p class="mt-2 mb-0 text-[13px] font-medium text-slate-700">' + esc(e.short) + '</p>' : ''),
                (e.full ? '<p class="mt-1 mb-0 text-[13px] leading-relaxed text-slate-600">' + esc(e.full).replace(/\n/g, '<br>') + '</p>' : ''),
            '</div>'
        ].join('');
    }

    function esc(s) {
        if (s == null) return '';
        var div = document.createElement('div');
        div.textContent = String(s);
        return div.innerHTML;
    }
</script>
