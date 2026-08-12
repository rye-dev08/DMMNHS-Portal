<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\PortalMailNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function seedSettings(): void
    {
        Setting::create([
            'id' => 1,
            'current_term' => 1,
            'current_school_year' => '2026-2027',
            'max_students_per_class' => 30,
            'max_subjects_per_teacher' => 8,
            'enrollment_phase' => 'none',
        ]);
    }

    private function makeStudent(): array
    {
        $user = User::create([
            'name' => 'Jane Doe',
            'username' => 'jane.doe',
            'email' => 'jane@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'grade_level' => 7,
            'status' => 'active',
        ]);

        return [$user, $student];
    }

    private function makeTeacher(): User
    {
        $user = User::create([
            'name' => 'Mr. Smith',
            'username' => 'mr.smith',
            'email' => 'smith@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        Teacher::create([
            'user_id' => $user->id,
            'advisory_class' => 'Grade 7-A',
            'max_students' => 30,
            'max_subjects' => 8,
            'status' => 'active',
        ]);

        return $user;
    }

    private function addSubject(int $studentId, string $name): void
    {
        Subject::create([
            'student_id' => $studentId,
            'teacher_id' => 1,
            'subject_name' => $name,
            'course_code' => '',
            'teacher_code' => '',
            'room_no' => '',
        ]);
    }

    private function addGrade(int $studentId, int $subjectId): void
    {
        DB::table('grades')->updateOrInsert(
            ['student_id' => $studentId, 'subject_id' => $subjectId, 'quarter' => 'Term 1'],
            ['grade' => 90, 'remarks' => '', 'date_submitted' => now()]
        );
    }

    public function test_grade_submitted_creates_portal_notification(): void
    {
        $this->seedSettings();
        [$user, $student] = $this->makeStudent();
        $this->addSubject($student->id, 'Math');
        $subjectId = DB::table('subjects')->where('student_id', $student->id)->value('id');

        app(NotificationService::class)->gradeSubmitted($student->id, 'Math', '90', 1);

        $this->assertDatabaseCount('notifications', 1);
        $notification = $user->notifications()->first();
        $this->assertEquals('Grade Submitted', $notification->data['title']);
        $this->assertEquals(route('student.grades'), $notification->data['link']);
    }

    public function test_grades_complete_sends_once_per_term_and_refires_after_incomplete(): void
    {
        $this->seedSettings();
        [$user, $student] = $this->makeStudent();

        $this->addSubject($student->id, 'Math');
        $this->addSubject($student->id, 'Science');
        $mathId = DB::table('subjects')->where('student_id', $student->id)->where('subject_name', 'Math')->value('id');
        $scienceId = DB::table('subjects')->where('student_id', $student->id)->where('subject_name', 'Science')->value('id');

        $service = app(NotificationService::class);

        // One of two graded -> not complete yet.
        $this->addGrade($student->id, $mathId);
        $service->syncGradeCompletion($student->id);
        $this->assertDatabaseCount('notifications', 0);

        // Second graded -> complete -> sends exactly one notification.
        $this->addGrade($student->id, $scienceId);
        $service->syncGradeCompletion($student->id);
        $service->syncGradeCompletion($student->id); // duplicate call must not re-send
        $this->assertDatabaseCount('notifications', 1);
        $this->assertEquals('All Grades Complete', $user->notifications()->first()->data['title']);

        // Editing an existing grade must NOT re-send.
        $this->addGrade($student->id, $mathId);
        $service->syncGradeCompletion($student->id);
        $this->assertDatabaseCount('notifications', 1);

        // New subject added -> incomplete again -> completion refires once.
        $this->addSubject($student->id, 'English');
        $englishId = DB::table('subjects')->where('student_id', $student->id)->where('subject_name', 'English')->value('id');

        $service->syncGradeCompletion($student->id); // still incomplete
        $this->assertDatabaseCount('notifications', 1);

        $this->addGrade($student->id, $englishId);
        $service->syncGradeCompletion($student->id);
        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_enrollment_events_notify_student_and_teacher(): void
    {
        $this->seedSettings();
        [$studentUser, $student] = $this->makeStudent();
        $teacherUser = $this->makeTeacher();

        $teacherId = DB::table('teachers')->where('user_id', $teacherUser->id)->value('id');

        app(NotificationService::class)->enrollmentRequested((int) $teacherId, 'Jane Doe');
        app(NotificationService::class)->enrollmentApproved($student->id, 'Mr. Smith', '2026-2027');
        app(NotificationService::class)->enrollmentRejected($student->id, 'Mr. Smith');

        $this->assertDatabaseCount('notifications', 3);

        $teacherNotifications = $teacherUser->notifications()->get();
        $studentNotifications = $studentUser->notifications()->get();

        $this->assertCount(1, $teacherNotifications);
        $this->assertCount(2, $studentNotifications);
        $this->assertEquals('New Enrollment Request', $teacherNotifications->first()->data['title']);

        // The student receives both an "Approved" and a "Rejected" notification,
        // created within the same second, so the collection ordering is not
        // guaranteed. Assert on the set of titles instead of ->first().
        $titles = $studentNotifications->pluck('data.title')->sort()->values()->all();
        $this->assertEqualsCanonicalizing(['Enrollment Approved', 'Enrollment Rejected'], $titles);

        // Mail channel is included when the user has an email, otherwise portal only.
        $mailNotification = new PortalMailNotification(['title' => 'T', 'message' => 'M']);
        $this->assertContains('database', $mailNotification->via($studentUser));
        $this->assertContains('mail', $mailNotification->via($studentUser));

        $noEmail = User::create([
            'name' => 'No Mail',
            'username' => 'no.mail',
            'email' => null,
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'student',
            'status' => 'active',
        ]);
        $this->assertContains('database', $mailNotification->via($noEmail));
        $this->assertNotContains('mail', $mailNotification->via($noEmail));
    }

    public function test_notification_routes_require_auth_and_mark_read(): void
    {
        $this->seedSettings();
        [$user, $student] = $this->makeStudent();
        $this->addSubject($student->id, 'Math');

        app(NotificationService::class)->gradeSubmitted($student->id, 'Math', '90', 1);

        $this->actingAs($user)->get(route('notifications.index'))->assertOk();
        $this->actingAs($user)->post(route('notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $user->unreadNotifications()->count());

        Auth::logout();
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_grades_complete_flag_is_unique_per_student_term_and_year(): void
    {
        $this->seedSettings();
        [$user, $student] = $this->makeStudent();

        $this->addSubject($student->id, 'Math');
        $mathId = DB::table('subjects')->where('student_id', $student->id)->value('id');
        $this->addGrade($student->id, $mathId);

        $service = app(NotificationService::class);
        $service->syncGradeCompletion($student->id);

        $flags = DB::table('grade_completion_flags')->get();
        $this->assertCount(1, $flags);

        Setting::where('id', 1)->update(['current_term' => 2]);
        $service->syncGradeCompletion($student->id);

        $flags = DB::table('grade_completion_flags')->get();
        $this->assertCount(2, $flags);
    }
}
