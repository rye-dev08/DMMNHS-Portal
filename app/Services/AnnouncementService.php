<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Centralised targeting/read logic for announcements so the admin
 * management UI and the student/teacher feeds share one source of truth.
 */
class AnnouncementService
{
    /**
     * Return the visible, targeted announcements for a user, enriched with
     * `is_read` and `target_label`. Only published, non-expired announcements
     * of the current school year + term are considered.
     */
    public function feed(User $user): array
    {
        $settings = Setting::find(1);
        $year = (string) ($settings->current_school_year ?? '');
        $term = (int) ($settings->current_term ?? 1);

        $announcements = Announcement::with('audiences')
            ->where('status', Announcement::STATUS_PUBLISHED)
            ->where('school_year', $year)
            ->where('term', $term)
            ->whereDate('publish_date', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', now()->toDateString());
            })
            ->orderByDesc('publish_date')
            ->orderByDesc('id')
            ->get();

        $readIds = DB::table('announcement_reads')
            ->where('user_id', $user->id)
            ->pluck('announcement_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $context = $this->userContext($user);

        $items = $announcements
            ->filter(fn (Announcement $announcement) => $this->matches($announcement, $context))
            ->values()
            ->map(function (Announcement $announcement) use ($readIds) {
                $announcement->is_read = in_array((int) $announcement->id, $readIds, true);
                $announcement->target_label = $this->audienceLabel($announcement);

                return $announcement;
            });

        return [
            'items' => $items,
            'unreadCount' => $items->where('is_read', false)->count(),
        ];
    }

    /**
     * Number of visible announcements the user has not read yet.
     */
    public function unreadCount(User $user): int
    {
        return $this->feed($user)['unreadCount'];
    }

    /**
     * Active students/teachers who should be notified about an announcement,
     * narrowed by its target role and audience refinements. Reuses the same
     * matching rules as the announcement feed so notifications only reach
     * users who will actually see the announcement.
     */
    public function recipientUsers(Announcement $announcement): Collection
    {
        $roles = match ($announcement->target_role) {
            'students' => ['student'],
            'teachers' => ['teacher'],
            'all' => ['student', 'teacher'],
            default => [],
        };

        if ($roles === []) {
            return collect();
        }

        $users = User::whereIn('role', $roles)
            ->where('status', 'active')
            ->with('student', 'teacher')
            ->get();

        return $users
            ->filter(fn (User $user) => $this->matches($announcement, $this->userContext($user)))
            ->values();
    }

    public function markRead(Announcement $announcement, User $user): void
    {
        DB::table('announcement_reads')->updateOrInsert(
            [
                'announcement_id' => $announcement->id,
                'user_id' => $user->id,
            ],
            ['read_at' => now()]
        );
    }

    /**
     * Whether the announcement is currently visible in the user's feed
     * (published, current year + term, publish date reached, not expired,
     * and targeted at the user). Used to reject read-marks on announcements
     * the user cannot actually see.
     */
    public function isVisibleFor(Announcement $announcement, User $user): bool
    {
        if (! $announcement->isPublished()) {
            return false;
        }

        $settings = Setting::find(1);
        $currentYear = (string) ($settings->current_school_year ?? '');
        $currentTerm = (int) ($settings->current_term ?? 1);

        if ((string) $announcement->school_year !== $currentYear
            || (int) $announcement->term !== $currentTerm) {
            return false;
        }

        $today = now()->toDateString();

        if ($announcement->publish_date === null
            || $announcement->publish_date->toDateString() > $today) {
            return false;
        }

        if ($announcement->expiration_date !== null
            && $announcement->expiration_date->toDateString() < $today) {
            return false;
        }

        return $this->matches($announcement, $this->userContext($user));
    }

