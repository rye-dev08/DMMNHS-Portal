<?php

namespace App\Http\Controllers\OfficeAdmin;

use App\Http\Controllers\AcademicCalendarController as SharedCalendarController;
use App\Http\Controllers\Controller;
use App\Models\AcademicCalendarEvent;
use App\Models\Setting;
use App\Services\NotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AcademicCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $settings = Setting::find(1);
        $currentYear = (string) ($settings->current_school_year ?? date('Y').'-'.(date('Y') + 1));

        $years = AcademicCalendarEvent::query()
            ->distinct()
            ->pluck('school_year')
            ->map(fn ($year) => (string) $year)
            ->merge([$currentYear, SharedCalendarController::nextSchoolYear($currentYear)])
            ->unique()
            ->sort()
            ->values();

        $categories = array_keys(AcademicCalendarEvent::CATEGORIES);

        $filterYear = (string) $request->input('school_year', $currentYear);
        $filterTerm = (string) $request->input('term', '');
        $filterCategory = (string) $request->input('category', '');

        $query = AcademicCalendarEvent::query();
        if ($filterYear !== '') {
            $query->where('school_year', $filterYear);
        }
        if ($filterTerm !== '') {
            $query->where('term', (int) $filterTerm);
        }
        if ($filterCategory !== '') {
            $query->where('category', $filterCategory);
        }

        $events = $query->orderBy('event_date')->orderBy('start_time')->get();

        $syStart = SharedCalendarController::schoolYearStart($filterYear);
        $syEnd = SharedCalendarController::schoolYearEnd($filterYear);

        $now = CarbonImmutable::today();
        $defaultPreview = $now->between($syStart, $syEnd) ? $now->startOfMonth() : $syStart;

        $pm = (int) $request->integer('pm', $defaultPreview->month);
        $candidate = CarbonImmutable::createFromDate($syStart->year, $pm, 1);
        if ($candidate->lt($syStart)) {
            $candidate = CarbonImmutable::createFromDate($syStart->year + 1, $pm, 1);
        }
        $previewMonth = $candidate->lt($syStart) ? $syStart : $candidate;
        $previewMonth = $previewMonth->gt($syEnd) ? $syEnd : $previewMonth;
        $previewMonth = $previewMonth->startOfMonth();

        $previewEvents = AcademicCalendarEvent::query()
            ->where('school_year', $filterYear)
            ->whereBetween('event_date', [$previewMonth->toDateString(), $previewMonth->endOfMonth()->toDateString()])
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($event) => $event->event_date->format('Y-m-d'));

        $todayKey = $now->format('Y-m-d');

        $prev = $previewMonth->subMonthNoOverflow();
        $next = $previewMonth->addMonthNoOverflow();

        return view('office.academic_calendar', [
            'settings' => $settings,
            'currentYear' => $currentYear,
            'years' => $years,
            'categories' => $categories,
            'filterYear' => $filterYear,
            'filterTerm' => $filterTerm,
            'filterCategory' => $filterCategory,
            'events' => $events,
            'previewMonthLabel' => $previewMonth->format('F Y'),
            'previewGrid' => SharedCalendarController::buildGrid($previewMonth, $todayKey),
            'previewEvents' => $previewEvents,
            'previewPrevUrl' => $prev->gte($syStart) ? $this->previewUrl($prev) : null,
            'previewNextUrl' => $next->lte($syEnd) ? $this->previewUrl($next) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        AcademicCalendarEvent::create($data);

        app(NotificationService::class)->calendarEventCreated(
            $data['title'],
            $data['school_year'],
            $data['term']
        );

        flash_notice('Calendar event added successfully!', 'success');

        return redirect()->route('office.academic-calendar', $this->filterQuery());
    }

    public function edit(int $eventId): View
    {
        $event = AcademicCalendarEvent::findOrFail($eventId);

        $categories = array_keys(AcademicCalendarEvent::CATEGORIES);
        $years = AcademicCalendarEvent::query()
            ->distinct()
            ->pluck('school_year')
            ->map(fn ($year) => (string) $year)
            ->merge([(string) ($event->school_year ?? '')])
            ->unique()
            ->sort()
            ->values();

        return view('office.edit_calendar_event_modal', [
            'event' => $event,
            'modalId' => 'edit-calendar-event-modal',
            'categories' => $categories,
            'years' => $years,
        ]);
    }

    public function update(Request $request, int $eventId): RedirectResponse
    {
        $event = AcademicCalendarEvent::findOrFail($eventId);

        try {
            $data = $this->validated($request);
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->withErrors($exception->errors())
                ->with('edit_event_id', $event->id);
        }

        $event->update($data);

        app(NotificationService::class)->calendarEventUpdated(
            $data['title'],
            $data['school_year'],
            $data['term']
        );

        flash_notice('Calendar event updated successfully!', 'success');

        return redirect()->route('office.academic-calendar', $this->filterQuery());
    }

    public function destroy(int $eventId): RedirectResponse
    {
        $event = AcademicCalendarEvent::findOrFail($eventId);

        app(NotificationService::class)->calendarEventCancelled(
            $event->title,
            $event->school_year,
            $event->term
        );

        $event->delete();

        flash_notice('Calendar event deleted.', 'success');

        return redirect()->route('office.academic-calendar', $this->filterQuery());
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'event_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:150'],
            'category' => ['required', 'string', Rule::in(array_keys(AcademicCalendarEvent::CATEGORIES))],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'full_description' => ['nullable', 'string'],
        ]);

        return [
            'title' => trim($data['title']),
            'event_date' => $data['event_date'],
            'start_time' => $data['start_time'] ?: null,
            'end_time' => $data['end_time'] ?: null,
            'location' => trim($data['location'] ?? '') ?: null,
            'category' => $data['category'],
            'school_year' => trim($data['school_year']),
            'term' => (int) $data['term'],
            'short_description' => trim($data['short_description'] ?? '') ?: null,
            'full_description' => trim($data['full_description'] ?? '') ?: null,
        ];
    }

    private function filterQuery(): array
    {
        return collect(request()->query())
            ->only(['school_year', 'term', 'category', 'pm'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }

    private function previewUrl(CarbonImmutable $date): string
    {
        return route('office.academic-calendar', array_merge($this->filterQuery(), [
            'pm' => $date->month,
        ]));
    }
}
