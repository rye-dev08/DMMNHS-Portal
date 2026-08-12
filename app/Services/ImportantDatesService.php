<?php

namespace App\Services;

use App\Models\AcademicCalendarEvent;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Aggregation layer for the "Important Dates" dashboard widget.
 *
 * This service only reads data that already exists elsewhere in the portal:
 *   - Academic Calendar events (current school year + term, not yet past)
 *   - Requirement & Submission Tracker deadlines (role-scoped)
 *
 * It intentionally creates no records of its own. Enrollment / semester dates
 * surface through the Academic Calendar (e.g. Deadline / Academic / Event
 * categories) so nothing is duplicated.
 */
class ImportantDatesService
{
    public const TYPE_ACADEMIC_EVENT = 'academic_event';

    public const TYPE_REQUIREMENT = 'requirement';

    public const URGENT_DAYS = 1;

    public const SOON_DAYS = 3;

    /**
     * All upcoming dates relevant to the given user, nearest first.
     */
    public function forUser(User $user): Collection
    {
        $items = match ($user->role) {
            'student' => $this->studentItems($user),
            'teacher' => $this->teacherItems($user),
            'office_admin' => $this->officeItems(),
            default => collect(),
        };

        return $items->sortBy(fn ($item) => $item->date->toDateString())->values();
    }

    private function period(): ?object
    {
        $settings = Setting::find(1);

        return $settings?->period();
    }

    private function studentItems(User $user): Collection
    {
        $period = $this->period();
        $student = Student::where('user_id', $user->id)->first();

        if ($period === null || $student === null) {
            return collect();
        }

        $studentId = (int) $student->id;
        $teacherIds = DB::table('enrollment_requests')
            ->where('student_id', $studentId)
            ->where('status', 'approved')
            ->pluck('teacher_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $events = $this->calendarEvents($period, $user);

        $requirements = Requirement::query()
            ->where('school_year', (string) $period->school_year)
            ->where('term', (int) $period->term)
            ->where('status', Requirement::STATUS_ACTIVE)
            ->whereNotNull('due_date')
            ->whereIn('teacher_id', $teacherIds)
            ->whereDate('due_date', '>=', CarbonImmutable::today()->toDateString())
            ->get();

        $submissions = RequirementSubmission::query()
            ->where('student_id', $studentId)
            ->whereIn('requirement_id', $requirements->pluck('id'))
            ->get()
            ->keyBy('requirement_id');

        $requirements = $requirements->filter(function (Requirement $requirement) use ($submissions) {
            $submission = $submissions->get($requirement->id);

            // No submission yet (pending) or asked to revise => still actionable.
            return $submission === null
                || $submission->status === RequirementSubmission::STATUS_NEEDS_REVISION;
        });

        $requirementItems = $requirements->map(function (Requirement $requirement) {
            return $this->requirementItem(
                $requirement,
                route('student.requirements.show', $requirement->id)
            );
        });

        return $events->merge($requirementItems);
    }

    private function teacherItems(User $user): Collection
    {
        $period = $this->period();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($period === null || $teacher === null) {
            return collect();
        }

        $teacherId = (int) $teacher->id;
        $events = $this->calendarEvents($period, $user);

        $requirements = Requirement::query()
            ->where('teacher_id', $teacherId)
            ->where('school_year', (string) $period->school_year)
            ->where('term', (int) $period->term)
            ->where('status', Requirement::STATUS_ACTIVE)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', CarbonImmutable::today()->toDateString())
            ->get();

        $requirementItems = $requirements->map(function (Requirement $requirement) {
            return $this->requirementItem(
                $requirement,
                route('teacher.requirements.show', $requirement->id)
            );
        });

        return $events->merge($requirementItems);
    }

    private function officeItems(): Collection
    {
        $period = $this->period();

        if ($period === null) {
            return collect();
        }

        $events = $this->calendarEvents($period, null);

        $requirements = Requirement::query()
            ->where('school_year', (string) $period->school_year)
            ->where('term', (int) $period->term)
            ->where('status', Requirement::STATUS_ACTIVE)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', CarbonImmutable::today()->toDateString())
            ->get();

        $requirementItems = $requirements->map(function (Requirement $requirement) {
            return $this->requirementItem(
                $requirement,
                route('office.requirements.show', $requirement->id)
            );
        });

        return $events->merge($requirementItems);
    }

    private function calendarEvents(object $period, ?User $user): Collection
    {
        $events = AcademicCalendarEvent::query()
            ->where('school_year', (string) $period->school_year)
            ->where('term', (int) $period->term)
            ->whereDate('event_date', '>=', CarbonImmutable::today()->toDateString())
            ->orderBy('event_date')
            ->get();

        return collect($events->map(function (AcademicCalendarEvent $event) use ($user) {
            $date = $event->event_date->toImmutable()->startOfDay();

            return $this->item(
                self::TYPE_ACADEMIC_EVENT,
                $event->title,
                AcademicCalendarEvent::CATEGORIES[$event->category] ?? $event->category,
                $date,
                $this->calendarUrl($user, $date),
                $event->short_description ?? $event->full_description
            );
        }));
    }

    private function requirementItem(Requirement $requirement, string $url): object
    {
        return $this->item(
            self::TYPE_REQUIREMENT,
            $requirement->title,
            'Requirement Deadline',
            $requirement->due_date->toImmutable()->startOfDay(),
            $url,
            $requirement->description
        );
    }

    private function item(string $type, string $title, string $subtitle, CarbonImmutable $date, string $url, ?string $detail): object
    {
        $days = (int) CarbonImmutable::today()->startOfDay()->diffInDays($date->startOfDay());

        $relative = match (true) {
            $days <= 0 => 'Today',
            $days === 1 => 'Tomorrow',
            $days <= 7 => "In {$days} days",
            default => $date->format('M d, Y'),
        };

        $urgency = match (true) {
            $days <= self::URGENT_DAYS => 'urgent',
            $days <= self::SOON_DAYS => 'soon',
            default => 'normal',
        };

        return (object) [
            'type' => $type,
            'title' => $title,
            'subtitle' => $subtitle,
            'date' => $date,
            'relative' => $relative,
            'urgency' => $urgency,
            'url' => $url,
            'detail' => $detail,
        ];
    }

    private function calendarUrl(?User $user, CarbonImmutable $date): string
    {
        return match ($user?->role) {
            'teacher' => route('teacher.calendar', ['year' => $date->year, 'month' => $date->month]),
            'office_admin' => route('office.academic-calendar'),
            default => route('student.calendar', ['year' => $date->year, 'month' => $date->month]),
        };
    }
}
