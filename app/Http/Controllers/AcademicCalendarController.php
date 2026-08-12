<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendarEvent;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AcademicCalendarController extends Controller
{
    /**
     * Shared monthly calendar used by both the Student and Teacher portals.
     * Navigation is bounded to the current school year plus any future school
     * years that already have events placed on them.
     */
    public function index(Request $request): View
    {
        $settings = Setting::find(1);
        $currentYear = (string) ($settings->current_school_year ?? date('Y').'-'.(date('Y') + 1));

        // Allowed school years: the current one plus any future years with events.
        $allowedYears = AcademicCalendarEvent::query()
            ->distinct()
            ->pluck('school_year')
            ->map(fn ($year) => (string) $year)
            ->filter(fn ($year) => $year !== '' && $year >= $currentYear)
            ->merge([$currentYear])
            ->unique()
            ->sort()
            ->values();

        $earliest = self::schoolYearStart($currentYear);
        $latest = self::schoolYearEnd((string) $allowedYears->last());

        $requested = CarbonImmutable::createFromDate(
            (int) $request->integer('year', now()->year),
            max(1, min(12, (int) $request->integer('month', now()->month))),
            1
        );

        $display = $requested->lt($earliest) ? $earliest : $requested;
        $display = $display->gt($latest) ? $latest : $display;

        $schoolYear = self::schoolYearOf($display->year, $display->month);
        $monthStart = $display->startOfMonth();
        $monthEnd = $display->endOfMonth();

        $events = AcademicCalendarEvent::query()
            ->where('school_year', $schoolYear)
            ->whereBetween('event_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        $dayEvents = $events->groupBy(fn ($event) => $event->event_date->format('Y-m-d'));

        $todayKey = CarbonImmutable::today()->format('Y-m-d');

        $prev = $display->subMonthNoOverflow();
        $next = $display->addMonthNoOverflow();

        return view('calendar.show', [
            'settings' => $settings,
            'allowedYears' => $allowedYears,
            'schoolYear' => $schoolYear,
            'monthLabel' => $display->format('F Y'),
            'grid' => self::buildGrid($display, $todayKey),
            'dayEvents' => $dayEvents,
            'eventsJson' => $this->eventsJson($dayEvents),
            'todayKey' => $todayKey,
            'prevUrl' => $prev->gte($earliest) ? $this->monthUrl($prev) : null,
            'nextUrl' => $next->lte($latest) ? $this->monthUrl($next) : null,
        ]);
    }

    /**
     * First month (June) of the given school year.
     */
    public static function schoolYearStart(string $schoolYear): CarbonImmutable
    {
        [$startYear] = explode('-', $schoolYear);

        return CarbonImmutable::createFromDate((int) $startYear, 6, 1);
    }

    /**
     * Last month (May) of the given school year.
     */
    public static function schoolYearEnd(string $schoolYear): CarbonImmutable
    {
        [, $endYear] = explode('-', $schoolYear);

        return CarbonImmutable::createFromDate((int) $endYear, 5, 31);
    }

    /**
     * The school year that owns the given calendar month.
     * Philippine school years run from June to May.
     */
    public static function schoolYearOf(int $year, int $month): string
    {
        if ($month >= 6) {
            return $year.'-'.($year + 1);
        }

        return ($year - 1).'-'.$year;
    }

    /**
     * Compute the following school year label (e.g. "2025-2026" -> "2026-2027").
     */
    public static function nextSchoolYear(string $schoolYear): string
    {
        [$startYear] = explode('-', $schoolYear);
        $start = (int) $startYear;

        return ($start + 1).'-'.($start + 2);
    }

    /**
     * Build a grid of calendar cells for the given month.
     * Null entries are leading blanks before the 1st of the month.
     */
    public static function buildGrid(CarbonImmutable $month, string $todayKey): array
    {
        $start = $month->startOfMonth();
        $firstDayOfWeek = (int) $start->dayOfWeek; // 0 = Sunday
        $daysInMonth = $start->daysInMonth;

        $cells = [];
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $cells[] = null;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $start->addDays($day - 1);
            $cells[] = [
                'day' => $day,
                'key' => $date->format('Y-m-d'),
                'isToday' => $date->format('Y-m-d') === $todayKey,
            ];
        }

        return $cells;
    }

    public static function eventsJson($dayEvents): string
    {
        return $dayEvents->map(function ($events) {
            return $events->map(fn ($event) => [
                'title' => $event->title,
                'category' => $event->category,
                'badge' => academic_calendar_category_style((string) $event->category, 'badge'),
                'dot' => academic_calendar_category_style((string) $event->category, 'dot'),
                'start' => $event->start_time ? Carbon::parse($event->start_time)->format('g:i A') : null,
                'end' => $event->end_time ? Carbon::parse($event->end_time)->format('g:i A') : null,
                'location' => $event->location,
                'short' => $event->short_description,
                'full' => $event->full_description,
                'school_year' => $event->school_year,
                'term' => (int) $event->term,
            ]);
        })->toJson();
    }

    private function monthUrl(CarbonImmutable $date): string
    {
        $route = Route::has('teacher.calendar') && auth()->user()->role === 'teacher'
            ? 'teacher.calendar'
            : 'student.calendar';

        return route($route, ['year' => $date->year, 'month' => $date->month]);
    }
}
