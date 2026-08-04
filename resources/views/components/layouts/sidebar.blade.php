@php
    $role = auth()->check() ? auth()->user()->role : '';
    $homeUrl = match ($role) {
        'admin' => route('admin.dashboard'),
        'teacher' => route('teacher.dashboard'),
        'student' => route('student.dashboard'),
        default => route('login'),
    };

    $icon = function (string $key) {
        $patterns = [
            'grid' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />',
            'plus' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />',
            'badge' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />',
            'sliders' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />',
            'book' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />',
            'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />',
            'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />',
            'id' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />',
            'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />',
            'tick' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
            'info' => '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />',
            'phone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />',
            'person' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />',
            'key' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />',
        ];
        return $patterns[$key] ?? $patterns['grid'];
    };

    $items = match ($role) {
        'admin' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'grid'],
            ['label' => 'Manage Accounts', 'route' => 'admin.accounts', 'icon' => 'users'],
            ['label' => 'Create Account', 'route' => 'admin.accounts.create', 'icon' => 'plus'],
            ['label' => 'Enrollment Settings', 'route' => 'admin.enrollment-settings', 'icon' => 'sliders'],
            ['label' => 'Teacher Advisory', 'route' => 'admin.teacher-advisory', 'icon' => 'book'],
        ],
        'teacher' => [
            ['label' => 'Dashboard', 'route' => 'teacher.dashboard', 'icon' => 'grid'],
            ['label' => 'Advisory Portal', 'route' => 'teacher.advisory-portal', 'icon' => 'book'],
            ['label' => 'Enrollment Requests', 'route' => 'teacher.enrollment-requests', 'icon' => 'clipboard'],
            ['label' => 'Submit Grades', 'route' => 'teacher.submit-grades', 'icon' => 'tick'],
            ['label' => 'Grades Overview', 'route' => 'teacher.grades-overview', 'icon' => 'chart'],
        ],
        'student' => [
            ['label' => 'Dashboard', 'route' => 'student.dashboard', 'icon' => 'grid'],
            ['label' => 'Class Schedule', 'route' => 'student.schedule', 'icon' => 'calendar'],
            ['label' => 'Grades', 'route' => 'student.grades', 'icon' => 'chart'],
            ['label' => 'Enrollment Request', 'route' => 'student.enrollment', 'icon' => 'clipboard'],
        ],
        default => [],
    };

    $secondaryItems = [
        ['label' => 'My Info', 'route' => $role === 'student' ? 'student.info' : ($role === 'teacher' ? 'teacher.info' : ''), 'icon' => 'person', 'role' => ['student', 'teacher']],
        ['label' => 'Change Password', 'route' => 'password.change', 'icon' => 'key'],
        ['label' => 'About Us', 'route' => 'about', 'icon' => 'info'],
        ['label' => 'Contact Us', 'route' => 'contact', 'icon' => 'phone'],
    ];
@endphp

<form method="POST" action="{{ route('logout') }}" id="logout-form" class="hidden">
    @csrf
</form>

