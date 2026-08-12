<x-layouts.app :title="'Edit Account'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Edit Account: {{ $user->name }}</h2>
        </div>
        <a href="{{ route('admin.accounts') }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">← Manage Accounts</a>
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

    {{-- Info chips --}}
    <div class="mb-5 flex flex-wrap gap-2">
        <span class="rounded-lg border border-[#0018f9]/20 bg-white px-3 py-1.5 text-[12.5px] text-slate-600">Username: <strong class="text-[#0a1633]">{{ $user->username }}</strong></span>
        <span class="rounded-lg border border-[#0018f9]/20 bg-white px-3 py-1.5 text-[12.5px] text-slate-600">Role: <strong class="capitalize text-[#0018f9]">{{ str_replace('_', ' ', $user->role) }}</strong></span>
        <span class="rounded-lg border px-3 py-1.5 text-[12.5px] text-slate-600 {{ $user->status === 'active' ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-slate-100 text-slate-600' }}">Status: <strong>{{ $user->status }}</strong></span>
    </div>

    <form method="POST" action="{{ route('admin.accounts.update', $user) }}" data-validate class="mb-6 max-w-[900px] rounded-2xl border border-[#0018f9]/15 bg-white/80 p-5 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] sm:p-6">
        @csrf
        @method('PUT')

        <div class="mb-4 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-[#0018f9] to-[#0080fe] text-[13px] font-bold text-white">1</span>
            <h3 class="m-0 text-[15px] font-semibold text-[#0a1633]">Account Details</h3>
        </div>
        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
            <input type="text" name="name" placeholder="Full Name" required value="{{ old('name', $user->name) }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
            <input type="email" name="email" placeholder="Email" required value="{{ old('email', $user->email) }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
        </div>

        @if ($user->role === 'student')
            <div class="mb-3 mt-6 flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-[#0018f9] to-[#0080fc] text-[13px] font-bold text-white">2</span>
                <h3 class="m-0 text-[15px] font-semibold text-[#0a1633]">Student Profile</h3>
            </div>
            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-4">
                <select name="sex" class="futuristic-select px-2.5 py-1.5 text-[14px]">
                    <option value="">Sex</option>
                    <option value="M" {{ old('sex', $user->student->sex ?? '') === 'M' ? 'selected' : '' }}>Male</option>
                    <option value="F" {{ old('sex', $user->student->sex ?? '') === 'F' ? 'selected' : '' }}>Female</option>
                </select>
                <input type="date" name="birthday" value="{{ old('birthday', $user->student->birthday ?? '') }}"
                       class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
                <input type="number" name="age" placeholder="Age" value="{{ old('age', $user->student->age ?? '') }}"
                       class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
                <select name="grade_level" class="futuristic-select px-2.5 py-1.5 text-[14px]">
                    <option value="">Grade Level</option>
                    <option value="7" {{ old('grade_level', $user->student->grade_level ?? '') === '7' ? 'selected' : '' }}>Grade 7</option>
                    <option value="8" {{ old('grade_level', $user->student->grade_level ?? '') === '8' ? 'selected' : '' }}>Grade 8</option>
                    <option value="9" {{ old('grade_level', $user->student->grade_level ?? '') === '9' ? 'selected' : '' }}>Grade 9</option>
                    <option value="10" {{ old('grade_level', $user->student->grade_level ?? '') === '10' ? 'selected' : '' }}>Grade 10</option>
                    <option value="11" {{ old('grade_level', $user->student->grade_level ?? '') === '11' ? 'selected' : '' }}>Grade 11</option>
                    <option value="12" {{ old('grade_level', $user->student->grade_level ?? '') === '12' ? 'selected' : '' }}>Grade 12</option>
                </select>
            </div>
        @elseif ($user->role === 'teacher')
            <div class="mb-3 mt-6 flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-[#0018f9] to-[#0080fc] text-[13px] font-bold text-white">2</span>
                <h3 class="m-0 text-[15px] font-semibold text-[#0a1633]">Teacher Profile</h3>
            </div>
            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                <select name="grade_level" class="futuristic-select px-2.5 py-1.5 text-[14px]">
                    <option value="">Grade Level</option>
                    <option value="7" {{ old('grade_level', $user->teacher->grade_level ?? '') === '7' ? 'selected' : '' }}>Grade 7</option>
                    <option value="8" {{ old('grade_level', $user->teacher->grade_level ?? '') === '8' ? 'selected' : '' }}>Grade 8</option>
                    <option value="9" {{ old('grade_level', $user->teacher->grade_level ?? '') === '9' ? 'selected' : '' }}>Grade 9</option>
                    <option value="10" {{ old('grade_level', $user->teacher->grade_level ?? '') === '10' ? 'selected' : '' }}>Grade 10</option>
                    <option value="11" {{ old('grade_level', $user->teacher->grade_level ?? '') === '11' ? 'selected' : '' }}>Grade 11</option>
                    <option value="12" {{ old('grade_level', $user->teacher->grade_level ?? '') === '12' ? 'selected' : '' }}>Grade 12</option>
                </select>
                <input type="text" name="advisory_class" placeholder="Advisory Class (e.g. 11-A)" value="{{ old('advisory_class', $user->teacher->advisory_class ?? '') }}"
                       class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
                <input type="number" name="max_students" placeholder="Max Students" min="0" value="{{ old('max_students', $user->teacher->max_students ?? 0) }}"
                       class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
                <input type="number" name="max_subjects" placeholder="Max Subjects" min="0" value="{{ old('max_subjects', $user->teacher->max_subjects ?? 0) }}"
                       class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
            </div>
        @endif

        <button type="submit" class="mt-6 rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-7 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">
            Save Changes
        </button>
    </form>

    <div class="max-w-[900px] rounded-2xl border border-amber-300/40 bg-amber-50/60 p-5 shadow-[0_8px_24px_-10px_rgba(245,158,11,0.25)] sm:p-6">
        <div class="mb-4 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 text-[13px] font-bold text-white">!</span>
            <h3 class="m-0 text-[15px] font-semibold text-[#0a1633]">Reset Password</h3>
        </div>
        <form method="POST" action="{{ route('admin.accounts.reset-password', $user) }}" data-validate class="max-w-[620px]">
            @csrf
            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                <input type="password" name="password" placeholder="New Password" required data-password-policy data-min="8"
                       class="rounded-lg border border-amber-300/60 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                <input type="password" name="password_confirmation" placeholder="Confirm New Password" required
                       class="rounded-lg border border-amber-300/60 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
            </div>
            <p class="mt-1.5 text-[12.5px] text-[#0a1633]/55">Password rule: at least 8 characters, with uppercase or symbol.</p>
            <button type="submit" class="mt-3 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(245,158,11,0.7)] transition hover:brightness-110">
                Reset Password
            </button>
        </form>
    </div>
</x-layouts.app>