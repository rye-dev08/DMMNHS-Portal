<x-layouts.app :title="'Teacher Advisory'">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-block h-5 w-1.5 rounded-full bg-gradient-to-b from-[#0018f9] to-[#38bdf8]"></span>
            <h2 class="m-0 text-[#0a1633]">Teacher Advisory</h2>
        </div>
        <a href="{{ route('admin.enrollment-settings') }}"
           class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-4 py-2 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">Term &amp; Enrollment</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-[#0018f9]/15 shadow-[0_6px_20px_-8px_rgba(0,24,249,0.15)]">
        <table class="w-full border-collapse text-[14px]">
            <thead>
                <tr class="bg-gradient-to-r from-[#0a1633] via-[#0d2450] to-[#164aa8] text-left text-white">
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Teacher</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Advisory Class</th>
                    <th class="border border-[#0a1633] p-2.5 text-[13px] font-semibold tracking-wide">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teachers as $i => $t)
                    <form method="POST" action="{{ route('admin.enrollment-settings.advisory') }}"
                          id="advisory-form-{{ $t->user_id }}" class="hidden">
                        @csrf
                        <input type="hidden" name="teacher_user_id" value="{{ $t->user_id }}">
                    </form>
                    <tr class="{{ $i % 2 === 0 ? 'bg-white/90' : 'bg-[#f4f8ff]/80' }} transition hover:bg-[#eaf3ff]">
                        <td class="border border-[#dbe4f0] p-2.5 font-medium text-[#0a1633]">{{ $t->name }}</td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <input type="text" form="advisory-form-{{ $t->user_id }}" name="advisory_class"
                                   value="{{ $t->advisory_class }}" placeholder="e.g. Grade 11-A" size="15"
                                   class="rounded-lg border border-[#0018f9]/20 bg-white p-2 text-[14px] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15">
                        </td>
                        <td class="border border-[#dbe4f0] p-2.5">
                            <button type="submit" form="advisory-form-{{ $t->user_id }}"
                                    class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-4 py-2 font-semibold text-white shadow-[0_4px_12px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">Save</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-8 text-center text-[#6b7280]">No active teachers.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>