<div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-900/60 backdrop-blur-sm lg:hidden"></div>

    <aside id="app-sidebar"
           class="fixed inset-y-0 left-0 z-40 flex w-[264px] -translate-x-full flex-col border-r border-white/10 bg-gradient-to-b from-[#0a1633] via-[#0d2450] to-[#164aa8] transition-all duration-300 lg:translate-x-0">

    {{-- Futuristic background --}}
    <x-decorative-background :grid-size="'30px'" />

    <div class="relative z-10 flex flex-1 flex-col">
        {{-- Logo --}}
        <a href="{{ $homeUrl }}" class="sidebar-logo flex items-center gap-3 border-b border-white/10 px-5 py-5 no-underline">
             <img src="{{ asset('images/dmnhs-no-bg.jpg') }}" alt="School Logo"
                  class="h-11 w-11 rounded-[10px] border border-white/30 bg-white/10 object-cover shadow-[0_0_18px_rgba(45,125,246,0.45)]">
             <div>
                 <span class="sidebar-brand-text block text-[14px] font-bold leading-tight text-white">DMMNHS</span>
                 <span class="sidebar-brand-text block text-[11px] tracking-wide text-white/60">Student Portal</span>
             </div>
         </a>

        {{-- Profile chip --}}
        <div class="sidebar-profile mx-4 mt-4 flex items-center gap-2.5 rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 backdrop-blur">
             <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[#38bdf8] to-[#2563eb] text-[14px] font-bold text-white shadow-[0_0_12px_rgba(56,189,248,0.5)]">
                 {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
             </div>
             <div class="sidebar-chip-text min-w-0">
                 <p class="truncate text-[13px] font-semibold text-white">{{ auth()->user()->name }}</p>
                 <p class="text-[11px] capitalize text-white/55">{{ auth()->user()->role }}</p>
             </div>
         </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav mt-4 flex-1 overflow-y-auto px-3 pb-4">
            <p class="sidebar-section-label px-2 pb-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/40">Menu</p>
            <ul class="grid gap-1">
                @foreach ($items as $item)
                    @php
                        $active = request()->routeIs($item['route']);
                    @endphp
                    <li>
                        <a href="{{ route($item['route']) }}"
                           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13.5px] font-medium transition
                                  {{ $active
                                      ? 'bg-gradient-to-r from-[#1d4ed8]/80 to-[#2563eb]/40 text-white shadow-[0_0_16px_rgba(37,99,235,0.45)]'
                                      : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7"
                                 stroke="currentColor"
                                 class="{{ $active ? 'text-[#7dc6ff]' : 'text-white/45 group-hover:text-white/80' }} h-[18px] w-[18px] shrink-0">
                                {!! $icon($item['icon']) !!}
                            </svg>
                             <span class="sidebar-label">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <p class="sidebar-section-label mb-1.5 mt-4 px-2 pt-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/40">Account</p>
            <div class="sidebar-secondary-nav grid gap-1">
                @foreach ($secondaryItems as $item)
                    @php
                        if ($item['route'] === '' || ! empty($item['role']) && ! in_array($role, $item['role'])) {
                            continue;
                        }
                        $active = request()->routeIs($item['route']);
                    @endphp
                    <li class="list-none">
                        <a href="{{ route($item['route']) }}"
                           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13.5px] font-medium
                                  {{ $active
                                    ? 'bg-gradient-to-r from-[#1d4ed8]/40 to-[#2563eb]/20 text-white'
                                    : 'text-white/60 hover:bg-white/10 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                 stroke="currentColor" class="h-[18px] w-[18px] shrink-0 text-white/45">
                                {!! $icon($item['icon']) !!}
                            </svg>
                             <span class="sidebar-label">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </div>
         </nav>

        {{-- Logout footer --}}
        <div class="sidebar-footer border-t border-white/10 p-3">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13.5px] font-medium text-white/70 transition hover:bg-[#dc2626]/15 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-[18px] w-[18px] shrink-0 text-white/50">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                <span class="sidebar-label">Logout</span>
            </a>
        </div>
    </div>

    {{-- Sidebar edge collapse toggle --}}
    <button id="sidebar-collapse-toggle" type="button" aria-label="Collapse sidebar"
            class="sidebar-collapse-btn hidden lg:inline-flex h-7 w-7 items-center justify-center rounded-full border border-[#a78bfa]/25 bg-[#8b5cf6]/25 text-[#c4b5f5] backdrop-blur transition hover:bg-[#a78bfa]/35 hover:text-white">
        <svg data-sidebar-arrow-open class="sidebar-arrow sidebar-arrow-open h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
        <svg data-sidebar-arrow-closed class="sidebar-arrow sidebar-arrow-closed h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
    </button>
</aside>