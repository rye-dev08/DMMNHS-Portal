<x-layouts.app :title="'My Academic Journey'">
    @php
        $iconPath = function (string $key) {
            $patterns = [
                'person' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
                'id' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
                'clipboard' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z',
                'book' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
                'requirement' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
                'chart' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
                'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z',
                'tick' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                'bell' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
                'announcement' => 'M13.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z',
                'search' => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z',
            ];

            return $patterns[$key] ?? $patterns['tick'];
        };

        $categoryStyles = [
            'Academic' => ['dot' => 'bg-[#38bdf8]', 'icon' => 'bg-sky-500/15 text-sky-600'],
            'Enrollment' => ['dot' => 'bg-[#8b5cf6]', 'icon' => 'bg-violet-500/15 text-violet-600'],
            'Requirements' => ['dot' => 'bg-[#f59e0b]', 'icon' => 'bg-amber-500/15 text-amber-600'],
            'Grades' => ['dot' => 'bg-[#10b981]', 'icon' => 'bg-emerald-500/15 text-emerald-600'],
            'Documents' => ['dot' => 'bg-[#3b82f6]', 'icon' => 'bg-blue-500/15 text-blue-600'],
            'Activities' => ['dot' => 'bg-[#ec4899]', 'icon' => 'bg-pink-500/15 text-pink-600'],
        ];

        $termLabel = fn ($term) => $term !== null ? 'Semester '.$term : '—';
        $hasActiveFilters = ! empty(array_filter($activeFilters)) || $search !== '';
    @endphp

    <div class="flex flex-col gap-6 lg:gap-7">
        {{-- Hero --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] p-6 text-white shadow-[0_12px_30px_-12px_rgba(10,22,51,0.7)]">
            <div class="pointer-events-none absolute inset-0"
                 style="background-image: linear-gradient(rgba(148,197,255,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(148,197,255,0.08) 1px, transparent 1px); background-size: 34px 34px;"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(56,189,248,0.22),transparent_55%)]"></div>
            <div class="relative z-10">
                <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-white/55">Student</p>
                <h2 class="m-0 mt-1 text-2xl font-bold">My Academic Journey</h2>
                <p class="mt-1.5 text-[13.5px] text-white/70">A permanent chronological record of your milestones across the portal.</p>
            </div>
        </div>

        {{-- Filters + search --}}
        <form method="GET" action="{{ route('student.timeline') }}"
              class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 p-4 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] backdrop-blur-sm">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                <div class="col-span-2 lg:col-span-1">
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Search</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{!! $iconPath('search') !!}" />
                        </svg>
                        <input type="text" name="q" value="{{ $search }}"
                               placeholder="Grade, Enrollment, Math..."
                               class="w-full rounded-lg border border-[#0018f9]/15 bg-white py-2 pl-8 pr-3 text-[13px] text-[#0a1633] outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/20">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">School Year</label>
                    <select name="school_year" class="w-full rounded-lg border border-[#0018f9]/15 bg-white py-2 px-3 text-[13px] text-[#0a1633] outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/20">
                        <option value="">All Years</option>
                        @foreach ($options->schoolYears as $year)
                            <option value="{{ $year }}" @selected((string) ($activeFilters['school_year'] ?? '') === (string) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Semester</label>
                    <select name="term" class="w-full rounded-lg border border-[#0018f9]/15 bg-white py-2 px-3 text-[13px] text-[#0a1633] outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/20">
                        <option value="">All Semesters</option>
                        @foreach ($options->terms as $termOption)
                            <option value="{{ $termOption }}" @selected((int) ($activeFilters['term'] ?? 0) === (int) $termOption)>{{ $termOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Event Type</label>
                    <select name="category" class="w-full rounded-lg border border-[#0018f9]/15 bg-white py-2 px-3 text-[13px] text-[#0a1633] outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/20">
                        <option value="">All Types</option>
                        @foreach ($options->categories as $categoryOption)
                            <option value="{{ $categoryOption }}" @selected(($activeFilters['category'] ?? '') === $categoryOption)>{{ $categoryOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2 lg:col-span-1">
                    <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Date Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" name="from" value="{{ $activeFilters['from'] ?? '' }}"
                               class="w-full rounded-lg border border-[#0018f9]/15 bg-white py-2 px-2 text-[12.5px] text-[#0a1633] outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/20">
                        <input type="date" name="to" value="{{ $activeFilters['to'] ?? '' }}"
                               class="w-full rounded-lg border border-[#0018f9]/15 bg-white py-2 px-2 text-[12.5px] text-[#0a1633] outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/20">
                    </div>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                <p class="m-0 text-[12px] text-slate-500">{{ $events->count() }} milestone(s) shown</p>
                <div class="flex items-center gap-2">
                    @if ($hasActiveFilters)
                        <a href="{{ route('student.timeline') }}"
                           class="rounded-lg border border-[#0018f9]/15 bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#0a1633] no-underline transition hover:bg-[#f4f8ff]">Clear Filters</a>
                    @endif
                    <button type="submit"
                            class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-1.5 text-[12.5px] font-semibold text-white transition hover:brightness-110">Apply</button>
                </div>
            </div>
        </form>

        {{-- Timeline --}}
        @if ($events->isEmpty())
            <div class="relative overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 px-4 py-14 text-center shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] backdrop-blur-sm">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#0018f9]/10 to-[#38bdf8]/10 text-[#0018f9]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <h3 class="m-0 text-[16px] font-semibold text-[#0a1633]">My academic journey has not started yet.</h3>
                <p class="mx-auto mt-1.5 max-w-md text-[13px] text-slate-500">
                    Milestones from your enrollment, grades, requirements, and school events will appear here automatically.
                </p>
                <a href="{{ route('student.enrollment') }}"
                   class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 text-[13px] font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">
                    Start with Enrollment
                </a>
            </div>
        @else
            <div class="relative pl-7">
                {{-- Vertical connector --}}
                <div class="absolute bottom-4 left-[13px] top-4 w-[2px] bg-gradient-to-b from-[#0018f9]/30 via-[#38bdf8]/25 to-[#0018f9]/10"></div>

                <div class="flex flex-col gap-5">
                    @foreach ($events as $event)
                        @php
                            $style = $categoryStyles[$event->category] ?? $categoryStyles['Academic'];
                        @endphp
                        <div class="relative">
                            {{-- Timeline dot --}}
                            <span class="absolute -left-7 top-5 z-10 flex h-[26px] w-[26px] items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-[#0018f9] to-[#38bdf8] shadow-[0_0_12px_rgba(0,24,249,0.45)]">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3 text-white">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />
                                </svg>
                            </span>

                            <div data-timeline-id="{{ $event->id }}"
                                 class="group cursor-pointer overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white/80 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] backdrop-blur-sm transition hover:-translate-y-0.5 hover:shadow-[0_12px_30px_-10px_rgba(0,24,249,0.3)]">
                                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>
                                <div class="pointer-events-none absolute right-2 top-2 h-4 w-4 rounded-tr-lg border-r border-t border-[#38bdf8]/40"></div>
                                <div class="flex items-start gap-3.5 p-4">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $style['icon'] }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{!! $iconPath($event->icon) !!}" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-[14.5px] font-semibold text-[#0a1633]">{{ $event->title }}</span>
                                            @if ($event->badge)
                                                <span class="rounded-md border border-[#10b981]/25 bg-emerald-50 px-1.5 py-0.5 text-[10.5px] font-semibold text-emerald-700">{{ $event->badge }}</span>
                                            @endif
                                        </div>
                                        <p class="mt-0.5 m-0 text-[12.5px] leading-relaxed text-slate-500">{{ $event->detail }}</p>
                                        <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11.5px] text-slate-400">
                                            <span class="font-medium text-[#0018f9]">{{ $event->at->format('M d, Y g:i A') }}</span>
                                            <span>&middot;</span>
                                            <span>S.Y. {{ $event->school_year !== '' ? $event->school_year : '—' }}</span>
                                            <span>&middot;</span>
                                            <span>{{ $termLabel($event->term) }}</span>
                                            <span class="ml-auto inline-flex items-center gap-1 rounded-md border border-[#0018f9]/10 bg-[#f4f8ff] px-2 py-0.5 text-[10.5px] font-semibold uppercase tracking-wide text-[#0018f9]/70">
                                                <span class="inline-block h-1.5 w-1.5 rounded-full {{ $style['dot'] }}"></span>
                                                {{ $event->category }}
                                            </span>
                                        </div>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="mt-1 h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-[#0018f9]">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Detail modal --}}
    <dialog id="timeline-detail-modal" class="modal-modal timeline-detail-modal">
        <div class="grid gap-4 p-6 max-w-lg">
            <div class="flex items-center justify-between gap-3">
                <h3 id="timeline-detail-heading" class="m-0 text-[15px] font-semibold text-[#0a1633]">Timeline Event</h3>
                <button type="button" onclick="closeTimelineModal()" aria-label="Close"
                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#0018f9]/15 bg-white text-[#0a1633]/60 transition hover:bg-[#f4f8ff] hover:text-[#0a1633]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="timeline-detail-body" class="grid gap-3 max-h-[60vh] overflow-y-auto pr-1"></div>
            <div class="flex justify-end">
                <button type="button" onclick="closeTimelineModal()"
                        class="rounded-lg border border-[#0018f9]/20 bg-white px-6 py-2 font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                    Close
                </button>
            </div>
        </div>
    </dialog>

    <style>
        .timeline-detail-modal {
            border: none;
            border-radius: 14px;
            box-shadow: 0 20px 50px -12px rgba(2, 6, 23, 0.35);
            background: white;
            max-width: 560px;
            width: 92%;
            padding: 0;
            margin: auto;
            inset: 0;
            position: fixed;
            align-items: center;
            justify-content: center;
        }
        .timeline-detail-modal::backdrop {
            background: rgba(10, 22, 51, 0.5);
            backdrop-filter: blur(4px);
        }
        .timeline-detail-modal[open] {
            display: flex;
        }
    </style>

    <script>
        (function () {
            window.timelineData = window.timelineData || {};
            @foreach ($events as $event)
                window.timelineData[{{ json_encode($event->id) }}] = {
                    title: @json($event->title),
                    detail: @json($event->detail),
                    date: @json($event->at->format('F j, Y g:i A')),
                    school_year: @json($event->school_year !== '' ? $event->school_year : null),
                    term: @json($event->term),
                    category: @json($event->category),
                    badge: @json($event->badge),
                    url: @json($event->url),
                    action_text: @json($event->action_text)
                };
            @endforeach

            function esc(s) {
                if (s == null) return '';
                var div = document.createElement('div');
                div.textContent = String(s);
                return div.innerHTML;
            }

            window.closeTimelineModal = function () {
                var modal = document.getElementById('timeline-detail-modal');
                if (modal && typeof modal.close === 'function') {
                    modal.close();
                } else if (modal) {
                    modal.style.display = 'none';
                    modal.removeAttribute('open');
                }
            };

            function renderTimelineCard(e) {
                var termText = e.term ? 'Semester ' + e.term : '&mdash;';
                var yearText = e.school_year ? 'S.Y. ' + esc(e.school_year) : '&mdash;';
                return [
                    '<div class="overflow-hidden rounded-xl border border-[#0018f9]/15 bg-white shadow-sm">',
                        '<div class="h-1 w-full bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>',
                        '<div class="p-4">',
                            '<div class="flex flex-wrap items-center gap-2">',
                                '<span class="text-[15px] font-bold text-[#0a1633]">' + esc(e.title) + '</span>',
                                (e.badge ? '<span class="rounded-md border border-[#10b981]/25 bg-emerald-50 px-1.5 py-0.5 text-[11px] font-semibold text-emerald-700">' + esc(e.badge) + '</span>' : ''),
                            '</div>',
                            '<span class="mt-1.5 inline-flex items-center rounded-md border border-[#0018f9]/10 bg-[#f4f8ff] px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-[#0018f9]/70">' + esc(e.category) + '</span>',
                            '<p class="mt-2.5 mb-0 text-[13.5px] leading-relaxed text-slate-600">' + esc(e.detail) + '</p>',
                            '<div class="mt-3 grid gap-1 rounded-lg bg-[#f4f8ff] p-3 text-[12.5px] text-slate-600">',
                                '<p class="m-0"><span class="font-semibold text-[#0a1633]">Date:</span> ' + esc(e.date) + '</p>',
                                '<p class="m-0"><span class="font-semibold text-[#0a1633]">School Year:</span> ' + yearText + '</p>',
                                '<p class="m-0"><span class="font-semibold text-[#0a1633]">Semester:</span> ' + termText + '</p>',
                            '</div>',
                            (e.url ? '<p class="mt-3 mb-0"><a href="' + esc(e.url) + '" class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 text-[12.5px] font-semibold text-white no-underline transition hover:brightness-110">' + (e.action_text ? esc(e.action_text) : 'Open Module') + '</a></p>' : ''),
                        '</div>',
                    '</div>'
                ].join('');
            }

            window.openTimelineEvent = function (id) {
                var modal = document.getElementById('timeline-detail-modal');
                var body = document.getElementById('timeline-detail-body');
                var head = document.getElementById('timeline-detail-heading');
                if (!modal || !body) return;

                var e = (window.timelineData && window.timelineData[id]) || null;

                if (!e) {
                    body.innerHTML =
                        '<div class="rounded-xl border border-[#0018f9]/15 bg-[#f4f8ff] px-5 py-10 text-center">' +
                            '<p class="mt-3 mb-0 text-[15px] font-semibold text-[#0a1633]">Event not found</p>' +
                            '<p class="mt-1 mb-0 text-[13px] text-slate-500">This timeline event is no longer available.</p>' +
                        '</div>';
                } else {
                    head.textContent = e.category;
                    body.innerHTML = renderTimelineCard(e);
                }

                if (typeof modal.showModal === 'function') {
                    modal.showModal();
                } else {
                    modal.style.display = 'flex';
                    modal.setAttribute('open', '');
                }
            };

            document.querySelectorAll('[data-timeline-id]').forEach(function (row) {
                row.addEventListener('click', function () {
                    openTimelineEvent(row.getAttribute('data-timeline-id'));
                });
            });
        })();
    </script>
</x-layouts.app>
