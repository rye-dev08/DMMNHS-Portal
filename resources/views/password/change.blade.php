@php
    $backUrl = match (auth()->user()->role) {
        'system_admin' => route('admin.dashboard'),
        'office_admin' => route('office.dashboard'),
        'teacher' => route('teacher.dashboard'),
        default => route('student.dashboard'),
    };
@endphp

<x-layouts.app :title="'Change Password'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Change Password</h2>
        </div>
        <a href="{{ $backUrl }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Dashboard</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-[13px] text-red-700">
            <ul class="m-0 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" data-validate
          class="relative mx-auto max-w-[560px] overflow-hidden rounded-2xl border border-[#0018f9]/15 bg-white/85 p-5 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] sm:p-6">
        @csrf

        <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>

        <div class="mb-5 flex items-center gap-3 border-b border-[#0018f9]/10 pb-4">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#0018f9] to-[#0080fe] text-white shadow-[0_5px_14px_-5px_rgba(0,24,249,0.7)]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </span>
            <div>
                <h3 class="m-0 text-[16px] font-semibold text-[#0a1633]">Change Password</h3>
                <p class="m-0 text-[12.5px] text-slate-500">Update your password below.</p>
            </div>
        </div>

        <div class="grid gap-4">
            <div class="grid gap-1.5">
                <label for="old_password" class="text-[13px] font-semibold text-[#0a1633]">Old Password</label>
                <input type="password" id="old_password" name="old_password" required
                       class="w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
            </div>

            <div class="grid gap-1.5">
                <label for="new_password" class="text-[13px] font-semibold text-[#0a1633]">New Password</label>
                <input type="password" id="new_password" name="new_password" required data-password-policy data-min="8"
                       class="w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                <p class="text-[12.5px] text-[#0a1633]/55">At least 8 chars, with uppercase or symbol.</p>
            </div>

            <div class="grid gap-1.5">
                <label for="confirm_password" class="text-[13px] font-semibold text-[#0a1633]">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required data-match="new_password"
                       class="w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
            </div>
        </div>

        <label class="mb-3 flex items-center gap-1.5 text-[13px] text-slate-600">
            <input type="checkbox" id="show-pass" class="accent-[#0018f9]"> <span>Show passwords</span>
        </label>

        <button type="submit"
                class="mt-5 w-full rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] p-3 text-[15px] font-semibold text-white shadow-[0_6px_18px_-6px_rgba(0,24,249,0.8)] transition hover:brightness-110 active:scale-[0.99]">
            Change Password
        </button>
    </form>

    <script>
        document.getElementById('show-pass').addEventListener('change', function () {
            var type = this.checked ? 'text' : 'password';
            document.getElementById('old_password').type = type;
            document.getElementById('new_password').type = type;
            document.getElementById('confirm_password').type = type;
        });
    </script>
</x-layouts.app>