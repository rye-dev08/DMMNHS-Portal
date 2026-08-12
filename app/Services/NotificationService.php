<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\GradeCompletionFlag;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\PortalMailNotification;
use App\Notifications\PortalNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Central place for every portal notification. Controllers call one of the
 * named methods below and the service decides the recipient(s), the message
 * and whether an email is also sent.
 *
 * Channel rules:
 *   - Student (portal + email): password changed, enrollment approved/rejected,
 *     all grades complete, enrollment phase opened, new school year started,
 *     password reset, welcome email, email verification.
 *   - Teacher (portal + email): new enrollment request, advisory class
 *     assigned/changed, password changed.
 *   - Portal only: grade submitted/updated, profile/info updated, subject
 *     added/removed, phase closed, term changed, account info updated,
 *     calendar events, announcements, requirement submissions,
 *     grade submission completion/overdue,
 *     subject assignment updates, enrollment phase changes.
 *   - Admins never receive notifications or emails.
 */
class NotificationService
{
    private const COOLDOWN_SECONDS = 60;

    public function __construct(private AnnouncementService $announcements) {}

    private function activeUsers(string $role): Collection
    {
        return User::where('role', $role)->where('status', 'active')->get();
    }

    private function studentUser(int $studentId): ?User
    {
        return Student::with('user')->find($studentId)?->user;
    }

    private function teacherUser(int $teacherId): ?User
    {
        return Teacher::with('user')->find($teacherId)?->user;
    }

    private function dashboardLink(User $user): string
    {
        return match ($user->role) {
            'teacher' => route('teacher.dashboard'),
            'student' => route('student.dashboard'),
            'office_admin' => route('office.dashboard'),
            default => route('admin.dashboard'),
        };
    }

    private function send(User $user, array $data, bool $email = false): void
    {
        if (empty($data['message'])) {
            return;
        }

        $user->notify($email
            ? new PortalMailNotification($data)
            : new PortalNotification($data));
    }

    /**
     * Deduplication helper: skip sending if the same user already has
     * an unread notification with the same title + message within the
     * cooldown window.
     */
    private function isDuplicate(User $user, string $title, string $message): bool
    {
        return $user->notifications()
            ->where('data', 'like', '%"title":"'.addslashes($title).'"%')
            ->where('data', 'like', '%"message":"'.addslashes($message).'"%')
            ->where('created_at', '>=', now()->subSeconds(self::COOLDOWN_SECONDS))
            ->exists();
    }

    private function safeSend(User $user, array $data, bool $email = false): void
    {
        if (empty($data['message'])) {
            return;
        }

        if ($this->isDuplicate($user, $data['title'] ?? '', $data['message'] ?? '')) {
            return;
        }

        $this->send($user, $data, $email);
    }

    /*
    |--------------------------------------------------------------------------
    | Student events
    |--------------------------------------------------------------------------
    */

