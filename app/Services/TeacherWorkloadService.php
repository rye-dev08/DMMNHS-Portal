<?php

namespace App\Services;

use App\Http\Controllers\OfficeAdmin\TeacherAssignmentController;
use App\Models\EnrollmentRequest;
use App\Models\GradeSubmissionDeadline;
use App\Models\Requirement;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Aggregation layer for the Teacher Workload Dashboard.
 *
 * This service only reads data that already exists elsewhere in the portal:
 *   - Teacher assignments (teachers.advisory_class, teacher_subjects, subjects)
 *   - Student enrollment (enrollment_requests)
 *   - Grade management (grades + GradeSubmissionMonitorService units)
 *   - Requirement & Submission Tracker (requirements, requirement_submissions)
 *   - Message / notification system (unread notifications)
 *   - Academic Calendar (academic_calendar_events)
 *   - School year / semester (Setting::period())
 *
 * It creates no records of its own; every number below is derived from the
 * existing systems so nothing is duplicated or tracked separately.
 */
class TeacherWorkloadService
{
    public const DEFAULT_ACTIVITY_LIMIT = 10;

    public function forUser(User $user): object
    {
        $teacher = Teacher::where('user_id', $user->id)->first();
        $teacherId = (int) ($teacher->id ?? 0);
        $period = Setting::find(1)?->period();
        $schoolYear = (string) ($period->school_year ?? '');
        $term = (int) ($period->term ?? 1);

        $gradeService = app(GradeSubmissionMonitorService::class);
        $gradeUnits = $teacherId > 0
            ? $gradeService->units(['teacher' => (int) $user->id])
            : collect();

        $assignedStudents = $this->assignedStudents($teacherId);
        $subjectNames = $this->subjectNames($teacherId);

        $requirements = $this->activeRequirements($teacherId, $schoolYear, $term);
        $pendingRequirements = $this->pendingRequirements($requirements);
        $upcomingDeadlines = $this->upcomingDeadlines($teacherId, $requirements, $schoolYear, $term);
        $classSummary = $this->classSummary($teacher, $teacherId, $schoolYear, $term, $subjectNames);

        return (object) [
            'teacher' => $teacher,
            'period' => $period,
            'summary' => $this->summary(
                $teacher,
                $assignedStudents,
                $subjectNames,
                $pendingRequirements,
                $gradeUnits,
                $user,
                $upcomingDeadlines
            ),
            'todayWorkload' => $this->todayWorkload(
                $teacherId,
                $assignedStudents,
                $gradeUnits,
                $requirements,
                $user
            ),
            'upcomingDeadlines' => $upcomingDeadlines,
            'pendingRequirements' => $pendingRequirements,
            'gradeUnits' => $gradeUnits,
            'gradeCompletion' => $gradeService->completion($gradeUnits),
            'classSummary' => $classSummary,
            'recentActivity' => $this->recentActivity($teacherId, $user),
            'quickActions' => $this->quickActions(),
        ];
    }

    /**
     * Summary cards shown at the top of the dashboard.
     */
    private function summary(
        ?Teacher $teacher,
        int $assignedStudents,
        Collection $subjectNames,
        Collection $pendingRequirements,
        Collection $gradeUnits,
        User $user,
        Collection $upcomingDeadlines
    ): object {
        $pendingGrades = $gradeUnits
            ->whereIn('status', [GradeSubmissionMonitorService::STATUS_PENDING, GradeSubmissionMonitorService::STATUS_LATE])
            ->count();

        return (object) [
            'students' => $assignedStudents,
            'advisory_sections' => $teacher?->advisory_class ? 1 : 0,
            'subjects_handled' => $subjectNames->count(),
            'classes_today' => $this->classesToday($teacher),
            'pending_requirements' => $pendingRequirements->count(),
            'pending_grade_submissions' => $pendingGrades,
            'unread_messages' => $user->unreadNotifications()->count(),
            'upcoming_deadlines' => $upcomingDeadlines->count(),
        ];
    }

