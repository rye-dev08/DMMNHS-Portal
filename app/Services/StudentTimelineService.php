<?php

namespace App\Services;

use App\Models\AcademicCalendarEvent;
use App\Models\AssessmentScore;
use App\Models\EnrollmentRequest;
use App\Models\Grade;
use App\Models\Requirement;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Aggregation layer for the "Student Timeline (My Academic Journey)" feature.
 *
 * This service only reads data that already exists elsewhere in the portal:
 *   - Enrollment (enrollment_requests)
 *   - Grade management (grades + subjects)
 *   - Requirement & Submission Tracker (requirements, requirement_submissions)
 *   - Academic Calendar (academic_calendar_events)
 *   - Announcements (announcements feed)
 *   - Assessment scores (activity / quiz / exam)
 *   - School year / semester (Setting::period())
 *   - Notification system (used as a timestamp source for milestones that have
 *     no dedicated datetime column, e.g. enrollment approval, term completion)
 *
 * It intentionally creates no records of its own. The timeline is a permanent
 * read-only view of the student's academic history; nothing is duplicated.
 */
class StudentTimelineService
{
    public const CATEGORY_ACADEMIC = 'Academic';

    public const CATEGORY_REQUIREMENTS = 'Requirements';

    public const CATEGORY_GRADES = 'Grades';

    public const CATEGORY_ENROLLMENT = 'Enrollment';

    public const CATEGORY_DOCUMENTS = 'Documents';

    public const CATEGORY_ACTIVITIES = 'Activities';

    public const CATEGORIES = [
        self::CATEGORY_ACADEMIC,
        self::CATEGORY_REQUIREMENTS,
        self::CATEGORY_GRADES,
        self::CATEGORY_ENROLLMENT,
        self::CATEGORY_DOCUMENTS,
        self::CATEGORY_ACTIVITIES,
    ];

    /**
     * All timeline events for the student, newest first.
     *
     * @return Collection<int, object>
     */
    public function forUser(User $user): Collection
    {
        $student = Student::where('user_id', $user->id)->first();

        if ($user->role !== 'student' || $student === null) {
            return collect();
        }

        $studentId = (int) $student->id;
        $period = Setting::find(1)?->period();
        $schoolYear = (string) ($period->school_year ?? '');
        $term = (int) ($period->term ?? 1);

        $events = collect()
            ->merge($this->accountEvents($user))
            ->merge($this->enrollmentEvents($studentId, $user, $schoolYear))
            ->merge($this->subjectEvents($studentId, $schoolYear))
            ->merge($this->requirementEvents($studentId))
            ->merge($this->gradeEvents($studentId, $schoolYear))
            ->merge($this->assessmentEvents($studentId, $schoolYear))
            ->merge($this->milestoneEvents($user, $schoolYear, $term))
            ->merge($this->calendarEvents($schoolYear, $term))
            ->merge($this->announcementEvents($user));

        return $events
            ->filter(fn ($event) => $event->at !== null)
            ->sortByDesc(fn ($event) => $event->at->timestamp)
            ->values();
    }

    /**
     * The latest N events for the dashboard widget.
     *
     * @return Collection<int, object>
     */
    public function recent(User $user, int $limit = 5): Collection
    {
        return $this->forUser($user)->take($limit)->values();
    }

    /**
     * Distinct school years and terms present in the timeline, plus the
     * fixed event categories, used to build the filter UI.
     */
    public function filterOptions(Collection $events): object
    {
        $schoolYears = $events
            ->pluck('school_year')
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->sortDesc()
            ->values();

        $terms = $events
            ->pluck('term')
            ->filter(fn ($value) => $value !== null)
            ->unique()
            ->sort()
            ->values();

        return (object) [
            'schoolYears' => $schoolYears,
            'terms' => $terms,
            'categories' => self::CATEGORIES,
        ];
    }

