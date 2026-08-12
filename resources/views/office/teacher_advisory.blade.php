@php
    $filter = request('filter', 'all');
    $ctrl = \App\Http\Controllers\OfficeAdmin\TeacherAssignmentController::class;
@endphp

<x-layouts.app :title="'Teacher Advisory'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.3 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Teacher Advisory</h2>
        </div>
<div class="flex items-center gap-3">
            <form method="GET" action="{{ route('office.teacher-advisory') }}" class="flex items-center gap-2">
                <label for="filter" class="text-[13px] font-medium text-[#475569]">Filter:</label>
                 <select id="filter" name="filter"
                         class="futuristic-select px-3 py-1.5 text-[13px]"
                         onchange="this.form.submit()">
                     <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Levels</option>
                     <option value="jhs" {{ $filter === 'jhs' ? 'selected' : '' }}>JHS (Grades 7–10)</option>
                     <option value="shs" {{ $filter === 'shs' ? 'selected' : '' }}>SHS (Grades 11–12)</option>
                 </select>
             </form>
            <a href="{{ route('office.assign-class') }}"
               class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Assign Class</a>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[700px] border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Advisory Class</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Level</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Track</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teachers as $i => $t)
                    @php
                        $parsed = $t->advisory_class ? $ctrl::parseAdvisory($t->advisory_class) : null;
                    @endphp
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 font-medium text-[#0a1633]">{{ $t->name }}</td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            @if ($t->advisory_class)
                                <span class="inline-flex items-center rounded-lg border border-[#0018f9]/20 bg-[#0018f9]/5 px-2.5 py-1 text-[13px] text-[#0a1633]">{{ $t->advisory_class }}</span>
                            @else
                                <span class="text-[#94a3b8]">Unassigned</span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            @if ($parsed)
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[12px] font-medium
                                    {{ $parsed['level'] === 'JHS' ? 'bg-emerald-100/50 text-emerald-700' : 'bg-purple-100/50 text-purple-700' }}">
                                    {{ $parsed['level'] }}
                                </span>
                            @else
                                <span class="text-[#94a3b8]">-</span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            @if ($parsed && $parsed['track'])
                                @if ($parsed['track'] === 'TVL')
                                    <span class="inline-flex items-center rounded-md bg-orange-100/50 px-2 py-0.5 text-[12px] font-medium text-orange-700">TVL</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-sky-100/50 px-2 py-0.5 text-[12px] font-medium text-sky-700">{{ $parsed['track'] }}</span>
                                @endif
                            @else
                                <span class="text-[#94a3b8]">-</span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            @if ($t->advisory_class)
                                <button type="button"
                                        onclick="openEditModal({{ $t->user_id }})"
                                        class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-3 py-1.5 text-[13px] font-semibold text-white no-underline transition hover:brightness-110">
                                    Edit
                                </button>
                            @else
                                <a href="{{ route('office.assign-class') }}"
                                   class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-3 py-1.5 text-[13px] font-semibold text-white no-underline transition hover:brightness-110">
                                    Assign
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-[#6b7280]">No active teachers with advisory classes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function initModalTrackToggle() {
            var gradeSelect = document.getElementById('grade_level');
            var trackField = document.getElementById('track-field');
            if (!gradeSelect || !trackField) return;

            var toggleTrack = function () {
                var val = gradeSelect.value;
                var isSHS = val === '11' || val === '12';
                trackField.style.display = isSHS ? 'grid' : 'none';
            };

            gradeSelect.addEventListener('change', toggleTrack);
            toggleTrack();
        }

        function injectEditModal(html) {
            const existingModal = document.getElementById('edit-advisory-modal');
            if (existingModal) existingModal.remove();

            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = html.trim();
            while (modalContainer.firstChild) {
                document.body.appendChild(modalContainer.firstChild);
            }

            const modal = document.getElementById('edit-advisory-modal');
            if (modal && typeof modal.showModal === 'function') {
                modal.showModal();
            } else if (modal) {
                modal.style.display = 'block';
                modal.setAttribute('open', '');
            }

            initModalTrackToggle();
        }

        function openEditModal(teacherUserId) {
            fetch("{{ route('office.advisory.edit', ':teacherId') }}".replace(':teacherId', teacherUserId), {
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
                injectEditModal(html);
            })
            .catch(err => {
                console.error('Failed to load edit modal:', err);
                showToast('Failed to load the edit form. Please refresh the page and try again.', 'error');
            });
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-advisory-modal');
            if (modal && typeof modal.close === 'function') {
                modal.close();
            } else if (modal) {
                modal.style.display = 'none';
                modal.removeAttribute('open');
            }
        }

        // Submit the edit modal through fetch so validation errors (422) are
        // re-rendered inline inside the form instead of as a toast behind the
        // blurred backdrop. Success responses follow the redirect to reload
        // the advisory table with the success toast.
        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!form || form.id !== 'edit-advisory-form' || form.getAttribute('data-ajax') !== '1') {
                return;
            }

            event.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                body: new FormData(form)
            })
            .then(function (response) {
                var contentType = response.headers.get('Content-Type') || '';
                if (contentType.indexOf('application/json') !== -1) {
                    return response.json().then(function (data) {
                        if (data && data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }
                        throw new Error('Missing redirect target in response');
                    });
                }
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                if (response.status === 422) {
                    return response.text().then(function (html) {
                        injectEditModal(html);
                    });
                }
                throw new Error('Server responded with status ' + response.status);
            })
            .catch(function (err) {
                console.error('Failed to save advisory class:', err);
                if (submitBtn) submitBtn.disabled = false;
                showToast('Failed to save the advisory class. Please try again.', 'error');
            });
        });

        @if (session('edit_teacher_id'))
            document.addEventListener('DOMContentLoaded', function () {
                openEditModal({{ (int) session('edit_teacher_id') }});
            });
        @endif
    </script>
</x-layouts.app>
