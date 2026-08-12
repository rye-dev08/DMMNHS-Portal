<x-layouts.app :title="'Contact Us'">
    @php
        $prefillName = auth()->check() ? auth()->user()->name : '';
        $prefillEmail = auth()->check() ? auth()->user()->email ?? '' : '';
    @endphp
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Contact Us</h2>
        </div>
        <nav class="flex flex-wrap gap-2">
            <a href="{{ route('about') }}" class="rounded-full border border-[#0018f9]/25 bg-white px-3.5 py-2 text-[13px] font-semibold text-[#0018f9] no-underline transition hover:bg-[#0018f9]/10">About Us</a>
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

    <x-card :title="'School Office'">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div>
                <span class="block text-[12px] font-semibold uppercase tracking-wide text-[#0018f9]/70">Email</span>
                <span class="block text-[14px] font-medium text-slate-800">registrar@dmnhs.edu</span>
            </div>
            <div>
                <span class="block text-[12px] font-semibold uppercase tracking-wide text-[#0018f9]/70">Phone</span>
                <span class="block text-[14px] font-medium text-slate-800">+63 900 000 0000</span>
            </div>
            <div>
                <span class="block text-[12px] font-semibold uppercase tracking-wide text-[#0018f9]/70">Address</span>
                <span class="block text-[14px] font-medium text-slate-800">Don Mariano National High School, Philippines</span>
            </div>
        </div>
    </x-card>

    <h3 class="mb-1 mt-5 text-center text-[15px] font-semibold text-[#0a1633]">Send a Message</h3>

    @if ($blocked)
        <div class="mx-auto mt-3 flex max-w-[680px] items-start gap-3 rounded-xl border border-red-200 bg-red-50/80 px-4 py-3.5">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="mt-0.5 h-5 w-5 shrink-0 text-red-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <div>
                <p class="text-[14px] font-semibold text-red-700">You are currently unable to send messages to the administration.</p>
                <p class="mt-0.5 text-[13px] text-red-600/80">If you believe this is a mistake, please contact your administrator directly.</p>
            </div>
        </div>
    @endif

    <form class="mx-auto my-4 max-w-[680px] rounded-2xl border border-[#0018f9]/15 bg-white/80 p-5 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] sm:p-6" method="POST" action="{{ route('contact.submit') }}">
        @csrf
        @if ($isSender)
            <div class="mb-4 flex items-start gap-2.5 rounded-lg border border-[#0018f9]/10 bg-[#0018f9]/5 px-3.5 py-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="mt-0.5 h-4 w-4 shrink-0 text-[#0018f9]">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <p class="text-[12.5px] leading-snug text-[#0018f9]/90">
                    This message is sent under your portal account. You have
                    <span class="font-semibold">{{ $remaining }} of {{ $limit }} messages remaining today</span>.
                </p>
            </div>
        @endif

        @if ($limitReached && ! $blocked)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-3 text-[13.5px] font-medium text-amber-800">
                You have reached your daily message limit. You can send another message tomorrow.
            </div>
        @endif

        <div class="mb-3 grid gap-1.5">
            <label for="name" class="text-[13px] font-semibold text-[#0a1633]">Name</label>
            <input type="text" id="name" name="name" placeholder="Your name" required value="{{ old('name', $prefillName) }}"
                   {{ $isSender || $blocked || $limitReached ? 'readonly' : '' }}
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15 {{ $isSender ? 'bg-slate-50 text-slate-700' : '' }} disabled:cursor-not-allowed">
            @error('name') <span class="text-[13px] text-red-600">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3 grid gap-1.5">
            <label for="email" class="text-[13px] font-semibold text-[#0a1633]">Email</label>
            <input type="email" id="email" name="email" placeholder="Your email" required value="{{ old('email', $prefillEmail) }}"
                   {{ $isSender || $blocked || $limitReached ? 'readonly' : '' }}
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15 {{ $isSender ? 'bg-slate-50 text-slate-700' : '' }} disabled:cursor-not-allowed">
            @error('email') <span class="text-[13px] text-red-600">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3 grid gap-1.5">
            <label for="subject" class="text-[13px] font-semibold text-[#0a1633]">Subject</label>
            <input type="text" id="subject" name="subject" placeholder="Topic (optional)" value="{{ old('subject') }}"
                   {{ $blocked || $limitReached ? 'disabled' : '' }}
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15 disabled:cursor-not-allowed disabled:bg-slate-50">
        </div>
        <div class="mb-3 grid gap-1.5">
            <label for="message" class="text-[13px] font-semibold text-[#0a1633]">Message</label>
            <textarea id="message" name="message" rows="4" placeholder="Type your message" required
                      {{ $blocked || $limitReached ? 'disabled' : '' }}
                      class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15 disabled:cursor-not-allowed disabled:bg-slate-50">{{ old('message') }}</textarea>
            @error('message') <span class="text-[13px] text-red-600">{{ $message }}</span> @enderror
        </div>
        <button type="submit" {{ $blocked || $limitReached ? 'disabled' : '' }}
                class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-5 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">Send Message</button>
    </form>
</x-layouts.app>
