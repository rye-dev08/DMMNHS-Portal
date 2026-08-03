<x-layouts.app :title="'Approve Teachers'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Approve Teachers</h2>
        </div>
        <a href="{{ route('admin.dashboard') }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Back to Dashboard</a>
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

    @if ($teachers->isEmpty())
        <div class="rounded-2xl border border-[#0018f9]/15 bg-white/80 p-8 text-center text-[14px] text-slate-500">
            No pending teachers to approve.
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Max Students</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Max Subjects Per Student</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Advisory Class</th>
                        <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teachers as $i => $t)
                        <form method="POST" action="{{ route('admin.approve-teachers.approve') }}"
                              id="approve-form-{{ $t->user_id }}" class="hidden">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $t->user_id }}">
                        </form>
                        <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                            <td class="border border-[#dbe4f0] p-2.5 font-medium text-[#0a1633]">{{ $t->name }}</td>
                            <td class="border border-[#dbe4f0] p-2.5">
                                <input type="number" form="approve-form-{{ $t->user_id }}" name="max_students" min="1"
                                       value="{{ $defaultMaxStudents }}" required
                                       class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                            </td>
                            <td class="border border-[#dbe4f0] p-2.5">
                                <input type="number" form="approve-form-{{ $t->user_id }}" name="max_subjects" min="1"
                                       value="{{ $defaultMaxSubjects }}" required
                                       class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                            </td>
                            <td class="border border-[#dbe4f0] p-2.5">
                                <input type="text" form="approve-form-{{ $t->user_id }}" name="advisory_class"
                                       value="{{ $t->advisory_class }}" placeholder="e.g. 7-A"
                                       class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                            </td>
                            <td class="border border-[#dbe4f0] p-2.5">
                                <button type="submit" form="approve-form-{{ $t->user_id }}"
                                        class="rounded-lg bg-gradient-to-r from-[#10b981] to-[#059669] px-4 py-2 font-semibold text-white shadow-[0_4px_12px_-4px_rgba(16,185,129,0.7)] transition hover:brightness-110">
                                    Approve
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-[#6b7280]">No pending teachers.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.app>