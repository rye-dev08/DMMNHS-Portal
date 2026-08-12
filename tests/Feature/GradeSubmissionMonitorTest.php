<?php

namespace Tests\Feature;

use App\Models\EnrollmentRequest;
use App\Models\GradeSubmissionDeadline;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GradeSubmissionMonitorTest extends TestCase
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

    private function makeUser(string $role, string $username): User
    {
        return User::create([
            'name' => ucfirst($username),
            'username' => $username,
            'email' => $username.'@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => $role,
            'status' => 'active',
        ]);
    }

    private function makeTeacher(User $user, string $advisory = 'Grade 7-A'): Teacher
    {
        return Teacher::create([
            'user_id' => $user->id,
            'advisory_class' => $advisory,
            'max_students' => 30,
            'max_subjects' => 8,
            'status' => 'active',
        ]);
    }

    private function makeStudent(User $user, int $gradeLevel = 7): Student
    {
        return Student::create([
            'user_id' => $user->id,
            'grade_level' => $gradeLevel,
            'status' => 'active',
        ]);
    }

    private function approveEnrollment(int $studentId, int $teacherId): void
    {
        EnrollmentRequest::create([
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'status' => 'approved',
            'date_requested' => now()->subMonths(2),
        ]);
    }

    private function addSubject(int $studentId, int $teacherId, string $name): int
    {
        $subject = Subject::create([
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'subject_name' => $name,
            'course_code' => '',
            'teacher_code' => '',
            'room_no' => '',
        ]);

        return (int) $subject->id;
    }

    private function addGrade(int $studentId, int $subjectId, string $quarter = 'Term 1', ?string $date = null): void
    {
        DB::table('grades')->insert([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'grade' => '90',
            'remarks' => 'Outstanding',
            'quarter' => $quarter,
            'date_submitted' => $date ?? now()->subDays(5)->toDateTimeString(),
        ]);
    }

    /**
     * @return array{0: User, 1: User, 2: Teacher, 3: Student}
     */
    private function scenario(): array
    {
        $this->seedSettings();
        $office = $this->makeUser('office_admin', 'office');
        $teacherUser = $this->makeUser('teacher', 'teacher_a');
        $teacher = $this->makeTeacher($teacherUser);
        $studentUser = $this->makeUser('student', 'student_a');
        $student = $this->makeStudent($studentUser);
        $this->approveEnrollment((int) $student->id, (int) $teacher->id);

        return [$office, $teacherUser, $teacher, $student];
    }

    public function test_office_admin_can_view_monitor_page(): void
    {
        [$office] = $this->scenario();

        $this->actingAs($office)
            ->get(route('office.grade-submissions'))
            ->assertOk()
            ->assertSee('Teacher Grade Submission Monitor', false)
            ->assertSee('Total Teachers', false)
            ->assertSee('Overall Completion', false)
            ->assertSee('Submission Details', false);
    }

    public function test_non_office_roles_are_blocked_from_monitor(): void
    {
        [$office, $teacherUser] = $this->scenario();

        $this->actingAs($teacherUser)
            ->get(route('office.grade-submissions'))
            ->assertRedirect(route('login'));

        $studentUser = $this->makeUser('student', 'student_blocked');
        $this->makeStudent($studentUser);

        $this->actingAs($studentUser)
            ->get(route('office.grade-submissions'))
            ->assertRedirect(route('login'));

        $this->actingAs($office)->get(route('office.grade-submissions'))->assertOk();
    }

    public function test_units_and_summary_derive_status_from_existing_grades(): void
    {
        [$office, $teacherUser, $teacher, $student] = $this->scenario();
        $subjectId = $this->addSubject((int) $student->id, (int) $teacher->id, 'Mathematics');

        $this->actingAs($office)
            ->get(route('office.grade-submissions'))
            ->assertOk()
            ->assertSee('Mathematics')
            ->assertSee('Pending')
            ->assertSee('0/1 students graded', false);

        $this->addGrade((int) $student->id, $subjectId);

        $this->actingAs($office)
            ->get(route('office.grade-submissions'))
            ->assertOk()
            ->assertSee('1/1 students graded', false)
            ->assertSee('Submitted');
    }

    public function test_status_becomes_late_when_deadline_passes_without_submission(): void
    {
        [$office, $teacherUser, $teacher, $student] = $this->scenario();
        $this->addSubject((int) $student->id, (int) $teacher->id, 'Science');

        GradeSubmissionDeadline::create([
            'school_year' => '2026-2027',
            'term' => 1,
            'subject_name' => '',
            'deadline' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($office)
            ->get(route('office.grade-submissions'))
            ->assertOk()
            ->assertSee('Late')
            ->assertSee('(overdue)', false);
    }

    public function test_submitted_unit_does_not_turn_late_after_deadline(): void
    {
        [$office, $teacherUser, $teacher, $student] = $this->scenario();
        $subjectId = $this->addSubject((int) $student->id, (int) $teacher->id, 'English');
        $this->addGrade((int) $student->id, $subjectId);

        GradeSubmissionDeadline::create([
            'school_year' => '2026-2027',
            'term' => 1,
            'subject_name' => '',
            'deadline' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($office)
            ->get(route('office.grade-submissions'))
            ->assertOk()
            ->assertSee('Submitted')
            ->assertDontSee('(overdue)', false);
    }

    public function test_summary_buckets_teachers_correctly(): void
    {
        $this->seedSettings();
        $office = $this->makeUser('office_admin', 'office');

        // Teacher A: fully submitted.
        $teacherAUser = $this->makeUser('teacher', 'teacher_a');
        $teacherA = $this->makeTeacher($teacherAUser);
        $studentAUser = $this->makeUser('student', 'student_a');
        $studentA = $this->makeStudent($studentAUser);
        $this->approveEnrollment((int) $studentA->id, (int) $teacherA->id);
        $subjectA = $this->addSubject((int) $studentA->id, (int) $teacherA->id, 'Math');
        $this->addGrade((int) $studentA->id, $subjectA);

        // Teacher B: pending.
        $teacherBUser = $this->makeUser('teacher', 'teacher_b');
        $teacherB = $this->makeTeacher($teacherBUser);
        $studentBUser = $this->makeUser('student', 'student_b');
        $studentB = $this->makeStudent($studentBUser);
        $this->approveEnrollment((int) $studentB->id, (int) $teacherB->id);
        $this->addSubject((int) $studentB->id, (int) $teacherB->id, 'Science');

        $response = $this->actingAs($office)->get(route('office.grade-submissions'));

        $response->assertOk()
            ->assertSee('Total Teachers', false)
            ->assertSee('Submitted');
    }

    public function test_deadline_crud_works(): void
    {
        [$office] = $this->scenario();

        $this->actingAs($office)
            ->post(route('office.grade-submissions.deadlines.store'), [
                'school_year' => '2026-2027',
                'term' => 1,
                'subject_name' => 'Mathematics',
                'deadline' => now()->addDays(3)->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('grade_submission_deadlines', [
            'school_year' => '2026-2027',
            'term' => 1,
            'subject_name' => 'Mathematics',
        ]);

        $deadline = GradeSubmissionDeadline::first();

        $this->actingAs($office)
            ->put(route('office.grade-submissions.deadlines.update', $deadline->id), [
                'deadline' => now()->addDays(10)->toDateString(),
            ])
            ->assertRedirect();

        $this->assertEquals(now()->addDays(10)->toDateString(), $deadline->fresh()->deadline->toDateString());

        $this->actingAs($office)
            ->delete(route('office.grade-submissions.deadlines.destroy', $deadline->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('grade_submission_deadlines', ['id' => $deadline->id]);
    }

    public function test_reminder_notifies_the_pending_teacher(): void
    {
        [$office, $teacherUser, $teacher, $student] = $this->scenario();
        $this->addSubject((int) $student->id, (int) $teacher->id, 'Mathematics');

        $this->actingAs($office)
            ->post(route('office.grade-submissions.remind'), [
                'teacher_user_id' => (int) $teacherUser->id,
                'subject_name' => 'Mathematics',
                'term' => 1,
                'school_year' => '2026-2027',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('notifications', 1);
        $notification = $teacherUser->notifications()->first();
        $this->assertEquals('Grade Submission Reminder', $notification->data['title']);
        $this->assertStringContainsString('deadline is approaching', $notification->data['message']);
    }

    public function test_remind_all_sends_notifications_to_pending_teachers_only(): void
    {
        $this->seedSettings();
        $office = $this->makeUser('office_admin', 'office');

        // Pending teacher.
        $pendingUser = $this->makeUser('teacher', 'teacher_pending');
        $pendingTeacher = $this->makeTeacher($pendingUser);
        $pendingStudentUser = $this->makeUser('student', 'student_pending');
        $pendingStudent = $this->makeStudent($pendingStudentUser);
        $this->approveEnrollment((int) $pendingStudent->id, (int) $pendingTeacher->id);
        $this->addSubject((int) $pendingStudent->id, (int) $pendingTeacher->id, 'Math');

        // Submitted teacher.
        $doneUser = $this->makeUser('teacher', 'teacher_done');
        $doneTeacher = $this->makeTeacher($doneUser);
        $doneStudentUser = $this->makeUser('student', 'student_done');
        $doneStudent = $this->makeStudent($doneStudentUser);
        $this->approveEnrollment((int) $doneStudent->id, (int) $doneTeacher->id);
        $doneSubject = $this->addSubject((int) $doneStudent->id, (int) $doneTeacher->id, 'Science');
        $this->addGrade((int) $doneStudent->id, $doneSubject);

        $this->actingAs($office)
            ->post(route('office.grade-submissions.remind-all'), [
                'school_year' => '2026-2027',
                'term' => 1,
                'grade_level' => 0,
                'section' => '',
                'teacher' => 0,
                'subject' => '',
                'status' => '',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('notifications', 1);
        $this->assertEquals(1, $pendingUser->notifications()->count());
        $this->assertEquals(0, $doneUser->notifications()->count());
    }

    public function test_teacher_page_shows_only_own_units(): void
    {
        [$office, $teacherUser, $teacher, $student] = $this->scenario();
        $this->addSubject((int) $student->id, (int) $teacher->id, 'Mathematics');

        $otherTeacherUser = $this->makeUser('teacher', 'teacher_other');
        $otherTeacher = $this->makeTeacher($otherTeacherUser);
        $otherStudentUser = $this->makeUser('student', 'student_other');
        $otherStudent = $this->makeStudent($otherStudentUser);
        $this->approveEnrollment((int) $otherStudent->id, (int) $otherTeacher->id);
        $this->addSubject((int) $otherStudent->id, (int) $otherTeacher->id, 'Science');

        $this->actingAs($teacherUser)
            ->get(route('teacher.grade-submissions'))
            ->assertOk()
            ->assertSee('My Grade Submissions', false)
            ->assertSee('Mathematics')
            ->assertDontSee('Science');
    }

    public function test_office_dashboard_widget_shows_progress(): void
    {
        [$office, $teacherUser, $teacher, $student] = $this->scenario();
        $this->addSubject((int) $student->id, (int) $teacher->id, 'Mathematics');

        $this->actingAs($office)
            ->get(route('office.dashboard'))
            ->assertOk()
            ->assertSee('Grade Submission Progress', false)
            ->assertSee('View Details', false);
    }
}