    public function enrollmentApproved(int $studentId, string $teacherName, string $schoolYear): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'Enrollment Approved',
            'message' => "Your enrollment request to {$teacherName} has been approved for {$schoolYear}.",
            'kind' => 'success',
            'link' => route('student.enrollment'),
            'subject' => 'Your Enrollment Request Was Approved',
            'lines' => [
                "Your enrollment request to {$teacherName} for school year {$schoolYear} has been approved.",
                'You may now view your class schedule and subjects in the portal.',
            ],
            'action_text' => 'View Enrollment',
            'action_url' => route('student.enrollment'),
        ], true);
    }

    public function enrollmentRejected(int $studentId, string $teacherName): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'Enrollment Rejected',
            'message' => "Your enrollment request to {$teacherName} was not approved.",
            'kind' => 'error',
            'link' => route('student.enrollment'),
            'subject' => 'Enrollment Request Update',
            'lines' => [
                "Your enrollment request to {$teacherName} was not approved.",
                'You may file a new request to another available teacher.',
            ],
            'action_text' => 'View Enrollment',
            'action_url' => route('student.enrollment'),
        ], true);
    }

    public function gradeSubmitted(int $studentId, string $subjectName, string $grade, int $term): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'Grade Submitted',
            'message' => "Your grade for {$subjectName} (Term {$term}) has been submitted: {$grade}.",
            'kind' => 'grades',
            'link' => route('student.grades'),
        ]);
    }

    public function allGradesComplete(int $studentId, int $term, string $schoolYear): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'All Grades Complete',
            'message' => "All your grades for Term {$term} ({$schoolYear}) have been submitted.",
            'kind' => 'success',
            'link' => route('student.grades'),
            'subject' => 'All Your Grades Are Now Complete',
            'lines' => [
                "All your grades for Term {$term} of school year {$schoolYear} have been submitted.",
                'You can review your grades anytime on the Grades page.',
            ],
            'action_text' => 'View Grades',
            'action_url' => route('student.grades'),
        ], true);
    }

    public function subjectAdded(int $studentId, string $subjectName): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'New Subject Added',
            'message' => "{$subjectName} has been added to your class schedule.",
            'kind' => 'subject',
            'link' => route('student.schedule'),
        ]);
    }

    public function subjectRemoved(int $studentId, string $subjectName): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'Subject Removed',
            'message' => "{$subjectName} has been removed from your class schedule.",
            'kind' => 'subject',
            'link' => route('student.schedule'),
        ]);
    }

    public function profileUpdated(User $user): void
    {
        $this->send($user, [
            'title' => 'Profile Updated',
            'message' => 'Your personal information has been updated.',
            'kind' => 'info',
            'link' => $this->dashboardLink($user),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Teacher events
    |--------------------------------------------------------------------------
    */

    public function enrollmentRequested(int $teacherId, string $studentName): void
    {
        $teacher = $this->teacherUser($teacherId);

        if (! $teacher) {
            return;
        }

        $this->send($teacher, [
            'title' => 'New Enrollment Request',
            'message' => "{$studentName} has requested to enroll in your class.",
            'kind' => 'enrollment',
            'link' => route('teacher.enrollment-requests'),
            'subject' => 'New Enrollment Request',
            'lines' => [
                "{$studentName} has sent an enrollment request to your advisory class.",
                'Review and approve or reject the request from the Enrollment Requests page.',
            ],
            'action_text' => 'Review Requests',
            'action_url' => route('teacher.enrollment-requests'),
        ], true);
    }

    /**
     * Reminder sent by the Office Administrator to teachers who have not yet
     * submitted grades for a grading period. Portal + email.
     */
    public function gradeSubmissionReminder(int $teacherUserId, string $subjectLabel, int $term, string $schoolYear, ?string $deadline = null): void
    {
        $teacher = User::find($teacherUserId);

        if (! $teacher || $teacher->role !== 'teacher') {
            return;
        }

        $deadlineText = $deadline ? ' The deadline is '.$deadline.'.' : '';

        $this->send($teacher, [
            'title' => 'Grade Submission Reminder',
            'message' => "Reminder: Your grade submission deadline is approaching for {$subjectLabel} (Term {$term}, {$schoolYear}).{$deadlineText}",
            'kind' => 'info',
            'link' => route('teacher.grade-submissions'),
            'subject' => 'Grade Submission Deadline Reminder',
            'lines' => [
                "Reminder: Your grade submission deadline is approaching for {$subjectLabel} (Term {$term}, {$schoolYear}).",
                'Please submit all outstanding grades as soon as possible.',
            ],
            'action_text' => 'Open Grade Submissions',
            'action_url' => route('teacher.grade-submissions'),
        ], true);
    }

    public function advisoryAssigned(int $teacherUserId, string $advisoryClass): void
    {
        $teacher = User::find($teacherUserId);

        if (! $teacher) {
            return;
        }

        $this->send($teacher, [
            'title' => 'Advisory Class Assigned',
            'message' => "You have been assigned advisory class {$advisoryClass}.",
            'kind' => 'success',
            'link' => route('teacher.dashboard'),
            'subject' => 'Advisory Class Assigned',
            'lines' => [
                "Your advisory class has been set to {$advisoryClass}.",
                'You can now manage subjects and enrollment requests for this class.',
            ],
            'action_text' => 'Open Dashboard',
            'action_url' => route('teacher.dashboard'),
        ], true);
    }

    public function advisoryChanged(int $teacherUserId, string $advisoryClass): void
    {
        $teacher = User::find($teacherUserId);

        if (! $teacher) {
            return;
        }

        $this->send($teacher, [
            'title' => 'Advisory Class Changed',
            'message' => "Your advisory class has been updated to {$advisoryClass}.",
            'kind' => 'info',
            'link' => route('teacher.dashboard'),
            'subject' => 'Advisory Class Updated',
            'lines' => [
                "Your advisory class has been updated to {$advisoryClass}.",
                'You can now manage subjects and enrollment requests for this class.',
            ],
            'action_text' => 'Open Dashboard',
            'action_url' => route('teacher.dashboard'),
        ], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Shared (self) events
    |--------------------------------------------------------------------------
    */

    public function passwordChanged(User $user): void
    {
        $this->send($user, [
            'title' => 'Password Changed',
            'message' => 'Your account password was changed.',
            'kind' => 'security',
            'link' => $this->dashboardLink($user),
            'subject' => 'Your Password Was Changed',
            'lines' => [
                'The password for your DMMNHS Student Portal account was recently changed.',
                'If this was not you, please contact the administrator immediately.',
            ],
            'action_text' => 'Open Portal',
            'action_url' => $this->dashboardLink($user),
        ], true);
    }

    public function accountUpdated(User $user): void
    {
        $this->send($user, [
            'title' => 'Account Updated',
            'message' => 'An administrator updated your account information.',
            'kind' => 'info',
            'link' => $this->dashboardLink($user),
        ]);
    }

    /**
     * Generic acknowledgement sent after a message to the administration has
     * been reviewed. Intentionally reveals nothing about the moderation
     * decision (valid/invalid/blocked) to the sender.
     */
    public function messageReceived(User $user): void
    {
        $this->send($user, [
            'title' => 'Message Received',
            'message' => 'Message received!',
            'kind' => 'info',
            'link' => route('contact'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Requirement & Submission Tracker events
    |--------------------------------------------------------------------------
    */

    public function requirementAssigned(int $studentId, string $requirementTitle, string $link): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'New Requirement',
            'message' => "A new requirement has been assigned: {$requirementTitle}.",
            'kind' => 'requirement',
            'link' => $link,
        ]);
    }

    public function requirementBumped(int $studentId, string $requirementTitle, string $link): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'Requirement Reminder',
            'message' => "Reminder: You still have an outstanding requirement: {$requirementTitle}.",
            'kind' => 'requirement',
            'link' => $link,
        ]);
    }

    public function submissionApproved(int $studentId, string $requirementTitle, string $link): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'Submission Approved',
            'message' => "Your submission for \"{$requirementTitle}\" has been approved.",
            'kind' => 'success',
            'link' => $link,
        ]);
    }

    public function submissionNeedsRevision(int $studentId, string $requirementTitle, string $link): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'Submission Needs Revision',
            'message' => "Your submission for \"{$requirementTitle}\" needs revision.",
            'kind' => 'error',
            'link' => $link,
        ]);
    }

    /**
     * Due-soon/overdue reminder. Sent at most once per title while the student
     * still has that reminder unread, so the same requirement is never spammed.
     */
    public function requirementDueReminder(int $studentId, string $title, string $message, string $link): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $already = $student->notifications()
            ->where('data', 'like', '%"title":"'.$title.'"%')
            ->whereNull('read_at')
            ->exists();

        if ($already) {
            return;
        }

        $this->send($student, [
            'title' => $title,
            'message' => $message,
            'kind' => 'requirement',
            'link' => $link,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Broadcasts (admin actions)
    |--------------------------------------------------------------------------
    */

    public function enrollmentPhaseOpened(): void
    {
        foreach ($this->activeUsers('student') as $student) {
            $this->send($student, [
                'title' => 'Enrollment Phase Open',
                'message' => 'The enrollment phase for the school year is now open. You may file your enrollment request.',
                'kind' => 'info',
                'link' => route('student.enrollment'),
                'subject' => 'Enrollment Is Now Open',
                'lines' => [
                    'The enrollment phase for the new school year has opened.',
                    'You may now file your enrollment request to an available teacher.',
                ],
                'action_text' => 'File Enrollment',
                'action_url' => route('student.enrollment'),
            ], true);
        }
    }

    public function enrollmentPhaseClosed(): void
    {
        foreach ($this->activeUsers('student') as $student) {
            $this->send($student, [
                'title' => 'Enrollment Phase Closed',
                'message' => 'The enrollment phase has closed. No new enrollment requests can be filed.',
                'kind' => 'info',
                'link' => route('student.enrollment'),
            ]);
        }
    }

    public function termChanged(int $term): void
    {
        foreach ($this->activeUsers('student')->merge($this->activeUsers('teacher')) as $user) {
            $this->send($user, [
                'title' => 'New Term Started',
                'message' => "Term {$term} has begun. The system has been reset for the new term.",
                'kind' => 'info',
                'link' => $this->dashboardLink($user),
            ]);
        }
    }

    public function newSchoolYearStarted(string $schoolYear): void
    {
        foreach ($this->activeUsers('student') as $student) {
            $this->send($student, [
                'title' => 'New School Year',
                'message' => "A new school year ({$schoolYear}) has started. You may need to file a new enrollment request.",
                'kind' => 'info',
                'link' => route('student.enrollment'),
                'subject' => 'New School Year Has Started',
                'lines' => [
                    "A new school year ({$schoolYear}) has started.",
                    'Please file your enrollment request for the new school year.',
                ],
                'action_text' => 'File Enrollment',
                'action_url' => route('student.enrollment'),
            ], true);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Grades-complete dedup
    |--------------------------------------------------------------------------
    */

    /**
     * Recompute whether a student has a grade for every subject in the current
     * term. Sends the "all grades complete" notification exactly once per
     * student + term + school year, and re-arms it when the student goes back
     * to incomplete (e.g. a new subject is added) so it can fire again once
     * everything is complete once more.
     */
    public function syncGradeCompletion(int $studentId): void
    {
        if ($this->studentUser($studentId) === null) {
            return;
        }

        $settings = Setting::find(1);
        $term = (int) ($settings->current_term ?? 1);
        $schoolYear = (string) ($settings->current_school_year ?? '');

        $totalSubjects = (int) DB::table('subjects')->where('student_id', $studentId)->count();

        $gradedSubjects = (int) DB::table('grades')
            ->where('student_id', $studentId)
            ->where('quarter', 'Term '.$term)
            ->distinct('subject_id')
            ->count('subject_id');

        $complete = $totalSubjects > 0 && $gradedSubjects >= $totalSubjects;

        $flag = GradeCompletionFlag::firstOrNew([
            'student_id' => $studentId,
            'term' => $term,
            'school_year' => $schoolYear,
        ]);

        if ($complete && ! $flag->notified) {
            $flag->notified = true;
            $flag->save();

            $this->allGradesComplete($studentId, $term, $schoolYear);
        } elseif (! $complete) {
            $flag->notified = false;
            $flag->save();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Password events
    |--------------------------------------------------------------------------
    */

    public function passwordReset(int $studentId): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'Password Reset',
            'message' => 'Your password has been reset by an administrator.',
            'kind' => 'security',
            'link' => $this->dashboardLink($student),
            'subject' => 'Your Password Was Reset',
            'lines' => [
                'Your DMMNHS Student Portal password has been reset by an administrator.',
                'If you did not request this change, please contact the administrator immediately.',
            ],
            'action_text' => 'Open Portal',
            'action_url' => $this->dashboardLink($student),
        ], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Enrollment events
    |--------------------------------------------------------------------------
    */

    public function enrollmentSubmitted(int $studentId, string $teacherName): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->safeSend($student, [
            'title' => 'Enrollment Submitted',
            'message' => "Your enrollment request to {$teacherName} has been submitted and is pending approval.",
            'kind' => 'enrollment',
            'link' => route('student.enrollment'),
        ]);
    }

    public function enrollmentRevisionRequired(int $studentId, string $teacherName): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->safeSend($student, [
            'title' => 'Enrollment Needs Revision',
            'message' => "Your enrollment request to {$teacherName} needs revision. Please check the details and resubmit.",
            'kind' => 'enrollment',
            'link' => route('student.enrollment'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Academic Calendar events
    |--------------------------------------------------------------------------
    */

    public function calendarEventCreated(string $eventTitle, string $schoolYear, int $term): void
    {
        foreach ($this->activeUsers('student') as $student) {
            $this->safeSend($student, [
                'title' => 'New Calendar Event',
                'message' => "A new calendar event \"{$eventTitle}\" has been added for {$schoolYear}, Term {$term}.",
                'kind' => 'info',
                'link' => route('student.calendar'),
            ]);
        }
    }

    public function calendarEventUpdated(string $eventTitle, string $schoolYear, int $term): void
    {
        foreach ($this->activeUsers('student') as $student) {
            $this->safeSend($student, [
                'title' => 'Calendar Event Updated',
                'message' => "The calendar event \"{$eventTitle}\" has been updated for {$schoolYear}, Term {$term}.",
                'kind' => 'info',
                'link' => route('student.calendar'),
            ]);
        }
    }

    public function calendarEventCancelled(string $eventTitle, string $schoolYear, int $term): void
    {
        foreach ($this->activeUsers('student') as $student) {
            $this->safeSend($student, [
                'title' => 'Calendar Event Cancelled',
                'message' => "The calendar event \"{$eventTitle}\" for {$schoolYear}, Term {$term} has been cancelled.",
                'kind' => 'error',
                'link' => route('student.calendar'),
            ]);
        }
    }

    public function upcomingEventReminder(string $eventTitle, string $eventDate, string $schoolYear, int $term): void
    {
        foreach ($this->activeUsers('student') as $student) {
            $this->safeSend($student, [
                'title' => 'Upcoming Event Reminder',
                'message' => "Reminder: \"{$eventTitle}\" is scheduled for {$eventDate}.",
                'kind' => 'info',
                'link' => route('student.calendar'),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Announcement events
    |--------------------------------------------------------------------------
    */

    /**
     * Notify every active student/teacher who is targeted by the announcement
     * (based on its target role and audience refinements). Portal-only; a
     * published announcement with a future publish date is skipped because it
     * is not visible in the feeds yet.
     */
    public function announcementCreated(Announcement $announcement): void
    {
        if (! $announcement->isPublished()
            || $announcement->publish_date === null
            || $announcement->publish_date->gt(now())) {
            return;
        }

        foreach ($this->announcements->recipientUsers($announcement) as $user) {
            $this->safeSend($user, [
                'title' => 'New Announcement',
                'message' => "A new announcement \"{$announcement->title}\" has been posted.",
                'kind' => 'info',
                'link' => route('announcements'),
            ]);
        }
    }

    public function announcementUpdated(Announcement $announcement): void
    {
        if (! $announcement->isPublished()) {
            return;
        }

        foreach ($this->announcements->recipientUsers($announcement) as $user) {
            $this->safeSend($user, [
                'title' => 'Announcement Updated',
                'message' => "The announcement \"{$announcement->title}\" has been updated.",
                'kind' => 'info',
                'link' => route('announcements'),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Requirement submission events
    |--------------------------------------------------------------------------
    */

    public function requirementSubmitted(int $studentId, string $requirementTitle, string $link): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->safeSend($student, [
            'title' => 'Requirement Submitted',
            'message' => "Your submission for \"{$requirementTitle}\" has been received.",
            'kind' => 'requirement',
            'link' => $link,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Grade submission events
    |--------------------------------------------------------------------------
    */

    public function gradeSubmissionCompleted(int $teacherUserId, string $subjectLabel, int $term, string $schoolYear): void
    {
        $teacher = User::find($teacherUserId);

        if (! $teacher || $teacher->role !== 'teacher') {
            return;
        }

        $this->safeSend($teacher, [
            'title' => 'Grade Submission Complete',
            'message' => "All grades for {$subjectLabel} (Term {$term}, {$schoolYear}) have been submitted.",
            'kind' => 'grades',
            'link' => route('teacher.grade-submissions'),
        ]);
    }

    public function gradeSubmissionOverdue(int $teacherUserId, string $subjectLabel, int $term, string $schoolYear): void
    {
        $teacher = User::find($teacherUserId);

        if (! $teacher || $teacher->role !== 'teacher') {
            return;
        }

        $this->safeSend($teacher, [
            'title' => 'Grade Submission Overdue',
            'message' => "Grade submission for {$subjectLabel} (Term {$term}, {$schoolYear}) is now overdue.",
            'kind' => 'error',
            'link' => route('teacher.grade-submissions'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Teacher assignment events
    |--------------------------------------------------------------------------
    */

    public function subjectAssignmentUpdated(int $teacherUserId, string $subjectName): void
    {
        $teacher = User::find($teacherUserId);

        if (! $teacher || $teacher->role !== 'teacher') {
            return;
        }

        $this->safeSend($teacher, [
            'title' => 'Subject Assignment Updated',
            'message' => "Your subject assignment for \"{$subjectName}\" has been updated.",
            'kind' => 'subject',
            'link' => route('teacher.dashboard'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | System events
    |--------------------------------------------------------------------------
    */

    public function schoolYearChanged(string $oldYear, string $newYear): void
    {
        foreach ($this->activeUsers('student') as $student) {
            $this->safeSend($student, [
                'title' => 'School Year Changed',
                'message' => "The school year has changed from {$oldYear} to {$newYear}.",
                'kind' => 'info',
                'link' => route('student.dashboard'),
            ]);
        }
    }

    public function semesterChanged(int $oldTerm, int $newTerm): void
    {
        foreach ($this->activeUsers('student')->merge($this->activeUsers('teacher')) as $user) {
            $this->safeSend($user, [
                'title' => 'Semester Changed',
                'message' => "The semester has changed from Term {$oldTerm} to Term {$newTerm}.",
                'kind' => 'info',
                'link' => $this->dashboardLink($user),
            ]);
        }
    }

    public function enrollmentPhaseChanged(string $phase): void
    {
        foreach ($this->activeUsers('student') as $student) {
            $this->safeSend($student, [
                'title' => 'Enrollment Phase Changed',
                'message' => "The enrollment phase has been updated to: {$phase}.",
                'kind' => 'info',
                'link' => route('student.enrollment'),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Email-only notifications
    |--------------------------------------------------------------------------
    */

    public function welcomeEmail(User $user): void
    {
        $this->send($user, [
            'title' => 'Welcome to DMMNHS Student Portal',
            'message' => 'Welcome! Your account has been created and you can now access the student portal.',
            'kind' => 'success',
            'link' => route('login'),
            'subject' => 'Welcome to DMMNHS Student Portal',
            'greeting' => "Hello {$user->name},",
            'lines' => [
                'Welcome to the DMMNHS Student Portal!',
                'Your account has been created successfully.',
                'You can now log in and access your student dashboard.',
            ],
            'action_text' => 'Go to Login',
            'action_url' => route('login'),
        ], true);
    }

    public function emailVerification(User $user): void
    {
        $this->send($user, [
            'title' => 'Verify Your Email Address',
            'message' => 'Please verify your email address to activate your portal account.',
            'kind' => 'info',
            'link' => route('login'),
            'subject' => 'Verify Your Email Address',
            'greeting' => "Hello {$user->name},",
            'lines' => [
                'Please verify your email address to activate your portal account.',
                'Click the button below to verify your email.',
            ],
            'action_text' => 'Verify Email',
            'action_url' => route('login'),
        ], true);
    }

    public function accountLocked(User $user): void
    {
        $this->send($user, [
            'title' => 'Account Locked',
            'message' => 'Your portal account has been locked due to suspicious activity.',
            'kind' => 'error',
            'link' => $this->dashboardLink($user),
            'subject' => 'Your Account Has Been Locked',
            'greeting' => "Hello {$user->name},",
            'lines' => [
                'Your DMMNHS Student Portal account has been locked.',
                'Please contact the administrator to regain access.',
            ],
            'action_text' => 'Contact Administrator',
            'action_url' => route('contact'),
        ], true);
    }

    public function importantAnnouncementEmail(string $title, string $message): void
    {
        foreach ($this->activeUsers('student') as $student) {
            $this->send($student, [
                'title' => 'Important Announcement',
                'message' => "Important: {$title}",
                'kind' => 'info',
                'link' => route('announcements'),
                'subject' => "Important Announcement: {$title}",
                'greeting' => "Hello {$student->name},",
                'lines' => [
                    $message,
                    'Please review this important announcement in the portal.',
                ],
                'action_text' => 'View Announcement',
                'action_url' => route('announcements'),
            ], true);
        }
    }

    public function majorCalendarEventEmail(string $eventTitle, string $eventDate, string $schoolYear): void
    {
        foreach ($this->activeUsers('student') as $student) {
            $this->send($student, [
                'title' => 'School Event Reminder',
                'message' => "Upcoming school event: {$eventTitle} on {$eventDate}.",
                'kind' => 'info',
                'link' => route('student.calendar'),
                'subject' => "School Event: {$eventTitle}",
                'greeting' => "Hello {$student->name},",
                'lines' => [
                    "A major school event is coming up: {$eventTitle}",
                    "Date: {$eventDate}",
                    "School Year: {$schoolYear}",
                ],
                'action_text' => 'View Calendar',
                'action_url' => route('student.calendar'),
            ], true);
        }
    }

    public function requirementDeadlineReminderEmail(int $studentId, string $requirementTitle, string $dueDate): void
    {
        $student = $this->studentUser($studentId);

        if (! $student) {
            return;
        }

        $this->send($student, [
            'title' => 'Requirement Deadline Reminder',
            'message' => "Reminder: \"{$requirementTitle}\" is due on {$dueDate}.",
            'kind' => 'requirement',
            'link' => route('student.requirements'),
            'subject' => 'Requirement Deadline Reminder',
            'greeting' => "Hello {$student->name},",
            'lines' => [
                "This is a reminder that the requirement \"{$requirementTitle}\" is due on {$dueDate}.",
                'Please submit it before the deadline to avoid any issues.',
            ],
            'action_text' => 'View Requirements',
            'action_url' => route('student.requirements'),
        ], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Message events
    |--------------------------------------------------------------------------
    */

    public function newMessageReceived(User $user): void
    {
        $this->safeSend($user, [
            'title' => 'New Message Received',
            'message' => 'You have a new message from the administration.',
            'kind' => 'info',
            'link' => route('contact'),
        ]);
    }
}
