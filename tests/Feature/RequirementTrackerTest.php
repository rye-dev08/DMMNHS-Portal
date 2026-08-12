<?php

namespace Tests\Feature;

use App\Models\EnrollmentRequest;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RequirementTrackerTest extends TestCase
{
    use RefreshDatabase;

    private static int $counter = 0;

    private function seedSettings(): void
    {
        Setting::updateOrCreate(['id' => 1], [
            'current_term' => 2,
            'current_school_year' => '2026-2027',
            'max_students_per_class' => 30,
            'max_subjects_per_teacher' => 8,
            'enrollment_phase' => 'none',
        ]);
    }

    private function makeStudent(): User
    {
        self::$counter++;
        $user = User::create([
            'name' => 'Wowowin '.self::$counter,
            'username' => 'student.requirement'.self::$counter,
            'email' => 'student'.self::$counter.'@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'student',
            'status' => 'active',
        ]);

        Student::create([
            'user_id' => $user->id,
            'grade_level' => 11,
            'status' => 'active',
        ]);

        return $user;
    }

    private function makeTeacher(): User
    {
        self::$counter++;
        $user = User::create([
            'name' => 'Mr. Advisor '.self::$counter,
            'username' => 'teacher.requirement'.self::$counter,
            'email' => 'teacher'.self::$counter.'@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        Teacher::create([
            'user_id' => $user->id,
            'advisory_class' => 'Grade 11-Rizal (Academic)',
            'max_students' => 30,
            'max_subjects' => 8,
            'status' => 'active',
        ]);

        return $user;
    }

    private function enroll(int $studentId, int $teacherId, string $status = 'approved'): void
    {
        EnrollmentRequest::create([
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'status' => $status,
            'date_requested' => now(),
        ]);
    }

    private function teacherIdOf(User $teacher): int
    {
        return (int) DB::table('teachers')->where('user_id', $teacher->id)->value('id');
    }

    private function studentIdOf(User $student): int
    {
        return (int) DB::table('students')->where('user_id', $student->id)->value('id');
    }

    public function test_assigned_student_sees_requirement_unassigned_does_not(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);
        $assigned = $this->makeStudent();
        $unassigned = $this->makeStudent();
        $this->enroll($this->studentIdOf($assigned), $teacherId);

        $this->actingAs($teacher)
            ->post(route('teacher.requirements.store'), [
                'title' => 'Submit Form 137',
                'requirement_type' => 'school_form',
                'description' => 'Bring your signed Form 137.',
                'submission_required' => 1,
            ])
            ->assertRedirect(route('teacher.requirements.show', Requirement::first()->id));

        $this->actingAs($assigned)
            ->get(route('student.requirements'))
            ->assertOk()
            ->assertSee('Submit Form 137', false);

        $this->actingAs($unassigned)
            ->get(route('student.requirements'))
            ->assertOk()
            ->assertDontSee('Submit Form 137', false);
    }

    public function test_unassigned_student_cannot_open_requirement(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);
        $assigned = $this->makeStudent();
        $unassigned = $this->makeStudent();
        $this->enroll($this->studentIdOf($assigned), $teacherId);

        $requirement = Requirement::create([
            'teacher_id' => $teacherId,
            'title' => 'Confidential',
            'requirement_type' => 'other',
            'description' => 'Hidden',
            'section' => 'Grade 11-Rizal (Academic)',
            'school_year' => '2026-2027',
            'term' => 2,
            'status' => Requirement::STATUS_ACTIVE,
        ]);

        $this->actingAs($assigned)->get(route('student.requirements.show', $requirement->id))->assertOk();

        $this->actingAs($unassigned)->get(route('student.requirements.show', $requirement->id))->assertForbidden();
    }

    public function test_create_notifies_only_approved_students(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);

        $approved = $this->makeStudent();
        $pending = $this->makeStudent();
        $none = $this->makeStudent();
        $this->enroll($this->studentIdOf($approved), $teacherId);
        $this->enroll($this->studentIdOf($pending), $teacherId, 'pending');

        $this->actingAs($teacher)
            ->post(route('teacher.requirements.store'), [
                'title' => 'Birth Certificate',
                'requirement_type' => 'legal_document',
                'description' => 'Submit a photocopy.',
                'due_date' => now()->addDays(5)->format('Y-m-d'),
                'submission_required' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('requirements', 1);
        $requirement = Requirement::first();
        $this->assertSame($teacherId, (int) $requirement->teacher_id);
        $this->assertSame('2026-2027', $requirement->school_year);
        $this->assertSame(2, (int) $requirement->term);
        $this->assertSame('Grade 11-Rizal (Academic)', $requirement->section);

        // Only the approved student receives the "New Requirement" notification.
        $this->assertSame(1, $approved->notifications()->count());
        $this->assertSame(0, $pending->notifications()->count());
        $this->assertSame(0, $none->notifications()->count());

        $data = $approved->notifications()->first()->data;
        $this->assertSame('New Requirement', $data['title']);
        $this->assertSame(route('student.requirements.show', $requirement->id), $data['link']);
    }

    public function test_student_can_submit_text_and_file(): void
    {
        Storage::fake('public');
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);
        $student = $this->makeStudent();
        $studentId = $this->studentIdOf($student);
        $this->enroll($studentId, $teacherId);

        $requirement = Requirement::create([
            'teacher_id' => $teacherId,
            'title' => 'Research Paper',
            'requirement_type' => 'academic',
            'description' => 'Submit your research paper.',
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'submission_required' => 1,
            'section' => 'Grade 11-Rizal (Academic)',
            'school_year' => '2026-2027',
            'term' => 2,
            'status' => Requirement::STATUS_ACTIVE,
        ]);

        $this->actingAs($student)
            ->post(route('student.requirements.submit', $requirement->id), [
                'response_text' => 'Here is my research paper.',
                'attachment' => UploadedFile::fake()->create('paper.pdf', 100),
            ])
            ->assertRedirect(route('student.requirements.show', $requirement->id));

        $this->assertDatabaseCount('requirement_submissions', 1);
        $submission = RequirementSubmission::first();
        $this->assertSame($requirement->id, (int) $submission->requirement_id);
        $this->assertSame($studentId, (int) $submission->student_id);
        $this->assertSame('submitted', $submission->status);
        $this->assertSame('Here is my research paper.', $submission->response_text);
        $this->assertNotNull($submission->attachment);
        $this->assertSame('paper.pdf', $submission->attachment_name);
        $this->assertNotNull($submission->submitted_at);
        Storage::disk('public')->assertExists($submission->attachment);
    }

    public function test_submit_requires_text_or_file(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);
        $student = $this->makeStudent();
        $this->enroll($this->studentIdOf($student), $teacherId);

        $requirement = Requirement::create([
            'teacher_id' => $teacherId,
            'title' => 'Empty Submission',
            'requirement_type' => 'other',
            'description' => 'Nothing.',
            'submission_required' => 1,
            'school_year' => '2026-2027',
            'term' => 2,
            'status' => Requirement::STATUS_ACTIVE,
        ]);

        $this->actingAs($student)
            ->from(route('student.requirements.show', $requirement->id))
            ->post(route('student.requirements.submit', $requirement->id), [])
            ->assertRedirect(route('student.requirements.show', $requirement->id))
            ->assertSessionHasErrors('response_text');

        $this->assertDatabaseCount('requirement_submissions', 0);
    }

    public function test_teacher_can_approve_submission_and_notifies_student(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);
        $student = $this->makeStudent();
        $studentId = $this->studentIdOf($student);
        $this->enroll($studentId, $teacherId);

        $requirement = Requirement::create([
            'teacher_id' => $teacherId,
            'title' => 'Project Proposal',
            'requirement_type' => 'project',
            'description' => 'Submit your proposal.',
            'submission_required' => 1,
            'school_year' => '2026-2027',
            'term' => 2,
            'status' => Requirement::STATUS_ACTIVE,
        ]);

        $submission = RequirementSubmission::create([
            'requirement_id' => $requirement->id,
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'status' => RequirementSubmission::STATUS_SUBMITTED,
            'response_text' => 'My proposal',
            'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.submissions.approve', $submission->id))
            ->assertRedirect();

        $submission->refresh();
        $this->assertSame('approved', $submission->status);
        $this->assertNotNull($submission->reviewed_at);

        $this->assertSame(1, $student->notifications()->count());
        $data = $student->notifications()->first()->data;
        $this->assertSame('Submission Approved', $data['title']);
        $this->assertSame(route('student.requirements.show', $requirement->id), $data['link']);
    }

    public function test_teacher_can_request_revision_with_feedback_and_notifies_student(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);
        $student = $this->makeStudent();
        $studentId = $this->studentIdOf($student);
        $this->enroll($studentId, $teacherId);

        $requirement = Requirement::create([
            'teacher_id' => $teacherId,
            'title' => 'Essay',
            'requirement_type' => 'academic',
            'description' => 'Write an essay.',
            'submission_required' => 1,
            'school_year' => '2026-2027',
            'term' => 2,
            'status' => Requirement::STATUS_ACTIVE,
        ]);

        $submission = RequirementSubmission::create([
            'requirement_id' => $requirement->id,
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'status' => RequirementSubmission::STATUS_SUBMITTED,
            'response_text' => 'Draft essay',
            'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.submissions.revision', $submission->id), [
                'feedback' => 'Please expand your conclusion.',
            ])
            ->assertRedirect();

        $submission->refresh();
        $this->assertSame('needs_revision', $submission->status);
        $this->assertSame('Please expand your conclusion.', $submission->feedback);
        $this->assertNotNull($submission->reviewed_at);

        $this->assertSame(1, $student->notifications()->count());
        $data = $student->notifications()->first()->data;
        $this->assertSame('Submission Needs Revision', $data['title']);
    }

    public function test_student_resubmission_after_revision_is_resubmitted(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);
        $student = $this->makeStudent();
        $studentId = $this->studentIdOf($student);
        $this->enroll($studentId, $teacherId);

        $requirement = Requirement::create([
            'teacher_id' => $teacherId,
            'title' => 'Portfolio',
            'requirement_type' => 'activity',
            'description' => 'Submit your portfolio.',
            'submission_required' => 1,
            'school_year' => '2026-2027',
            'term' => 2,
            'status' => Requirement::STATUS_ACTIVE,
        ]);

        $submission = RequirementSubmission::create([
            'requirement_id' => $requirement->id,
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'status' => RequirementSubmission::STATUS_NEEDS_REVISION,
            'response_text' => 'First version',
            'feedback' => 'Needs work',
            'submitted_at' => now()->subDay(),
            'reviewed_at' => now()->subDay(),
        ]);

        $this->actingAs($student)
            ->post(route('student.requirements.submit', $requirement->id), [
                'response_text' => 'Revised version',
            ])
            ->assertRedirect(route('student.requirements.show', $requirement->id));

        $submission->refresh();
        $this->assertSame('resubmitted', $submission->status);
        $this->assertSame('Revised version', $submission->response_text);
        $this->assertNull($submission->feedback);
        $this->assertNull($submission->reviewed_at);
        $this->assertNotNull($submission->submitted_at);
    }

    public function test_bump_all_respects_24h_cooldown(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);
        $studentA = $this->makeStudent();
        $studentB = $this->makeStudent();
        $this->enroll($this->studentIdOf($studentA), $teacherId);
        $this->enroll($this->studentIdOf($studentB), $teacherId);

        $requirement = Requirement::create([
            'teacher_id' => $teacherId,
            'title' => 'Library Card',
            'requirement_type' => 'school_form',
            'description' => 'Submit your library card application.',
            'submission_required' => 1,
            'school_year' => '2026-2027',
            'term' => 2,
            'status' => Requirement::STATUS_ACTIVE,
        ]);

        // First bump works: both students reminded.
        $this->actingAs($teacher)
            ->post(route('teacher.requirements.bump', $requirement->id))
            ->assertRedirect();

        $requirement->refresh();
        $this->assertSame(1, (int) $requirement->bump_count);
        $this->assertNotNull($requirement->last_bumped_at);
        $this->assertSame($teacherId, (int) $requirement->last_bumped_by);
        $this->assertSame(2, $studentA->notifications()->count() + $studentB->notifications()->count());

        // Second bump immediately is blocked by the cooldown.
        $this->actingAs($teacher)
            ->post(route('teacher.requirements.bump', $requirement->id))
            ->assertRedirect()
            ->assertSessionHas('flash_notice');

        $requirement->refresh();
        $this->assertSame(1, (int) $requirement->bump_count);
        $this->assertSame(2, $studentA->notifications()->count() + $studentB->notifications()->count());
    }

    public function test_single_student_remind_sends_notification(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);
        $student = $this->makeStudent();
        $studentId = $this->studentIdOf($student);
        $this->enroll($studentId, $teacherId);

        $requirement = Requirement::create([
            'teacher_id' => $teacherId,
            'title' => 'Clearance',
            'requirement_type' => 'other',
            'description' => 'Submit your clearance.',
            'submission_required' => 1,
            'school_year' => '2026-2027',
            'term' => 2,
            'status' => Requirement::STATUS_ACTIVE,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.requirements.remind', [$requirement->id, $studentId]))
            ->assertRedirect();

        $this->assertSame(1, $student->notifications()->count());
        $data = $student->notifications()->first()->data;
        $this->assertSame('Requirement Reminder', $data['title']);

        $requirement->refresh();
        $this->assertSame(1, (int) $requirement->bump_count);
    }

    public function test_due_reminder_is_sent_once_while_unread(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);
        $student = $this->makeStudent();
        $studentId = $this->studentIdOf($student);
        $this->enroll($studentId, $teacherId);

        $requirement = Requirement::create([
            'teacher_id' => $teacherId,
            'title' => 'Overdue Paper',
            'requirement_type' => 'academic',
            'description' => 'This one is overdue.',
            'due_date' => now()->subDay()->format('Y-m-d'),
            'submission_required' => 1,
            'school_year' => '2026-2027',
            'term' => 2,
            'status' => Requirement::STATUS_ACTIVE,
        ]);

        $this->actingAs($student)->get(route('student.requirements'))->assertOk();
        $this->assertSame(1, $student->notifications()->count());
        $this->assertSame('Requirement Overdue', $student->notifications()->first()->data['title']);

        // Visiting again must not re-send the same reminder while unread.
        $this->actingAs($student)->get(route('student.requirements'))->assertOk();
        $this->assertSame(1, $student->notifications()->count());

        // A different reminder title (Due Soon) is a distinct notification and dedupes independently.
        $requirement->update(['due_date' => now()->addDay()->format('Y-m-d')]);
        $this->actingAs($student)->get(route('student.requirements'))->assertOk();
        $this->assertSame(2, $student->notifications()->count());
        $this->assertTrue($student->notifications()->where('data', 'like', '%"title":"Requirement Due Soon"%')->exists());

        // Still due soon -> the Due Soon reminder is not re-sent while unread.
        $this->actingAs($student)->get(route('student.requirements'))->assertOk();
        $this->assertSame(2, $student->notifications()->count());
    }

    public function test_no_due_date_requirement_shows_no_due_date(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();
        $teacherId = $this->teacherIdOf($teacher);
        $student = $this->makeStudent();
        $studentId = $this->studentIdOf($student);
        $this->enroll($studentId, $teacherId);

        $requirement = Requirement::create([
            'teacher_id' => $teacherId,
            'title' => 'Annual Medical Checkup',
            'requirement_type' => 'other',
            'description' => 'Get your annual checkup.',
            'submission_required' => 0,
            'school_year' => '2026-2027',
            'term' => 2,
            'status' => Requirement::STATUS_ACTIVE,
        ]);

        $this->actingAs($student)
            ->get(route('student.requirements.show', $requirement->id))
            ->assertOk()
            ->assertSee('No due date', false)
            ->assertSee('informational requirement', false);
    }

    public function test_guest_and_wrong_role_are_redirected(): void
    {
        $this->seedSettings();
        $student = $this->makeStudent();
        $teacher = $this->makeTeacher();

        $this->get(route('student.requirements'))->assertRedirect(route('login'));
        $this->get(route('teacher.requirements'))->assertRedirect(route('login'));

        // Student hitting a teacher route redirects to login (legacy behaviour).
        $this->actingAs($student)->get(route('teacher.requirements'))->assertRedirect(route('login'));

        // Teacher hitting a student route redirects to login.
        $this->actingAs($teacher)->get(route('student.requirements'))->assertRedirect(route('login'));
    }
}
