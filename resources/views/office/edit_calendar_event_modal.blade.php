@php
    $modalId = $modalId ?? 'calendar-event-modal';
    $isEdit = isset($event) && $event !== null;
    $action = $isEdit ? route('office.academic-calendar.update', $event->id) : route('office.academic-calendar.store');
    $categories = $categories ?? array_keys(\App\Models\AcademicCalendarEvent::CATEGORIES);
    $years = $years ?? [];
    $settings = \App\Models\Setting::find(1);
    $defaultYear = (string) ($settings->current_school_year ?? date('Y') . '-' . (date('Y') + 1));
    $defaultTerm = (int) ($settings->current_term ?? 1);
    $oldDate = $event ? $event->event_date->format('Y-m-d') : '';
    $inputClass = 'rounded-lg border border-[#0018f9]/20 bg-white px-4 py-2.5 text-[14px] text-[#0a1633] shadow-sm outline-none transition focus:border-[#0018f9] focus:ring-2 focus:ring-[#0018f9]/15';
    $labelClass = 'text-[13px] font-semibold text-[#0a1633]';
@endphp

<dialog id="{{ $modalId }}" class="modal-modal">
    <form method="POST" action="{{ $action }}" class="grid gap-4 p-6 max-w-lg">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="grid gap-1.5">
            <label for="title-{{ $modalId }}" class="{{ $labelClass }}">Event Title *</label>
            <input id="title-{{ $modalId }}" name="title" type="text" required maxlength="150"
                   value="{{ old('title', $event->title ?? '') }}"
                   placeholder="e.g. First Quarter Examinations"
                   class="{{ $inputClass }}">
            @error('title')
                <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Date *</label>
                <input type="date" name="event_date" required value="{{ old('event_date', $oldDate) }}" class="{{ $inputClass }}">
                @error('event_date')
                    <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Category *</label>
                <select name="category" required class="futuristic-select px-4 py-2.5">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category', $event->category ?? '') === $cat ? 'selected' : '' }}>{{ \App\Models\AcademicCalendarEvent::CATEGORIES[$cat] }}</option>
                    @endforeach
                </select>
                @error('category')
                    <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Start Time</label>
                <input type="time" name="start_time" value="{{ old('start_time', $event->start_time ?? '') }}" class="{{ $inputClass }}">
                @error('start_time')
                    <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">End Time</label>
                <input type="time" name="end_time" value="{{ old('end_time', $event->end_time ?? '') }}" class="{{ $inputClass }}">
                @error('end_time')
                    <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">School Year *</label>
                <select name="school_year" required class="futuristic-select px-4 py-2.5">
                    @forelse ($years as $year)
                        <option value="{{ $year }}" {{ old('school_year', $event->school_year ?? $defaultYear) === $year ? 'selected' : '' }}>{{ $year }}</option>
                    @empty
                        <option value="{{ $defaultYear }}">{{ $defaultYear }}</option>
                    @endforelse
                </select>
                @error('school_year')
                    <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>
            <div class="grid gap-1.5">
                <label class="{{ $labelClass }}">Term *</label>
                <select name="term" required class="futuristic-select px-4 py-2.5">
                    @for ($t = 1; $t <= 3; $t++)
                        <option value="{{ $t }}" {{ (int) old('term', $event->term ?? $defaultTerm) === $t ? 'selected' : '' }}>Term {{ $t }}</option>
                    @endfor
                </select>
                @error('term')
                    <span class="text-[12px] text-[#dc2626]">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid gap-1.5">
            <label class="{{ $labelClass }}">Location</label>
            <input type="text" name="location" maxlength="150" value="{{ old('location', $event->location ?? '') }}"
                   placeholder="e.g. Covered Court, Library, Room 204" class="{{ $inputClass }}">
        </div>

        <div class="grid gap-1.5">
            <label class="{{ $labelClass }}">Short Description</label>
            <input type="text" name="short_description" maxlength="255" value="{{ old('short_description', $event->short_description ?? '') }}"
                   placeholder="One-liner shown in the calendar" class="{{ $inputClass }}">
        </div>

        <div class="grid gap-1.5">
            <label class="{{ $labelClass }}">Full Description</label>
            <textarea name="full_description" rows="3" placeholder="Additional details..."
                      class="{{ $inputClass }}">{{ old('full_description', $event->full_description ?? '') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2 justify-end">
            <button type="button"
                    onclick="document.getElementById('{{ $modalId }}').close()"
                    class="rounded-lg border border-[#0018f9]/20 bg-white px-6 py-2.5 font-semibold text-[#0a1633] shadow-sm transition hover:bg-[#f4f8ff]">
                Cancel
            </button>
            <button type="submit"
                    class="rounded-lg bg-gradient-to-r from-[#0a1633] to-[#164aa8] px-6 py-2.5 font-semibold text-white shadow-[0_4px_14px_-4px_rgba(10,22,51,0.6)] transition hover:brightness-110">
                {{ $isEdit ? 'Update Event' : 'Add Event' }}
            </button>
        </div>
    </form>
</dialog>

<style>
    .modal-modal {
        border: none;
        border-radius: 12px;
        box-shadow: 0 20px 50px -12px rgba(2, 6, 23, 0.35);
        background: white;
        max-width: 520px;
        width: 92%;
        padding: 0;
        margin: auto;
        inset: 0;
        position: fixed;
        align-items: center;
        justify-content: center;
    }
    .modal-modal::backdrop {
        background: rgba(10, 22, 51, 0.5);
        backdrop-filter: blur(4px);
    }
    .modal-modal[open] {
        display: flex;
    }
    .modal-modal form {
        width: 100%;
    }
</style>
