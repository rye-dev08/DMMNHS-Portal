<x-layouts.app :title="'Manage Accounts'">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Manage Accounts</h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.accounts.create') }}"
               class="rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(16,185,129,0.7)] transition hover:brightness-110">+ New Account</a>
            <a href="{{ route('admin.dashboard') }}"
               class="rounded-lg bg-gradient-to-r from-[#64748b] to-[#475569] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(71,85,105,0.6)] transition hover:brightness-110">Dashboard</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.accounts') }}" class="mb-4 flex flex-wrap items-center gap-2.5">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search name, username, or email"
               class="min-w-[220px] flex-1 rounded-lg border border-[#0018f9]/25 bg-white p-2.5 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/20">
        <input type="hidden" name="role" value="{{ $role }}">
        <button type="submit" class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">Search</button>
        @if ($search !== '')
            <a href="{{ route('admin.accounts', ['role' => $role]) }}"
               class="rounded-lg border border-slate-300 px-4 py-2.5 text-[14px] text-slate-600 no-underline transition hover:bg-slate-50">Clear</a>
        @endif
    </form>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach (['all' => 'All', 'student' => 'Students', 'teacher' => 'Teachers', 'admin' => 'Admins'] as $key => $label)
            <a href="{{ route('admin.accounts', ['role' => $key, 'search' => $search]) }}"
               class="rounded-full px-4 py-1.5 text-[13px] font-semibold no-underline transition {{ $role === $key ? 'bg-gradient-to-r from-[#0018f9] to-[#0080fe] text-white shadow-[0_3px_10px_-3px_rgba(0,24,249,0.6)]' : 'border border-slate-300 bg-white text-slate-600 hover:border-[#0018f9]/40 hover:text-[#0018f9]' }}">
                {{ $label }}
            </a>
        @endforeach
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

    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Name</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Username</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Email</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Role</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Grade / Advisory</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Status</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="text-[14px]">
                @forelse ($users as $i => $u)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 font-medium text-[#0a1633]">{{ $u->name }}</td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $u->username }}</td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">{{ $u->email }}</td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="rounded-md px-2 py-0.5 text-[12px] font-semibold capitalize {{ $u->role === 'admin' ? 'bg-[#0018f9]/10 text-[#0018f9]' : ($u->role === 'teacher' ? 'bg-[#38bdf8]/15 text-[#0369a1]' : 'bg-[#10b981]/10 text-[#047857]') }}">{{ $u->role }}</span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5 text-slate-600">
                            @if ($u->role === 'student')
                                {{ $u->grade_level !== null ? 'Grade ' . $u->grade_level : 'Grade N/A' }}
                                <span class="text-slate-400">/ {{ !empty($u->section) ? $u->section : 'Unassigned' }}</span>
                            @elseif ($u->role === 'teacher')
                                {{ !empty($u->advisory_class) ? $u->advisory_class : 'Not set' }}
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <span class="rounded-full px-2.5 py-1 text-[12px] font-semibold {{ $u->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                {{ $u->status }}
                            </span>
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <a href="{{ route('admin.accounts.edit', $u->id) }}"
                                   class="rounded-md border border-[#0018f9]/30 bg-[#0018f9]/5 px-2.5 py-1 text-[12px] font-semibold text-[#0018f9] no-underline transition hover:bg-[#0018f9] hover:text-white">Edit</a>
                                <form method="POST" action="{{ route('admin.accounts.toggle-status', $u->id) }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="rounded-md border px-2.5 py-1 text-[12px] font-semibold transition {{ $u->status === 'active' ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                        {{ $u->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.accounts.destroy', $u->id) }}"
                                      onsubmit="return confirm('Delete this user and all their data?')" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-[#b91c1c] bg-[#dc2626] px-2.5 py-1 text-[12px] font-semibold text-white transition hover:bg-[#b91c1c]">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-[#6b7280]">No accounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="mt-3">{{ $users->links() }}</div>
    @endif
</x-layouts.app>