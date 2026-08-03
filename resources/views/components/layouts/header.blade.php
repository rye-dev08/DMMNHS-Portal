@php
    $role = auth()->check() ? auth()->user()->role : '';
    $homeUrl = match ($role) {
        'admin' => route('admin.dashboard'),
        'teacher' => route('teacher.dashboard'),
        'student' => route('student.dashboard'),
        default => route('login'),
    };
@endphp

<header class="sticky top-0 z-20 flex items-center gap-3 border-b border-white/10 bg-[linear-gradient(120deg,#0a1633,#0d2450,#164aa8)] px-4 py-3 text-white shadow-[0_4px_18px_rgba(2,6,23,0.25)] lg:px-6">
    <button id="menu-toggle" type="button" aria-label="Open Menu"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-[22px] text-white transition hover:bg-white/20 lg:hidden">
        &#9776;
    </button>

    <div class="flex items-center gap-3">
        <img src="{{ asset('images/dmnhs-no-bg.jpg') }}" alt="School Logo"
             class="h-11 w-11 rounded-[10px] border border-white/30 bg-white/10 object-cover">
        <div class="hidden sm:block">
            <h1 class="text-[16px] font-bold leading-tight">Don Mariano Marcos National High School Portal</h1>
            <p class="mt-0.5 text-[12px] text-white/65">Student Information and Grade Management System</p>
        </div>
    </div>

    <div class="ml-auto flex items-center gap-2.5">
        <span class="hidden rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[12px] font-medium capitalize text-white/80 md:inline-flex">
            {{ $role }} account
        </span>
        <a href="{{ $homeUrl }}" title="Home / Dashboard"
           class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-white no-underline transition hover:bg-white/20">
            &#8962;
        </a>
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           title="Logout"
           class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-white no-underline transition hover:bg-[#dc2626]/70">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
            </svg>
        </a>
    </div>
</header>