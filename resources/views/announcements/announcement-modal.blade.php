<dialog id="announcement-detail-modal" class="modal-modal announcement-details-modal">
    <div class="grid gap-4 p-6 max-w-lg">
        <div class="flex items-center justify-between gap-3">
            <h3 id="announcement-detail-heading" class="m-0 text-[15px] font-semibold text-[#0a1633]">Announcement</h3>
            <button type="button" onclick="closeAnnouncementModal()" aria-label="Close"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#0018f9]/15 bg-white text-[#0a1633]/60 transition hover:bg-[#f4f8ff] hover:text-[#0a1633]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="announcement-detail-body" class="grid gap-3 max-h-[60vh] overflow-y-auto pr-1"></div>
        <div class="flex justify-end">
            <button type="button" onclick="closeAnnouncementModal()"
                    class="rounded-lg border border-[#0018f9]/20 bg-white px-6 py-2 font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                Close
            </button>
        </div>
    </div>
</dialog>

<style>
    .announcement-details-modal {
        border: none;
        border-radius: 14px;
        box-shadow: 0 20px 50px -12px rgba(2, 6, 23, 0.35);
        background: white;
        max-width: 95vw;
        width: 100%;
        max-height: 90vh;
        padding: 0;
        margin: auto;
        inset: 0;
        position: fixed;
        align-items: center;
        justify-content: center;
        overflow-y: auto;
    }
    .announcement-details-modal::backdrop {
        background: rgba(10, 22, 51, 0.5);
        backdrop-filter: blur(4px);
    }
    .announcement-details-modal[open] {
        display: flex;
    }
</style>

<script>
    function announcementEsc(s) {
        if (s == null) return '';
        var div = document.createElement('div');
        div.textContent = String(s);
        return div.innerHTML;
    }

    function closeAnnouncementModal() {
        var modal = document.getElementById('announcement-detail-modal');
        if (modal && typeof modal.close === 'function') {
            modal.close();
        } else if (modal) {
            modal.style.display = 'none';
            modal.removeAttribute('open');
        }
    }

    function announcementCsrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function renderAnnouncementCard(a) {
        var accent = a.priority_accent || 'bg-sky-500';
        return [
            '<div class="overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white shadow-sm">',
                '<div class="h-1 w-full ' + accent + '"></div>',
                '<div class="p-4">',
                    '<div class="flex flex-wrap items-center gap-2">',
                        '<span class="text-[15px] font-bold text-[#0a1633]">' + announcementEsc(a.title) + '</span>',
                        (a.priority_badge ? '<span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-semibold ' + (a.priority_badge || '') + '">' + announcementEsc(a.priority_label || '') + '</span>' : ''),
                    '</div>',
                    '<p class="mt-0.5 mb-0 text-[11.5px] text-slate-400">' +
                        (a.publish_date ? announcementEsc(a.publish_date) : '') +
                        (a.target_label ? ' &middot; ' + announcementEsc(a.target_label) : '') +
                        (a.expiration_date ? ' &middot; ends ' + announcementEsc(a.expiration_date) : '') +
                    '</p>',
                    (a.summary ? '<p class="mt-2 mb-0 text-[13px] font-semibold text-slate-700">' + announcementEsc(a.summary) + '</p>' : ''),
                    (a.content ? '<p class="mt-1.5 mb-0 text-[13.5px] leading-relaxed text-slate-600">' + announcementEsc(a.content).replace(/\n/g, '<br>') + '</p>' : ''),
                    (a.attachment_url ? '<p class="mt-2.5 mb-0"><a href="' + announcementEsc(a.attachment_url) + '" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg border border-[#0018f9]/20 bg-[#f4f8ff] px-3 py-1.5 text-[12.5px] font-semibold text-[#0018f9] transition hover:bg-[#eaf3ff]">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" /></svg>' +
                        (a.attachment_name ? announcementEsc(a.attachment_name) : 'Download Attachment') + '</a></p>' : ''),
                '</div>',
            '</div>'
        ].join('');
    }

    function openAnnouncement(id) {
        var modal = document.getElementById('announcement-detail-modal');
        var body = document.getElementById('announcement-detail-body');
        var head = document.getElementById('announcement-detail-heading');
        if (!modal || !body) return;

        var a = (window.announcementsData && window.announcementsData[id]) || null;

        if (!a) {
            body.innerHTML =
                '<div class="rounded-xl border border-[#0018f9]/15 bg-[#f4f8ff] px-5 py-10 text-center">' +
                    '<p class="mt-3 mb-0 text-[15px] font-semibold text-[#0a1633]">Announcement not found</p>' +
                    '<p class="mt-1 mb-0 text-[13px] text-slate-500">This announcement may no longer be available.</p>' +
                '</div>';
        } else {
            head.textContent = 'Announcement';
            body.innerHTML = renderAnnouncementCard(a);
            if (!a.is_read) {
                markAnnouncementRead(id);
            }
        }

        if (typeof modal.showModal === 'function') {
            modal.showModal();
        } else {
            modal.style.display = 'flex';
            modal.setAttribute('open', '');
        }
    }

    function markAnnouncementRead(id) {
        if (!window.announcementsData || !window.announcementsData[id]) return;
        window.announcementsData[id].is_read = true;

        var row = document.querySelector('.announcement-row[data-announcement-id="' + id + '"]');
        var dot = document.querySelector('.announcement-dot-' + id);
        if (dot) {
            dot.classList.remove('bg-[#0018f9]');
            dot.classList.add('bg-slate-300');
        }

        fetch("{{ route('announcements.mark-read') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': announcementCsrf(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Server responded with status ' + response.status);
            }
            return response.json();
        })
        .then(function (json) {
            if (json && typeof json.unread === 'number') {
                updateAnnouncementBadge(json.unread);
            }
        })
        .catch(function () {
            showToast('Could not update announcement status. Please try again.', 'error');
        });
    }

    function updateAnnouncementBadge(count) {
        var badge = document.querySelector('.announcement-unread-badge');
        var counter = document.querySelector('[data-unread-count]');
        if (!badge) return;
        if (counter) counter.textContent = count;
        if (count > 0) {
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
</script>