    /**
     * Apply the optional school year / term / category / date range filters
     * and a free-text search over the timeline.
     *
     * @return Collection<int, object>
     */
    public function applyFilters(Collection $events, array $filters, ?string $search = null): Collection
    {
        $schoolYear = (string) ($filters['school_year'] ?? '');
        $term = (int) ($filters['term'] ?? 0);
        $category = (string) ($filters['category'] ?? '');
        $from = (string) ($filters['from'] ?? '');
        $to = (string) ($filters['to'] ?? '');
        $query = trim((string) $search);

        $filtered = $events->filter(function ($event) use ($schoolYear, $term, $category, $from, $to) {
            if ($schoolYear !== '' && $event->school_year !== $schoolYear) {
                return false;
            }

            if ($term > 0 && (int) $event->term !== $term) {
                return false;
            }

            if ($category !== '' && $event->category !== $category) {
                return false;
            }

            if ($from !== '') {
                $fromDate = CarbonImmutable::parse($from)->startOfDay();
                if ($event->at->lt($fromDate)) {
                    return false;
                }
            }

            if ($to !== '') {
                $toDate = CarbonImmutable::parse($to)->endOfDay();
                if ($event->at->gt($toDate)) {
                    return false;
                }
            }

            return true;
        });

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $filtered = $filtered->filter(function ($event) use ($needle) {
                return str_contains(mb_strtolower($event->title), $needle)
                    || str_contains(mb_strtolower($event->detail), $needle);
            });
        }

