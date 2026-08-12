<x-layouts.app :title="'About Us'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">About Us</h2>
        </div>
        <nav class="flex flex-wrap gap-2">
            <a href="{{ route('contact') }}" class="rounded-full border border-[#0018f9]/25 bg-white px-3.5 py-2 text-[13px] font-semibold text-[#0018f9] no-underline transition hover:bg-[#0018f9]/10">Contact Us</a>
            @auth
                @php
                    $back = match (auth()->user()->role) {
                        'system_admin' => route('admin.dashboard'),
                        'office_admin' => route('office.dashboard'),
                        'teacher' => route('teacher.dashboard'),
                        default => route('student.dashboard'),
                    };
                @endphp
                <a href="{{ $back }}" class="rounded-full border border-[#0018f9]/25 bg-white px-3.5 py-2 text-[13px] font-semibold text-[#0018f9] no-underline transition hover:bg-[#0018f9]/10">Back to Dashboard</a>
            @endauth
        </nav>
    </div>

    <x-card :title="'Our Mission'">
        <p>Don Mariano National High School Portal helps admins, teachers, and students manage enrollment, advisory classes, schedules, and grade tracking in one centralized system.</p>
    </x-card>
    <br>
    <x-card :title="'Our Vision'">
        <p>Don Mariano National High School Portal helps admins, teachers, and students manage enrollment, advisory classes, schedules, and grade tracking in one centralized system.</p>
    </x-card>
    <br>
    <x-card :title="'What This Portal Supports'">
        <p>Account management, teacher profiles with enrollment limits, student enrollment requests, class scheduling, and grading workflows with transparent status updates.</p>
    </x-card>
</x-layouts.app>