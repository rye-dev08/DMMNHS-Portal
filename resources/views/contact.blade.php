<x-layouts.app :title="'Contact Us'">
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
                        'admin' => route('admin.dashboard'),
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
    <form class="mx-auto my-4 max-w-[680px] rounded-2xl border border-[#0018f9]/15 bg-white/80 p-5 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] sm:p-6" method="POST" action="{{ route('contact.submit') }}">
        @csrf
        <div class="mb-3 grid gap-1.5">
            <label for="name" class="text-[13px] font-semibold text-[#0a1633]">Name</label>
            <input type="text" id="name" name="name" placeholder="Your name" required value="{{ old('name') }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
            @error('name') <span class="text-[13px] text-red-600">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3 grid gap-1.5">
            <label for="email" class="text-[13px] font-semibold text-[#0a1633]">Email</label>
            <input type="email" id="email" name="email" placeholder="Your email" required value="{{ old('email') }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
            @error('email') <span class="text-[13px] text-red-600">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3 grid gap-1.5">
            <label for="subject" class="text-[13px] font-semibold text-[#0a1633]">Subject</label>
            <input type="text" id="subject" name="subject" placeholder="Topic (optional)" value="{{ old('subject') }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
        </div>
        <div class="mb-3 grid gap-1.5">
            <label for="message" class="text-[13px] font-semibold text-[#0a1633]">Message</label>
            <textarea id="message" name="message" rows="4" placeholder="Type your message" required
                      class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">{{ old('message') }}</textarea>
            @error('message') <span class="text-[13px] text-red-600">{{ $message }}</span> @enderror
        </div>
        <button type="submit"
                class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-5 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110 active:scale-[0.99]">Send Message</button>
    </form>
</x-layouts.app>