    /**
     * "Today's Workload" action items derived from existing records.
     *
     * @return Collection<int, object>
     */
    private function todayWorkload(
        int $teacherId,
        int $assignedStudents,
        Collection $gradeUnits,
        Collection $requirements,
        User $user
    ): Collection {
        $items = collect();

        foreach ($gradeUnits as $unit) {
            if ($unit->status !== GradeSubmissionMonitorService::STATUS_SUBMITTED) {
                $remaining = $unit->assigned - $unit->graded;
                $items->push((object) [
                    'type' => 'grades',
                    'title' => $unit->subject_name,
                    'detail' => $remaining > 0 ? "Grade remaining: {$remaining} student(s)" : 'Awaiting grade submission',
                    'url' => route('teacher.grade-submissions'),
                    'urgency' => $unit->status === GradeSubmissionMonitorService::STATUS_LATE ? 'urgent' : 'soon',
                ]);
            }
        }

        $service = app(RequirementService::class);
        foreach ($requirements as $requirement) {
            $progress = $service->progress($requirement);
            $needsReview = $progress->submitted > 0 && $progress->approved < $progress->total;

            if ($needsReview) {
                $items->push((object) [
                    'type' => 'requirement',
                    'title' => $requirement->title,
                    'detail' => "Review {$progress->submitted} submission(s)",
                    'url' => route('teacher.requirements.show', $requirement->id),
                    'urgency' => 'normal',
                ]);
            }

            if ($requirement->due_date) {
                $days = (int) CarbonImmutable::today()->startOfDay()
                    ->diffInDays($requirement->due_date->toImmutable()->startOfDay());

                if ($days === 0) {
                    $items->push((object) [
                        'type' => 'deadline',
                        'title' => $requirement->title,
                        'detail' => 'Due today',
                        'url' => route('teacher.requirements.show', $requirement->id),
                        'urgency' => 'urgent',
                    ]);
                } elseif ($days === 1) {
                    $items->push((object) [
                        'type' => 'deadline',
                        'title' => $requirement->title,
                        'detail' => 'Due tomorrow',
                        'url' => route('teacher.requirements.show', $requirement->id),
                        'urgency' => 'soon',
                    ]);
                }
            }
        }

        $pendingEnrollments = EnrollmentRequest::where('teacher_id', $teacherId)
            ->where('status', 'pending')
            ->count();

        if ($pendingEnrollments > 0) {
            $items->push((object) [
                'type' => 'enrollment',
                'title' => 'Enrollment Requests',
                'detail' => "{$pendingEnrollments} request(s) awaiting approval",
                'url' => route('teacher.enrollment-requests'),
                'urgency' => 'normal',
            ]);
        }

        $unread = $user->unreadNotifications()->count();
        if ($unread > 0) {
            $items->push((object) [
                'type' => 'message',
                'title' => 'Unread Messages',
                'detail' => "{$unread} unread notification(s)",
                'url' => route('notifications.index'),
                'urgency' => 'normal',
            ]);
        }

        return $items
            ->sortBy(fn ($item) => $item->urgency === 'urgent' ? 0 : ($item->urgency === 'soon' ? 1 : 2))
            ->values();
    }

    /**
     * Upcoming teacher deadlines (requirement deadlines + grade submission
     * deadlines), nearest first. Only active, non-past items are included.
     *
     * @return Collection<int, object>
     */
    private function upcomingDeadlines(
        int $teacherId,
        Collection $requirements,
        string $schoolYear,
        int $term
    ): Collection {
        $deadlines = collect();

        foreach ($requirements as $requirement) {
            if ($requirement->due_date === null) {
                continue;
            }

            $deadlines->push((object) [
                'type' => 'requirement',
                'title' => $requirement->title,
                'date' => $requirement->due_date->toImmutable(),
                'url' => route('teacher.requirements.show', $requirement->id),
            ]);
        }

        $gradeDeadlines = GradeSubmissionDeadline::query()
            ->where('school_year', $schoolYear)
            ->where('term', $term)
            ->whereDate('deadline', '>=', CarbonImmutable::today()->toDateString())
            ->get();

        foreach ($gradeDeadlines as $deadline) {
            $label = $deadline->subject_name === GradeSubmissionDeadline::ALL_SUBJECTS
                ? 'Grade Submission'
                : $deadline->subject_name.' Grade Submission';

            $deadlines->push((object) [
                'type' => 'grades',
                'title' => $label,
                'date' => $deadline->deadline->toImmutable(),
                'url' => route('teacher.grade-submissions'),
            ]);
        }

        return $deadlines
            ->sortBy(fn ($item) => $item->date->toDateString())
            ->values();
    }