    /**
     * Whether the announcement should be visible to the given user context.
     */
    public function matches(Announcement $announcement, object $context): bool
    {
        switch ($announcement->target_role) {
            case 'all':
                break;
            case 'students':
                if ($context->role !== 'student') {
                    return false;
                }
                break;
            case 'teachers':
                if ($context->role !== 'teacher') {
                    return false;
                }
                break;
            case 'admins':
                if (! in_array($context->role, ['system_admin', 'office_admin'], true)) {
                    return false;
                }
                break;
            default:
                return false;
        }

        $audiences = $announcement->audiences;

        if ($context->role === 'student') {
            $gradeFilters = $audiences->where('target_type', 'grade_level')
                ->pluck('target_value')
                ->map(fn ($value) => (int) $value)
                ->filter(fn ($value) => $value > 0);
            $sectionFilters = $audiences->where('target_type', 'section')->pluck('target_value');
            $studentFilters = $audiences->where('target_type', 'student')
                ->pluck('target_value')
                ->map(fn ($value) => (int) $value);

            if ($gradeFilters->isNotEmpty()
                && ($context->grade === null || ! $gradeFilters->contains($context->grade))) {
                return false;
            }

            if ($sectionFilters->isNotEmpty()
                && ($context->section === null || ! $sectionFilters->contains($context->section))) {
                return false;
            }

            if ($studentFilters->isNotEmpty()
                && ($context->studentId === null || ! $studentFilters->contains($context->studentId))) {
                return false;
            }

            return true;
        }

        if ($context->role === 'teacher') {
            $teacherFilters = $audiences->where('target_type', 'teacher')
                ->pluck('target_value')
                ->map(fn ($value) => (int) $value);

            if ($teacherFilters->isNotEmpty()
                && ($context->teacherId === null || ! $teacherFilters->contains($context->teacherId))) {
                return false;
            }

            return true;
        }

        return true;
    }

    /**
     * Human readable audience description for admin list + user cards.
     */
    public function audienceLabel(Announcement $announcement): string
    {
        $base = $announcement->audienceBaseLabel();

        $audiences = $announcement->audiences;
        $descriptions = [];

        $grades = $audiences->where('target_type', 'grade_level')
            ->pluck('target_value')
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->sort();

        $sections = $audiences->where('target_type', 'section')->pluck('target_value');

        if ($grades->isNotEmpty()) {
            $descriptions[] = 'Grade '.$grades->implode(', ');
        }
        if ($sections->isNotEmpty()) {
            $descriptions[] = 'Sections: '.$sections->implode(', ');
        }
        if ($audiences->where('target_type', 'student')->isNotEmpty()) {
            $descriptions[] = count($audiences->where('target_type', 'student')).' specific student(s)';
        }
        if ($audiences->where('target_type', 'teacher')->isNotEmpty()) {
            $descriptions[] = count($audiences->where('target_type', 'teacher')).' specific teacher(s)';
        }

        return $descriptions === []
            ? $base
            : $base.' — '.implode(' · ', $descriptions);
    }

    /**
     * Derive a student's section from the existing advisory data:
     * their approved-enrollment adviser's advisory class, falling back to
     * any subject teacher's advisory class.
     */
    public function studentSection(int $studentId): ?string
    {
        $advisory = DB::table('enrollment_requests as er')
            ->join('teachers as t', 't.id', '=', 'er.teacher_id')
            ->where('er.student_id', $studentId)
            ->where('er.status', 'approved')
            ->whereNotNull('t.advisory_class')
            ->where('t.advisory_class', '!=', '')
            ->orderByDesc('er.date_requested')
            ->value('t.advisory_class');

        if ($advisory) {
            return $advisory;
        }

        $advisory = DB::table('subjects as s')
            ->join('teachers as t', 't.id', '=', 's.teacher_id')
            ->where('s.student_id', $studentId)
            ->whereNotNull('t.advisory_class')
            ->where('t.advisory_class', '!=', '')
            ->value('t.advisory_class');

        return $advisory ?: null;
    }

    /**
     * Build the per-user facts (role, grade, section, ids) used for matching.
     */
    private function userContext(User $user): object
    {
        $context = (object) [
            'role' => $user->role,
            'studentId' => null,
            'grade' => null,
            'section' => null,
            'teacherId' => null,
        ];

        if ($user->role === 'student' && $user->student) {
            $context->studentId = (int) $user->student->id;
            $context->grade = $user->student->grade_level !== null
                ? (int) $user->student->grade_level
                : null;
            $context->section = $this->studentSection($context->studentId);
        } elseif ($user->role === 'teacher' && $user->teacher) {
            $context->teacherId = (int) $user->teacher->id;
        }

        return $context;
    }
}
