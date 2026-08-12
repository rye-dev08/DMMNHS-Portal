<x-layouts.app :title="'Create Account'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Create Account</h2>
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

    <form method="POST" action="{{ route('admin.accounts.store') }}" data-validate class="mx-auto max-w-[900px] rounded-2xl border border-[#0018f9]/15 bg-white/80 p-5 shadow-[0_8px_24px_-10px_rgba(0,24,249,0.18)] sm:p-6">
        @csrf

        {{-- Section: Credentials --}}
        <div class="mb-4 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-[#0018f9] to-[#0080fe] text-[13px] font-bold text-white">1</span>
            <h3 class="m-0 text-[15px] font-semibold text-[#0a1633]">Login Credentials</h3>
        </div>
        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
            <input type="text" name="name" placeholder="Full Name" required value="{{ old('name') }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
            <input type="text" name="username" placeholder="Username" required value="{{ old('username') }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
            <input type="email" name="email" placeholder="Email" required value="{{ old('email') }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
            <div class="relative">
                <input type="password" id="password" name="password" placeholder="Password" required data-password-policy data-min="8"
                       class="w-full rounded-lg border border-[#0018f9]/20 bg-white p-2.5 pr-14 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                <label class="absolute right-2 top-1/2 -translate-y-1/2 cursor-pointer text-[13px] font-medium text-slate-500 select-none">
                    <input type="checkbox" id="show-create-pass" class="mr-1 accent-[#0018f9]"> Show
                </label>
            </div>
        </div>
        <p class="mt-1.5 text-[12.5px] text-[#0a1633]/55">Password rule: at least 8 characters, with uppercase or symbol.</p>

        {{-- Section: Role --}}
        <div class="mb-3 mt-6 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-[#0018f9] to-[#0080fc] text-[13px] font-bold text-white">2</span>
            <h3 class="m-0 text-[15px] font-semibold text-[#0a1633]">Account Role</h3>
        </div>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            @foreach ([
                'student' => ['Student', 'Enrolled learners with class schedule & grades.'],
                'teacher' => ['Teacher', 'Manages subjects, grades & enrollment.'],
                'office_admin' => ['Office Administrator', 'Academic calendar, announcements & requirements.'],
                'system_admin' => ['System Administrator', 'Full control over accounts & settings.'],
            ] as $val => [$rlabel, $desc])
                <label class="relative flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition {{ old('role', 'student') === $val ? 'border-[#0018f9]/60 bg-[#0018f9]/5 shadow-[0_0_0_1px_rgba(0,24,249,0.35)]' : 'border-slate-200 bg-white hover:border-[#0018f9]/30' }}">
                    <input type="radio" name="role" value="{{ $val }}" class="mt-1 accent-[#0018f9]" {{ old('role', 'student') === $val ? 'checked' : '' }}>
                    <span>
                        <span class="block text-[13.5px] font-semibold text-[#0a1633]">{{ $rlabel }}</span>
                        <span class="block text-[12px] leading-snug text-slate-500">{{ $desc }}</span>
                    </span>
                </label>
            @endforeach
        </div>

        {{-- Section: Student info --}}
        <div class="mb-3 mt-6 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-[#0018f9] to-[#0080fc] text-[13px] font-bold text-white">3</span>
            <h3 class="m-0 text-[15px] font-semibold text-[#0a1633]">Student Profile <span class="text-[12.5px] font-normal text-[#0a1633]/50">(only for students)</span></h3>
        </div>
        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-4">
            <select name="sex" class="futuristic-select px-2.5 py-1.5 text-[14px]">
                <option value="">Sex</option>
                <option value="M" {{ old('sex') === 'M' ? 'selected' : '' }}>Male</option>
                <option value="F" {{ old('sex') === 'F' ? 'selected' : '' }}>Female</option>
            </select>
            <input type="date" name="birthday" value="{{ old('birthday') }}" class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
            <input type="number" name="age" placeholder="Age" value="{{ old('age') }}" class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
            <select name="grade_level" class="futuristic-select px-2.5 py-1.5 text-[14px]">
                <option value="">Grade Level</option>
                <option value="7" {{ old('grade_level') === '7' ? 'selected' : '' }}>Grade 7</option>
                <option value="8" {{ old('grade_level') === '8' ? 'selected' : '' }}>Grade 8</option>
                <option value="9" {{ old('grade_level') === '9' ? 'selected' : '' }}>Grade 9</option>
                <option value="10" {{ old('grade_level') === '10' ? 'selected' : '' }}>Grade 10</option>
                <option value="11" {{ old('grade_level') === '11' ? 'selected' : '' }}>Grade 11</option>
                <option value="12" {{ old('grade_level') === '12' ? 'selected' : '' }}>Grade 12</option>
            </select>
        </div>

        {{-- Section: Teacher profile --}}
        <div class="mb-3 mt-6 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-[#0018f9] to-[#0080fc] text-[13px] font-bold text-white">4</span>
            <h3 class="m-0 text-[15px] font-semibold text-[#0a1633]">Teacher Profile <span class="text-[12.5px] font-normal text-[#0a1633]/50">(only for teachers, approved on creation)</span></h3>
        </div>
        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
            <input type="text" name="advisory_class" placeholder="Advisory Class (e.g. 11-A)" value="{{ old('advisory_class') }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
            <input type="number" name="max_students" placeholder="Max Students" min="1" value="{{ old('max_students') }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
            <input type="number" name="max_subjects" placeholder="Max Subjects Per Student" min="1" value="{{ old('max_subjects') }}"
                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9]">
        </div>
        <p class="mt-1.5 text-[12.5px] text-[#0a1633]/55">Leave blank to use the default limits from enrollment settings. The teacher account is activated and approved immediately.</p>

        <button type="submit" class="mt-6 rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] px-7 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(16,185,129,0.7)] transition hover:brightness-110">
            Create Account
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var cb = document.getElementById('show-create-pass');
            var pw = document.getElementById('password');
            cb.addEventListener('change', function () {
                pw.type = cb.checked ? 'text' : 'password';
            });
        });
    </script>
</x-layouts.app>