    /**
     * Active requirements for the current period that still need teacher
     * action, enriched with progress.
     *
     * @return Collection<int, object>
     */
    private function pendingRequirements(Collection $requirements): Collection
    {
        $service = app(RequirementService::class);
        $pending = collect();

        foreach ($requirements as $requirement) {
            $progress = $service->progress($requirement);
            $remaining = $progress->total - $progress->approved;

            if ($progress->total > 0 && $remaining > 0) {
                $pending->push((object) [
                    'id' => (int) $requirement->id,
                    'title' => $requirement->title,
                    'type_label' => $requirement->typeLabel(),
                    'submitted' => $progress->submitted,
                    'total' => $progress->total,
                    'remaining' => $remaining,
                    'percent' => $progress->percent,
                    'due_date' => $requirement->due_date?->toImmutable(),
                    'url' => route('teacher.requirements.show', $requirement->id),
                ]);
            }
        }

        return $pending->sortByDesc('remaining')->values();
    }

    /**
     * Per-subject class summary for the classes the teacher actually teaches.
     *
     * @return Collection<int, object>
     */
    private function classSummary(
        ?Teacher $teacher,
        int $teacherId,
        string $schoolYear,
        int $term,
        Collection $subjectNames
    ): Collection {
        if ($teacherId === 0 || $subjectNames->isEmpty()) {
            return collect();
        }

        $parsed = $teacher?->advisory_class
            ? TeacherAssignmentController::parseAdvisory($teacher->advisory_class)
            : null;
        $section = $parsed
            ? 'Grade '.$parsed['grade'].'-'.$parsed['section']
            : ($teacher?->advisory_class ?? '');

        $subjectIds = DB::table('subjects')
            ->where('teacher_id', $teacherId)
            ->whereIn('subject_name', $subjectNames->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $grades = DB::table('grades')
            ->whereIn('subject_id', $subjectIds)
            ->where('quarter', 'Term '.$term)
            ->get(['subject_id', 'student_id', 'grade']);

        $activeReq = $this->activeRequirements($teacherId, $schoolYear, $term);
        $requirementService = app(RequirementService::class);
        $pendingReqCount = 0;

        foreach ($activeReq as $requirement) {
            $progress = $requirementService->progress($requirement);
            if ($progress->total > 0 && $progress->approved < $progress->total) {
                $pendingReqCount++;
            }
        }

        $nextRequirement = $activeReq
            ->filter(fn ($r) => $r->due_date !== null && ! $r->isOverdue())
            ->sortBy(fn ($r) => $r->due_date->toDateString())
            ->first();

        $rows = collect();

        foreach ($subjectNames as $subjectName) {
            $subjectIdsForName = DB::table('subjects')
                ->where('teacher_id', $teacherId)
                ->where('subject_name', $subjectName)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $students = DB::table('subjects')
                ->where('teacher_id', $teacherId)
                ->where('subject_name', $subjectName)
                ->distinct()
                ->count('student_id');

            $subjectGrades = $grades->whereIn('subject_id', $subjectIdsForName);
            // Only actual numeric grades count toward the average — a stored
            // "N/A" grade must not be treated as a zero.
            $numericGrades = collect($subjectGrades->pluck('grade'))->filter(fn ($g) => is_numeric($g));
            $average = $numericGrades->isNotEmpty()
                ? round($numericGrades->avg(), 1)
                : null;

            $nextActivity = $nextRequirement
                ? (object) [
                    'title' => $nextRequirement->title,
                    'date' => $nextRequirement->due_date->toImmutable(),
                ]
                : null;

            $rows->push((object) [
                'subject_name' => $subjectName,
                'section' => $section,
                'students' => $students,
                'requirements_pending' => $pendingReqCount,
                'average_grade' => $average,
                'next_activity' => $nextActivity,
                'url' => route('teacher.grades-overview'),
            ]);
        }

        return $rows;
    }

    /**
     * Recent activity merged from grades, requirement reviews, enrollments,
     * announcements and notifications, newest first.
     *
     * @return Collection<int, object>
     */
    private function recentActivity(int $teacherId, User $user): Collection
    {
        $activity = collect();

        $gradeRows = DB::table('grades as g')
            ->join('subjects as s', 'g.subject_id', '=', 's.id')
            ->where('s.teacher_id', $teacherId)
            ->whereNotNull('g.date_submitted')
            ->get(['g.subject_id', 's.subject_name', 'g.date_submitted']);

        foreach ($gradeRows as $row) {
            $activity->push((object) [
                'kind' => 'grades',
                'title' => 'Grades submitted',
                'detail' => 'Updated '.$row->subject_name,
                'at' => $row->date_submitted ? Carbon::parse($row->date_submitted) : null,
            ]);
        }

        $reviewed = DB::table('requirement_submissions as rs')
            ->join('requirements as r', 'rs.requirement_id', '=', 'r.id')
            ->where('r.teacher_id', $teacherId)
            ->whereNotNull('rs.reviewed_at')
            ->get(['r.title', 'rs.reviewed_at']);

        foreach ($reviewed as $row) {
            $activity->push((object) [
                'kind' => 'requirement',
                'title' => 'Requirement reviewed',
                'detail' => $row->title,
                'at' => $row->reviewed_at ? Carbon::parse($row->reviewed_at) : null,
            ]);
        }

        $enrollments = DB::table('enrollment_requests as er')
            ->join('students as st', 'er.student_id', '=', 'st.id')
            ->join('users as u', 'st.user_id', '=', 'u.id')
            ->where('er.teacher_id', $teacherId)
            ->where('er.status', 'approved')
            ->get(['u.name', 'er.date_requested']);

        foreach ($enrollments as $row) {
            $activity->push((object) [
                'kind' => 'enrollment',
                'title' => 'Student enrolled',
                'detail' => $row->name,
                'at' => $row->date_requested ? Carbon::parse($row->date_requested) : null,
            ]);
        }

        $announcements = app(AnnouncementService::class)->feed($user)['items'];
        foreach ($announcements as $announcement) {
            $activity->push((object) [
                'kind' => 'announcement',
                'title' => 'New announcement',
                'detail' => $announcement->title,
                'at' => $announcement->publish_date ? $announcement->publish_date->copy()->endOfDay() : null,
            ]);
        }

        $notifications = $user->notifications()->limit(20)->get();
        foreach ($notifications as $notification) {
            $activity->push((object) [
                'kind' => 'message',
                'title' => $notification->data['title'] ?? 'Notification',
                'detail' => $notification->data['message'] ?? '',
                'at' => $notification->created_at,
            ]);
        }

        return $activity
            ->filter(fn ($item) => $item->at !== null)
            ->sortByDesc(fn ($item) => $item->at->timestamp)
            ->take(self::DEFAULT_ACTIVITY_LIMIT)
            ->values();
    }

    /**
     * Quick action buttons navigating to existing pages.
     *
     * @return Collection<int, array{label: string, url: string, icon: string}>
     */
    private function quickActions(): Collection
    {
        return collect([
            ['label' => 'Submit Grades', 'url' => route('teacher.submit-grades'), 'icon' => 'tick'],
            ['label' => 'Review Requirements', 'url' => route('teacher.requirements'), 'icon' => 'requirement'],
            ['label' => 'Open Messages', 'url' => route('notifications.index'), 'icon' => 'bell'],
            ['label' => 'View Schedule', 'url' => route('teacher.advisory-portal'), 'icon' => 'book'],
            ['label' => 'Academic Calendar', 'url' => route('teacher.calendar'), 'icon' => 'calendar'],
        ]);
    }

    private function assignedStudents(int $teacherId): int
    {
        if ($teacherId === 0) {
            return 0;
        }

        return DB::table('enrollment_requests')
            ->where('teacher_id', $teacherId)
            ->where('status', 'approved')
            ->distinct()
            ->count('student_id');
    }

    private function subjectNames(int $teacherId): Collection
    {
        if ($teacherId === 0) {
            return collect();
        }

        return DB::table('subjects')
            ->where('teacher_id', $teacherId)
            ->distinct()
            ->orderBy('subject_name')
            ->pluck('subject_name')
            ->values();
    }

    private function classesToday(?Teacher $teacher): int
    {
        if ($teacher === null) {
            return 0;
        }

        return TeacherSubject::where('teacher_id', (int) $teacher->id)->count();
    }

    /**
     * Active requirements for the current period belonging to this teacher.
     *
     * @return Collection<int, Requirement>
     */
    private function activeRequirements(int $teacherId, string $schoolYear, int $term): Collection
    {
        if ($teacherId === 0) {
            return collect();
        }

        return Requirement::query()
            ->where('teacher_id', $teacherId)
            ->where('school_year', $schoolYear)
            ->where('term', $term)
            ->where('status', Requirement::STATUS_ACTIVE)
            ->get();
    }
}