        return $filtered->values();
    }

    /**
     * Human friendly relative label used by the dashboard widget.
     */
    public static function relativeLabel(CarbonImmutable $at): string
    {
        $today = CarbonImmutable::today()->startOfDay();

        if ($at->isToday()) {
            return 'Today';
        }

        if ($at->isYesterday()) {
            return 'Yesterday';
        }

        $days = (int) $at->copy()->startOfDay()->diffInDays($today);

        if ($days <= 7) {
            return "{$days} days ago";
        }

        return $at->format('M d, Y');
    }

    private function accountEvents(User $user): Collection
    {
        if ($user->created_at === null) {
            return collect();
        }

        $period = Setting::find(1)?->period();

        return collect([
            $this->makeEvent(
                'account_activated',
                self::CATEGORY_ACADEMIC,
                'Student Account Activated',
                'Your student account was created. Welcome to the DMMNHS Student Portal.',
                $user->created_at->toImmutable(),
                (string) ($period->school_year ?? ''),
                null,
                route('student.dashboard'),
                'Open Dashboard',
                'person'
            ),
        ]);
    }

    private function enrollmentEvents(int $studentId, User $user, string $schoolYear): Collection
    {
        $requests = EnrollmentRequest::with('teacher.user')
            ->where('student_id', $studentId)
            ->get();

        if ($requests->isEmpty()) {
            return collect();
        }

        $notifications = $this->notificationTitles($user);

        $events = collect();

        foreach ($requests as $request) {
            $teacherName = $request->teacher?->user?->name ?? 'your adviser';

            $events->push($this->makeEvent(
                'enrollment_submitted',
                self::CATEGORY_ENROLLMENT,
                'Enrollment Submitted',
                "Your enrollment request to {$teacherName} was submitted.",
                $request->date_requested?->toImmutable(),
                $schoolYear,
                null,
                route('student.enrollment'),
                'View Enrollment',
                'clipboard'
            ));

            $status = (string) $request->status;

            if ($status === 'approved') {
                $events->push($this->makeEvent(
                    'enrollment_approved',
                    self::CATEGORY_ENROLLMENT,
                    'Enrollment Approved',
                    "Your enrollment request to {$teacherName} has been approved.",
                    $this->notificationAt($notifications, 'Enrollment Approved', $request->date_requested),
                    $schoolYear,
                    null,
                    route('student.enrollment'),
                    'View Enrollment',
                    'clipboard',
                    'Approved'
                ));
            } elseif ($status === 'rejected') {
                $events->push($this->makeEvent(
                    'enrollment_rejected',
                    self::CATEGORY_ENROLLMENT,
                    'Enrollment Rejected',
                    "Your enrollment request to {$teacherName} was not approved.",
                    $this->notificationAt($notifications, 'Enrollment Rejected', $request->date_requested),
                    $schoolYear,
                    null,
                    route('student.enrollment'),
                    'View Enrollment',
                    'clipboard',
                    'Rejected'
                ));
            }
        }

        return $events;
    }

    /**
     * A "Teacher Assigned" event per subject added to the student's schedule.
     */
    private function subjectEvents(int $studentId, string $schoolYear): Collection
    {
        $subjects = Subject::with('teacher.user')
            ->where('student_id', $studentId)
            ->get();

        if ($subjects->isEmpty()) {
            return collect();
        }

        $events = collect();

        foreach ($subjects as $subject) {
            $teacherName = $subject->teacher?->user?->name ?? 'Your teacher';

            $events->push($this->makeEvent(
                'teacher_assigned',
                self::CATEGORY_ACADEMIC,
                'Teacher Assigned',
                "{$subject->subject_name} was added to your schedule under {$teacherName}.",
                $subject->created_at?->toImmutable(),
                $schoolYear,
                null,
                route('student.schedule'),
                'View Schedule',
                'book'
            ));
        }

        return $events;
    }

    private function requirementEvents(int $studentId): Collection
    {
        $enrolledTeacherIds = DB::table('enrollment_requests')
            ->where('student_id', $studentId)
            ->where('status', 'approved')
            ->pluck('teacher_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $requirements = Requirement::with('submissions')
            ->where(fn ($query) => $query
                ->whereIn('teacher_id', $enrolledTeacherIds)
                ->orWhereHas('submissions', fn ($q) => $q->where('student_id', $studentId)))
            ->get();

        if ($requirements->isEmpty()) {
            return collect();
        }

        $events = collect();

        foreach ($requirements as $requirement) {
            $isActivity = in_array((string) $requirement->requirement_type, [
                Requirement::TYPE_ACTIVITY,
                Requirement::TYPE_PROJECT,
            ], true);

            $events->push($this->makeEvent(
                'requirement_assigned',
                $isActivity ? self::CATEGORY_ACTIVITIES : self::CATEGORY_REQUIREMENTS,
                $isActivity ? 'Activity Assigned' : 'Requirement Assigned',
                "{$requirement->title} was assigned to you by your teacher.",
                $requirement->created_at?->toImmutable(),
                (string) $requirement->school_year,
                (int) $requirement->term,
                route('student.requirements.show', $requirement->id),
                'View Requirement',
                'requirement'
            ));

            $submission = $requirement->submissions->firstWhere('student_id', $studentId);

            if ($submission === null) {
                continue;
            }

            $events->push($this->makeEvent(
                'requirement_submitted',
                $isActivity ? self::CATEGORY_ACTIVITIES : self::CATEGORY_REQUIREMENTS,
                $isActivity ? 'Activity Submitted' : 'Requirement Submitted',
                "Your submission for \"{$requirement->title}\" was received.",
                $submission->submitted_at?->toImmutable(),
                (string) $requirement->school_year,
                (int) $requirement->term,
                route('student.requirements.show', $requirement->id),
                'View Requirement',
                'requirement'
            ));

            if ($submission->reviewed_at !== null && $submission->isApproved()) {
                $events->push($this->makeEvent(
                    'requirement_approved',
                    $isActivity ? self::CATEGORY_ACTIVITIES : self::CATEGORY_REQUIREMENTS,
                    $isActivity ? 'Activity Approved' : 'Requirement Approved',
                    "Your submission for \"{$requirement->title}\" has been approved.",
                    $submission->reviewed_at->toImmutable(),
                    (string) $requirement->school_year,
                    (int) $requirement->term,
                    route('student.requirements.show', $requirement->id),
                    'View Requirement',
                    'requirement',
                    'Approved'
                ));
            }

            if ($submission->reviewed_at !== null && $submission->isNeedsRevision()) {
                $events->push($this->makeEvent(
                    'requirement_revision',
                    $isActivity ? self::CATEGORY_ACTIVITIES : self::CATEGORY_REQUIREMENTS,
                    $isActivity ? 'Activity Returned for Revision' : 'Requirement Returned for Revision',
                    "Your submission for \"{$requirement->title}\" was returned for revision.",
                    $submission->reviewed_at->toImmutable(),
                    (string) $requirement->school_year,
                    (int) $requirement->term,
                    route('student.requirements.show', $requirement->id),
                    'View Requirement',
                    'requirement',
                    'Needs Revision'
                ));
            }
        }

        return $events;
    }

    private function gradeEvents(int $studentId, string $schoolYear): Collection
    {
        $rows = DB::table('grades as g')
            ->join('subjects as s', 'g.subject_id', '=', 's.id')
            ->where('g.student_id', $studentId)
            ->whereNotNull('g.date_submitted')
            ->get(['g.id', 'g.grade', 'g.quarter', 'g.date_submitted', 's.subject_name']);

        if ($rows->isEmpty()) {
            return collect();
        }

        $events = collect();

        foreach ($rows as $row) {
            $events->push($this->makeEvent(
                'grade_released',
                self::CATEGORY_GRADES,
                'Grade Released',
                "Your grade in {$row->subject_name} has been released: {$row->grade}.",
                CarbonImmutable::parse($row->date_submitted),
                $schoolYear,
                $this->termFromQuarter((string) $row->quarter),
                route('student.grades'),
                'View Grades',
                'chart'
            ));
        }

        return $events;
    }

    private function assessmentEvents(int $studentId, string $schoolYear): Collection
    {
        $scores = AssessmentScore::where('student_id', $studentId)->get();

        if ($scores->isEmpty()) {
            return collect();
        }

        $labels = [
            'activity' => 'Activity Recorded',
            'quiz' => 'Quiz Completed',
            'exam' => 'Exam Recorded',
        ];

        $events = collect();

        foreach ($scores as $score) {
            $label = $labels[$score->score_type] ?? 'Assessment Recorded';

            $events->push($this->makeEvent(
                'assessment_'.$score->score_type,
                self::CATEGORY_ACTIVITIES,
                $label,
                "{$label}: {$score->score}/{$score->max_score}.",
                $score->created_at?->toImmutable(),
                $schoolYear,
                null,
                route('student.grades'),
                'View Grades',
                'book'
            ));
        }

        return $events;
    }

    /**
     * Milestones whose only timestamp is a portal notification: semester
     * completion ("All Grades Complete"), new term, and new school year.
     */
    private function milestoneEvents(User $user, string $schoolYear, int $term): Collection
    {
        $notifications = $user->notifications()->get();
        $events = collect();

        foreach ($notifications as $notification) {
            $title = (string) ($notification->data['title'] ?? '');
            $message = (string) ($notification->data['message'] ?? '');

            if ($title === 'All Grades Complete') {
                $events->push($this->makeEvent(
                    'semester_completed',
                    self::CATEGORY_GRADES,
                    'Semester Completed',
                    $message !== '' ? $message : 'All your grades have been submitted.',
                    $notification->created_at->toImmutable(),
                    $this->yearFromMessage($message) ?? $schoolYear,
                    $this->termFromMessage($message) ?? $term,
                    route('student.grades'),
                    'View Grades',
                    'tick'
                ));
            }

            if ($title === 'New Term Started') {
                $events->push($this->makeEvent(
                    'term_started',
                    self::CATEGORY_ACADEMIC,
                    'New Term Started',
                    $message !== '' ? $message : 'A new term has begun.',
                    $notification->created_at->toImmutable(),
                    $schoolYear,
                    $this->termFromMessage($message) ?? $term,
                    route('student.dashboard'),
                    'Open Dashboard',
                    'calendar'
                ));
            }

            if ($title === 'New School Year') {
                $events->push($this->makeEvent(
                    'school_year_started',
                    self::CATEGORY_ACADEMIC,
                    'School Year Started',
                    $message !== '' ? $message : 'A new school year has started.',
                    $notification->created_at->toImmutable(),
                    $this->yearFromMessage($message) ?? $schoolYear,
                    null,
                    route('student.enrollment'),
                    'File Enrollment',
                    'calendar'
                ));
            }
        }

        return $events;
    }

    private function calendarEvents(string $schoolYear, int $term): Collection
    {
        $events = AcademicCalendarEvent::query()
            ->where('school_year', $schoolYear)
            ->where('term', $term)
            ->whereDate('event_date', '<=', CarbonImmutable::today()->toDateString())
            ->get();

        if ($events->isEmpty()) {
            return collect();
        }

        return $events->map(function (AcademicCalendarEvent $event) use ($schoolYear, $term) {
            $categoryLabel = AcademicCalendarEvent::CATEGORIES[$event->category] ?? $event->category;

            return $this->makeEvent(
                'academic_event',
                self::CATEGORY_ACADEMIC,
                $event->title,
                "{$categoryLabel} — school event from the academic calendar.",
                $event->event_date->toImmutable()->startOfDay(),
                $schoolYear,
                $term,
                route('student.calendar'),
                'View Calendar',
                'calendar'
            );
        });
    }

    private function announcementEvents(User $user): Collection
    {
        $feed = app(AnnouncementService::class)->feed($user)['items'];

        if (empty($feed)) {
            return collect();
        }

        return collect($feed)->map(function ($announcement) {
            $publishDate = $announcement->publish_date;

            if ($publishDate === null) {
                return null;
            }

            return $this->makeEvent(
                'announcement',
                self::CATEGORY_ACADEMIC,
                'New Announcement',
                $announcement->short_summary ?? $announcement->title,
                $publishDate->toImmutable()->endOfDay(),
                (string) $announcement->school_year,
                (int) $announcement->term,
                route('announcements'),
                'View Announcements',
                'bell'
            );
        })->filter();
    }

    private function makeEvent(
        string $type,
        string $category,
        string $title,
        string $detail,
        ?CarbonImmutable $at,
        string $schoolYear,
        ?int $term,
        ?string $url,
        ?string $actionText,
        string $icon,
        ?string $badge = null
    ): object {
        return (object) [
            'id' => $type.'-'.md5($title.'|'.$detail.'|'.($at?->toDateTimeString() ?? '')),
            'type' => $type,
            'category' => $category,
            'title' => $title,
            'detail' => $detail,
            'at' => $at,
            'school_year' => $schoolYear,
            'term' => $term,
            'url' => $url,
            'action_text' => $actionText,
            'icon' => $icon,
            'badge' => $badge,
        ];
    }

    /**
     * Timestamp of the latest notification with the given title, or a fallback.
     */
    private function notificationAt(Collection $notifications, string $title, $fallback): ?CarbonImmutable
    {
        $match = $notifications->first(fn ($notification) => ($notification->data['title'] ?? null) === $title);

        if ($match) {
            return $match->created_at->toImmutable();
        }

        return $fallback?->toImmutable();
    }

    private function notificationTitles(User $user): Collection
    {
        return $user->notifications()->get();
    }

    private function termFromQuarter(string $quarter): ?int
    {
        if (preg_match('/Term\s+(\d+)/', $quarter, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function termFromMessage(string $message): ?int
    {
        if (preg_match('/Term\s+(\d+)/i', $message, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function yearFromMessage(string $message): ?string
    {
        if (preg_match('/\(\s*(\d{4}-\d{4})\s*\)/', $message, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
