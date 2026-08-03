@php
    $backUrl = match (auth()->user()->role) {
        'admin' => route('admin.dashboard'),
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

    <form method="POST" action="{{ route('password.update') }}" data-validate
          class="mx-auto my-4 max-w-[430px] rounded-2xl border border-[#0018f9]/15 bg-white/80 p-5 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] sm:p-6">
        @csrf
        <div class="mb-3.5 grid gap-1.5">
            <label for="old_password" class="text-[13px] font-semibold text-[#0a1633]">Old Password</label>
            <input type="password" id="old_password" name="old_password" required
                   class="w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
        </div>
        <div class="mb-3.5 grid gap-1.5">
            <label for="new_password" class="text-[13px] font-semibold text-[#0a1633]">New Password</label>
            <input type="password" id="new_password" name="new_password" required data-password-policy data-min="8"
                   class="w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
            <p class="text-[12.5px] text-[#0a1633]/55">At least 8 chars, with uppercase or symbol.</p>
        </div>
        <div class="mb-3.5 grid gap-1.5">
            <label for="confirm_password" class="text-[13px] font-semibold text-[#0a1633]">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required data-match="new_password"
                   class="w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
        </div>

        <label class="mb-3 flex items-center gap-1.5 text-[13px] text-slate-600">
            <input type="checkbox" id="show-pass" class="accent-[#0018f9]"> <span>Show passwords</span>
        </label>

        <button type="submit"
                class="w-full rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110 active:scale-[0.99]">
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