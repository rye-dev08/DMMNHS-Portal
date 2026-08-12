<x-layouts.app :title="'Teacher Dashboard'">
    <div id="poll-dashboard" class="flex flex-col gap-6 lg:gap-7">
        {{-- Hero --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] p-6 text-white shadow-[0_12px_30px_-12px_rgba(10,22,51,0.7)]">
            <div class="pointer-events-none absolute inset-0"
                 style="background-image: linear-gradient(rgba(148,197,255,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(148,197,255,0.08) 1px, transparent 1px); background-size: 34px 34px;"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(56,189,248,0.22),transparent_55%)]"></div>
            <div class="relative z-10">
                <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-white/55">Teacher Dashboard</p>
                <h2 class="m-0 mt-1 text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h2>
                <p class="mt-1.5 text-[13.5px] text-white/70">
                    @if ($workload->period)
                        {{ $workload->period->label }}
                    @endif
                    @if ($advisory !== '')
                        · Advisory: <strong class="text-white">{{ $advisory }}</strong>
                    @endif
                </p>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 gap-3.5 lg:grid-cols-4">
            <x-card :title="'Students'">
                <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $workload->summary->students }}</p>
                <p class="mt-1 text-[12.5px] text-slate-500">Approved students in your class</p>
            </x-card>
            <x-card :title="'Subjects Handled'">
                <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $workload->summary->subjects_handled }}</p>
                <p class="mt-1 text-[12.5px] text-slate-500">Subjects currently taught</p>
            </x-card>
            <x-card :title="'Pending Requirements'">
                <p class="m-0 text-2xl font-bold {{ $workload->summary->pending_requirements > 0 ? 'text-amber-600' : 'text-[#0018f9]' }}">{{ $workload->summary->pending_requirements }}</p>
                <p class="mt-1 text-[12.5px] text-slate-500">Requiring your action</p>
            </x-card>
            <x-card :title="'Unread Messages'">
                <p class="m-0 text-2xl font-bold {{ $workload->summary->unread_messages > 0 ? 'text-[#0018f9]' : 'text-slate-400' }}">{{ $workload->summary->unread_messages }}</p>
                <p class="mt-1 text-[12.5px] text-slate-500">Portal notifications</p>
            </x-card>
            <x-card :title="'Pending Grade Submissions'">
                <p class="m-0 text-2xl font-bold {{ $workload->summary->pending_grade_submissions > 0 ? 'text-amber-600' : 'text-[#0018f9]' }}">{{ $workload->summary->pending_grade_submissions }}</p>
                <p class="mt-1 text-[12.5px] text-slate-500">Units not yet submitted</p>
            </x-card>
            <x-card :title="'Upcoming Deadlines'">
                <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $workload->summary->upcoming_deadlines }}</p>
                <p class="mt-1 text-[12.5px] text-slate-500">Requirements &amp; grade submissions</p>
            </x-card>
            <x-card :title="'Advisory Sections'">
                <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $workload->summary->advisory_sections }}</p>
                <p class="mt-1 text-[12.5px] text-slate-500">Assigned advisory section</p>
            </x-card>
            <x-card :title="'Today\'s Schedule'">
                <p class="m-0 text-2xl font-bold text-[#0018f9]">{{ $workload->summary->classes_today }}</p>
                <p class="mt-1 text-[12.5px] text-slate-500">Subjects taught today</p>
            </x-card>
        </div>

        {{-- Privacy notice --}}
        <div class="flex items-start gap-3 rounded-xl border border-[#0018f9]/15 bg-white/80 p-3.5 shadow-[0_6px_18px_-8px_rgba(0,24,249,0.15)]">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#0018f9]/10 text-[#0018f9]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4.5 w-4.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </span>
            <p class="m-0 text-[13px] leading-relaxed text-slate-600">
                <strong class="text-[#0a1633]">Privacy Notice:</strong> Screenshotting, recording, or sharing student Grades,
                Profile Info, Scores, and similar records without authorization is strictly prohibited for data privacy. The
                administration also maintains privacy and confidentiality of all user records.
            </p>
        </div>

        {{-- Workload + Deadlines --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Today's Workload --}}
            <x-card :title="'Today\'s Workload'">
                @if ($workload->todayWorkload->isEmpty())
                    <p class="m-0 py-2 text-center text-[13px] text-slate-500">No pending workload.</p>
                @else
                    <ul class="grid gap-1.5">
                        @foreach ($workload->todayWorkload as $item)
                            <li>
                                <a href="{{ $item->url }}" class="group flex items-center gap-3 rounded-lg px-2 py-1.5 no-underline transition hover:bg-[#0018f9]/5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                                        {{ $item->urgency === 'urgent' ? 'bg-red-50 text-red-600' : ($item->urgency === 'soon' ? 'bg-amber-50 text-amber-600' : 'bg-[#0018f9]/8 text-[#0018f9]') }}">
                                        @if ($item->type === 'grades')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        @elseif ($item->type === 'deadline')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        @elseif ($item->type === 'enrollment')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                                        @elseif ($item->type === 'message')
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                        @endif
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-[13px] font-semibold text-[#0a1633] transition group-hover:text-[#2563eb]">{{ $item->title }}</span>
                                        <span class="mt-0.5 block truncate text-[11.5px] text-slate-500">{{ $item->detail }}</span>
                                    </span>
                                    @if ($item->urgency === 'urgent')
                                        <span class="shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-[10.5px] font-bold uppercase tracking-wide text-red-600">Now</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            {{-- Upcoming Deadlines --}}
            <x-card :title="'Upcoming Deadlines'">
                @if ($workload->upcomingDeadlines->isEmpty())
                    <p class="m-0 py-2 text-center text-[13px] text-slate-500">No upcoming deadlines.</p>
                @else
                    <ul class="grid gap-1.5">
                        @foreach ($workload->upcomingDeadlines->take(8) as $deadline)
                            @php
                                $days = (int) \Illuminate\Support\Carbon::today()->startOfDay()
                                    ->diffInDays(\Illuminate\Support\Carbon::parse($deadline->date)->startOfDay());
                                $relative = $days === 0 ? 'Today' : ($days === 1 ? 'Tomorrow' : ($days <= 7 ? "In {$days} days" : $deadline->date->format('M d, Y')));
                            @endphp
                            <li>
                                <a href="{{ $deadline->url }}" class="group flex items-center gap-3 rounded-lg px-2 py-1.5 no-underline transition hover:bg-[#0018f9]/5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#0018f9]/8 text-[#0018f9]">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-[13px] font-semibold text-[#0a1633] transition group-hover:text-[#2563eb]">{{ $deadline->title }}</span>
                                        <span class="mt-0.5 block text-[11.5px] text-slate-500">{{ $deadline->type === 'grades' ? 'Grade submission deadline' : 'Requirement deadline' }}</span>
                                    </span>
                                    <span class="shrink-0 rounded-md border px-1.5 py-0.5 text-[11px] font-semibold {{ $days === 0 ? 'border-red-200 bg-red-50 text-red-700' : ($days === 1 ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-200 bg-slate-50 text-slate-600') }}">
                                        {{ $relative }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>

        {{-- Requirements + Grade progress --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Pending Requirements --}}
            <x-card :title="'Pending Requirements'">
                @if ($workload->pendingRequirements->isEmpty())
                    <p class="m-0 py-2 text-center text-[13px] text-slate-500">No pending requirements.</p>
                @else
                    <ul class="grid gap-1.5">
                        @foreach ($workload->pendingRequirements->take(6) as $requirement)
                            <li>
                                <a href="{{ $requirement->url }}" class="group block rounded-lg px-2 py-2 no-underline transition hover:bg-[#0018f9]/5">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-[13px] font-semibold text-[#0a1633] transition group-hover:text-[#2563eb]">{{ $requirement->title }}</span>
                                            <span class="mt-0.5 block text-[11.5px] text-slate-500">
                                                Submitted: {{ $requirement->submitted }} / {{ $requirement->total }}
                                                · Remaining: {{ $requirement->remaining }}
                                            </span>
                                        </span>
                                        <span class="shrink-0 text-[12px] font-semibold text-[#0018f9]">View Submissions →</span>
                                    </div>
                                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-[#e5edf8]">
                                        <div class="h-full rounded-full bg-gradient-to-r from-[#0018f9] to-[#38bdf8]" style="width: {{ $requirement->percent }}%"></div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            {{-- Grade Submission Progress --}}
            <x-card :title="'Grade Submission Progress'">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <p class="m-0 text-[13px] font-semibold text-[#0a1633]">
                        {{ $workload->period?->school_year ?? '' }} · Term {{ $workload->period?->term ?? 1 }}
                    </p>
                    <p class="m-0 text-[15px] font-bold text-[#0018f9]">{{ $workload->gradeCompletion }}%</p>
                </div>
                <div class="mb-3 h-2 w-full overflow-hidden rounded-full bg-[#e5edf8]">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#0018f9] to-[#38bdf8] transition-all" style="width: {{ $workload->gradeCompletion }}%"></div>
                </div>
                <div class="mb-3 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[12px] font-semibold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> {{ $workload->gradeUnits->where('status', 'submitted')->count() }} Submitted
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-[12px] font-semibold text-amber-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> {{ $workload->gradeUnits->where('status', 'pending')->count() }} Pending
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-[12px] font-semibold text-red-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> {{ $workload->gradeUnits->where('status', 'late')->count() }} Late
                    </span>
                </div>
                @if ($workload->gradeUnits->isEmpty())
                    <p class="m-0 py-2 text-center text-[13px] text-slate-500">No grade submission units yet.</p>
                @else
                    <ul class="grid gap-1.5 sm:grid-cols-2">
                        @foreach ($workload->gradeUnits as $unit)
                            <li class="flex items-center gap-2 rounded-lg px-2 py-1.5">
                                <span class="h-2 w-2 shrink-0 rounded-full
                                    {{ $unit->status === 'submitted' ? 'bg-emerald-500' : ($unit->status === 'late' ? 'bg-red-500' : 'bg-amber-500') }}"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13px] font-semibold text-[#0a1633]">{{ $unit->subject_name }}</span>
                                    <span class="block text-[11.5px] text-slate-500">
                                        {{ $unit->grade_level ? 'Grade '.$unit->grade_level : '' }}{{ $unit->section ? ' - '.$unit->section : '' }}
                                        · {{ $unit->graded }}/{{ $unit->assigned }} graded
                                    </span>
                                </span>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[10.5px] font-bold uppercase tracking-wide
                                    {{ $unit->status === 'submitted' ? 'bg-emerald-100 text-emerald-700' : ($unit->status === 'late' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700') }}">
                                    {{ $unit->status }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <div class="mt-3">
                    <a href="{{ route('teacher.grade-submissions') }}"
                       class="rounded-md border border-[#0018f9]/25 bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#0018f9] no-underline transition hover:bg-[#eef4ff]">View Details →</a>
                </div>
            </x-card>
        </div>

        {{-- Class Summary --}}
        <x-card :title="'Class Summary'">
            @if ($workload->classSummary->isEmpty())
                <p class="m-0 py-2 text-center text-[13px] text-slate-500">No classes assigned yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-[12.5px]">
                        <thead>
                            <tr class="border-b border-[#0018f9]/10 text-[11px] uppercase tracking-wide text-slate-400">
                                <th class="px-2 py-2 font-semibold">Subject</th>
                                <th class="px-2 py-2 font-semibold">Section</th>
                                <th class="px-2 py-2 text-right font-semibold">Students</th>
                                <th class="px-2 py-2 text-right font-semibold">Pending</th>
                                <th class="px-2 py-2 text-right font-semibold">Average</th>
                                <th class="px-2 py-2 font-semibold">Upcoming Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($workload->classSummary as $class)
                                <tr class="border-b border-[#0018f9]/5 transition hover:bg-[#0018f9]/5">
                                    <td class="px-2 py-2.5 font-semibold text-[#0a1633]">{{ $class->subject_name }}</td>
                                    <td class="px-2 py-2.5 text-slate-600">{{ $class->section }}</td>
                                    <td class="px-2 py-2.5 text-right font-semibold text-[#0018f9]">{{ $class->students }}</td>
                                    <td class="px-2 py-2.5 text-right {{ $class->requirements_pending > 0 ? 'font-semibold text-amber-600' : 'text-slate-500' }}">{{ $class->requirements_pending }}</td>
                                    <td class="px-2 py-2.5 text-right font-semibold text-[#0a1633]">{{ $class->average_grade !== null ? number_format($class->average_grade, 1) : '—' }}</td>
                                    <td class="px-2 py-2.5 text-slate-600">
                                        @if ($class->next_activity)
                                            <span class="block truncate">{{ $class->next_activity->title }}</span>
                                            <span class="block text-[11px] text-slate-400">{{ $class->next_activity->date->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        {{-- Activity + Quick actions --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Recent Activity --}}
            <x-card :title="'Recent Activity'">
                @if ($workload->recentActivity->isEmpty())
                    <p class="m-0 py-2 text-center text-[13px] text-slate-500">No recent activity.</p>
                @else
                    <ol class="relative border-l border-[#0018f9]/15 pl-4">
                        @foreach ($workload->recentActivity as $activity)
                            <li class="mb-3.5 last:mb-0">
                                <span class="absolute -left-[7px] mt-1 h-3.5 w-3.5 rounded-full border-2 border-white
                                    {{ $activity->kind === 'grades' ? 'bg-emerald-500' : ($activity->kind === 'requirement' ? 'bg-violet-500' : ($activity->kind === 'enrollment' ? 'bg-sky-500' : ($activity->kind === 'announcement' ? 'bg-amber-500' : 'bg-[#0018f9]'))) }}"></span>
                                <p class="m-0 text-[13px] font-semibold text-[#0a1633]">{{ $activity->title }}</p>
                                <p class="m-0 truncate text-[11.5px] text-slate-500">{{ $activity->detail }}</p>
                                <p class="m-0 text-[11px] text-slate-400">{{ $activity->at->format('M d, Y g:i A') }}</p>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-card>

            {{-- Quick Actions --}}
            <x-card :title="'Quick Actions'">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($workload->quickActions as $action)
                        <a href="{{ $action['url'] }}"
                           class="rounded-xl border border-[#0018f9]/15 bg-white p-3 text-center no-underline shadow-[0_4px_12px_-6px_rgba(0,24,249,0.2)] transition hover:-translate-y-0.5 hover:shadow-[0_8px_18px_-8px_rgba(0,24,249,0.3)]">
                            <span class="block text-[13px] font-semibold text-[#0a1633]">{{ $action['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </x-card>
        </div>

        {{-- Important dates + Announcements --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div>
                <x-important-dates :items="$importantDates" :view-all-url="route('teacher.important-dates')" :limit="5" />
            </div>
            <div>
                @include('announcements.feed', [
                    'announcements' => $announcements,
                    'unreadCount' => $announcementUnread,
                    'heading' => 'Announcements',
                    'context' => 'dashboard',
                    'limit' => 6,
                ])
            </div>
        </div>
    </div>
</x-layouts.app>
