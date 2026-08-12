<?php

namespace App\Services;

use App\Models\Requirement;
use App\Models\RequirementSubmission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Business logic for the Requirement & Submission Tracker.
 *
 * Assignment is always derived from the existing enrollment system: the
 * students assigned to a requirement are exactly the students with an
 * approved enrollment request under the requirement's teacher. A teacher can
 * never assign a requirement to unrelated students.
 */
class RequirementService
{
    public const BUMP_COOLDOWN_HOURS = 24;

    /**
     * Students currently approved/enrolled under the given teacher.
     */
    public function assignedStudents(int $teacherId): Collection
    {
        return DB::table('students as s')
            ->join('enrollment_requests as er', function ($join) {
                $join->on('er.student_id', '=', 's.id')->where('er.status', '=', 'approved');
            })
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->where('er.teacher_id', $teacherId)
            ->select('s.id', 'u.name', 's.grade_level')
            ->distinct()
            ->orderBy('u.name')
            ->get();
    }

    public function assignedStudentIds(int $teacherId): array
    {
        return $this->assignedStudents($teacherId)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Effective status of a student's submission. A missing row means "pending".
     */
    public function effectiveStatus(?RequirementSubmission $submission): string
    {
        return $submission === null ? RequirementSubmission::STATUS_PENDING : $submission->status;
    }

    /**
     * Aggregated progress for the teacher overview.
     */
    public function progress(Requirement $requirement): object
    {
        $assigned = $this->assignedStudents((int) $requirement->teacher_id);
        $submissions = $requirement->submissions()->get()->keyBy('student_id');

        $counts = [
            'submitted' => 0,
            'under_review' => 0,
            'needs_revision' => 0,
            'resubmitted' => 0,
            'approved' => 0,
            'pending' => 0,
        ];

        foreach ($assigned as $student) {
            $status = $this->effectiveStatus($submissions->get((int) $student->id));
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }

        $total = $assigned->count();
        $started = $total - $counts['pending'];

        return (object) [
            'total' => $total,
            'submitted' => $started,
            'pending' => $counts['pending'],
            'needs_revision' => $counts['needs_revision'],
            'approved' => $counts['approved'],
            'under_review' => $counts['under_review'],
            'resubmitted' => $counts['resubmitted'],
            'percent' => $total > 0 ? (int) round($started / $total * 100) : 0,
        ];
    }

    public function canBump(Requirement $requirement): bool
    {
        if ($requirement->last_bumped_at === null) {
            return true;
        }

        return $requirement->last_bumped_at->lt(now()->subHours(self::BUMP_COOLDOWN_HOURS));
    }

    public function bumpCooldownRemaining(Requirement $requirement): string
    {
        if ($requirement->last_bumped_at === null) {
            return '';
        }

        $availableAt = $requirement->last_bumped_at->addHours(self::BUMP_COOLDOWN_HOURS);

        if ($availableAt->lte(now())) {
            return '';
        }

        return 'Available '.$availableAt->format('M d, g:i A');
    }

    private function recordBump(Requirement $requirement, int $teacherId): void
    {
        $requirement->update([
            'last_bumped_at' => now(),
            'last_bumped_by' => $teacherId,
            'bump_count' => ((int) $requirement->bump_count) + 1,
        ]);
    }

    /**
     * Remind every assigned student who has not submitted yet. Returns the
     * number of students reminded. Respects the 24-hour cooldown.
     */
    public function bumpAll(Requirement $requirement, int $teacherId, NotificationService $notifications): int
    {
        if (! $this->canBump($requirement)) {
            return 0;
        }

        $submittedIds = $requirement->submissions()->pluck('student_id')->map(fn ($id) => (int) $id)->all();
        $targets = $this->assignedStudents((int) $requirement->teacher_id)
            ->filter(fn ($student) => ! in_array((int) $student->id, $submittedIds, true));

        foreach ($targets as $student) {
            $notifications->requirementBumped(
                (int) $student->id,
                $requirement->title,
                route('student.requirements.show', $requirement->id)
            );
        }

        $this->recordBump($requirement, $teacherId);

        return $targets->count();
    }

    /**
     * Remind a single assigned student who has not submitted. Respects the
     * same 24-hour cooldown so students are never spammed.
     */
    public function bumpStudent(Requirement $requirement, int $studentId, int $teacherId, NotificationService $notifications): bool
    {
        if (! $this->canBump($requirement)) {
            return false;
        }

        $assigned = $this->assignedStudentIds((int) $requirement->teacher_id);

        if (! in_array($studentId, $assigned, true)) {
            return false;
        }

        $exists = $requirement->submissions()->where('student_id', $studentId)->exists();

        if ($exists) {
            return false;
        }

        $notifications->requirementBumped($studentId, $requirement->title, route('student.requirements.show', $requirement->id));

        $this->recordBump($requirement, $teacherId);

        return true;
    }

    /**
     * Fire "due soon" / "overdue" reminders for a student's current
     * requirements. Each is sent at most once per title while unread.
     */
    public function notifyDueReminders(int $studentId, Collection $requirements, NotificationService $notifications): void
    {
        foreach ($requirements as $requirement) {
            $submission = $requirement->submissions()->where('student_id', $studentId)->first();

            if ($this->effectiveStatus($submission) === RequirementSubmission::STATUS_APPROVED) {
                continue;
            }

            if ($requirement->due_date === null) {
                continue;
            }

            if ($requirement->isOverdue()) {
                $notifications->requirementDueReminder(
                    $studentId,
                    'Requirement Overdue',
                    "Reminder: \"{$requirement->title}\" is now overdue. Please submit it as soon as possible.",
                    route('student.requirements.show', $requirement->id)
                );
            } elseif ($requirement->isDueSoon()) {
                $notifications->requirementDueReminder(
                    $studentId,
                    'Requirement Due Soon',
                    "Reminder: \"{$requirement->title}\" is due on {$requirement->due_date->format('M d, Y')}.",
                    route('student.requirements.show', $requirement->id)
                );
            }
        }
    }

    public function canSubmit(Requirement $requirement, ?RequirementSubmission $submission): bool
    {
        if ($submission === null) {
            return true;
        }

        return ! in_array($submission->status, [RequirementSubmission::STATUS_UNDER_REVIEW, RequirementSubmission::STATUS_APPROVED], true);
    }
}
