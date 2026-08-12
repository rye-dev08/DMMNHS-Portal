<?php

namespace Tests\Feature;

use App\Models\AcademicCalendarEvent;
use App\Models\EnrollmentRequest;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ImportantDatesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportantDatesTest extends TestCase
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

    private function makeTeacher(User $user): Teacher
    {
        return Teacher::create([
            'user_id' => $user->id,
            'advisory_class' => 'Grade 7-A',
            'max_students' => 30,
            'max_subjects' => 8,
            'status' => 'active',
        ]);
    }

    private function makeStudent(User $user): Student
    {
        return Student::create([
            'user_id' => $user->id,
            'grade_level' => 7,
            'status' => 'active',
        ]);
    }

    private function approveEnrollment(int $studentId, int $teacherId): void
    {
        EnrollmentRequest::create([
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'status' => 'approved',
            'date_requested' => now()->subDays(3),
        ]);
    }

    private function makeRequirement(int $teacherId, array $overrides = []): Requirement
    {
        return Requirement::create(array_merge([
            'teacher_id' => $teacherId,
            'title' => 'Requirement '.uniqid(),
            'requirement_type' => Requirement::TYPE_ACADEMIC,
            'description' => 'Test description',
            'due_date' => now()->addDays(5)->toDateString(),
            'submission_required' => true,
            'section' => 'Grade 7-A',
            'school_year' => '2026-2027',
            'term' => 1,
            'status' => Requirement::STATUS_ACTIVE,
        ], $overrides));
    }

    private function makeEvent(array $overrides = []): AcademicCalendarEvent
    {
        return AcademicCalendarEvent::create(array_merge([
            'title' => 'Event '.uniqid(),
            'event_date' => now()->addDays(7)->toDateString(),
            'category' => 'Event',
            'school_year' => '2026-2027',
            'term' => 1,
        ], $overrides));
    }

    private function studentScenario(): array
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'teacher_a');
        $teacher = $this->makeTeacher($teacherUser);
        $studentUser = $this->makeUser('student', 'student_a');
        $student = $this->makeStudent($studentUser);
        $this->approveEnrollment((int) $student->id, (int) $teacher->id);

        return [$teacher, $student, $studentUser];
    }

    public function test_student_dashboard_shows_actionable_requirements_and_events(): void
    {
        [$teacher, $student, $studentUser] = $this->studentScenario();

        $requirement = $this->makeRequirement((int) $teacher->id, [
            'title' => 'Parent Consent Form',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);
        $event = $this->makeEvent([
            'title' => 'School Foundation Day',
            'event_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Important Dates', false)
            ->assertSee('Parent Consent Form')
            ->assertSee('School Foundation Day')
            ->assertSee('Requirement Deadline')
            ->assertSee('School Event', false)
            ->assertSee(route('student.requirements.show', $requirement->id))
            ->assertSee(route('student.calendar', ['year' => now()->addDays(2)->year, 'month' => now()->addDays(2)->month]));
    }

    public function test_student_hides_requirements_with_no_action_required(): void
    {
        [$teacher, $student, $studentUser] = $this->studentScenario();

        $approved = $this->makeRequirement((int) $teacher->id, ['title' => 'Already Approved']);
        RequirementSubmission::create([
            'requirement_id' => $approved->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'status' => RequirementSubmission::STATUS_APPROVED,
        ]);

        $submitted = $this->makeRequirement((int) $teacher->id, ['title' => 'Already Submitted']);
        RequirementSubmission::create([
            'requirement_id' => $submitted->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'status' => RequirementSubmission::STATUS_SUBMITTED,
        ]);

        $underReview = $this->makeRequirement((int) $teacher->id, ['title' => 'Under Review']);
        RequirementSubmission::create([
            'requirement_id' => $underReview->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'status' => RequirementSubmission::STATUS_UNDER_REVIEW,
        ]);

        $html = view('components.important-dates', [
            'items' => app(ImportantDatesService::class)->forUser($studentUser),
            'viewAllUrl' => '',
            'limit' => 5,
        ])->render();

        $this->assertStringNotContainsString('Already Approved', $html);
        $this->assertStringNotContainsString('Already Submitted', $html);
        $this->assertStringNotContainsString('Under Review', $html);
    }

    public function test_student_keeps_requirement_marked_needs_revision(): void
    {
        [$teacher, $student, $studentUser] = $this->studentScenario();

        $revision = $this->makeRequirement((int) $teacher->id, [
            'title' => 'Needs Revision Again',
            'due_date' => now()->addDays(4)->toDateString(),
        ]);
        RequirementSubmission::create([
            'requirement_id' => $revision->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'status' => RequirementSubmission::STATUS_NEEDS_REVISION,
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Needs Revision Again');
    }

    public function test_student_hides_requirements_without_due_date_and_expired_dates(): void
    {
        [$teacher, $student, $studentUser] = $this->studentScenario();

        $this->makeRequirement((int) $teacher->id, [
            'title' => 'No Due Date Set',
            'due_date' => null,
        ]);
        $this->makeRequirement((int) $teacher->id, [
            'title' => 'Already Overdue',
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        $html = view('components.important-dates', [
            'items' => app(ImportantDatesService::class)->forUser($studentUser),
            'viewAllUrl' => '',
            'limit' => 5,
        ])->render();

        $this->assertStringNotContainsString('No Due Date Set', $html);
        $this->assertStringNotContainsString('Already Overdue', $html);
    }

    public function test_student_only_sees_requirements_from_enrolled_teachers(): void
    {
        [$teacher, $student, $studentUser] = $this->studentScenario();

        $otherTeacherUser = $this->makeUser('teacher', 'teacher_b');
        $otherTeacher = $this->makeTeacher($otherTeacherUser);
        $unrelated = $this->makeRequirement((int) $otherTeacher->id, ['title' => 'Unrelated Teacher']);

        $this->actingAs($studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertDontSee('Unrelated Teacher');
    }

    public function test_teacher_sees_own_requirements_but_not_other_teachers(): void
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'teacher_a');
        $teacher = $this->makeTeacher($teacherUser);

        $otherTeacherUser = $this->makeUser('teacher', 'teacher_b');
        $otherTeacher = $this->makeTeacher($otherTeacherUser);

        $own = $this->makeRequirement((int) $teacher->id, ['title' => 'My Own Requirement']);
        $this->makeRequirement((int) $otherTeacher->id, ['title' => 'Colleague Requirement']);

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('My Own Requirement')
            ->assertDontSee('Colleague Requirement');
    }

    public function test_office_admin_dashboard_shows_school_wide_dates(): void
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'teacher_a');
        $teacher = $this->makeTeacher($teacherUser);
        $officeUser = $this->makeUser('office_admin', 'office_a');

        $requirement = $this->makeRequirement((int) $teacher->id, ['title' => 'School Wide Form']);
        $event = $this->makeEvent(['title' => 'Recognition Day']);

        $this->actingAs($officeUser)
            ->get(route('office.dashboard'))
            ->assertOk()
            ->assertSee('School Wide Form')
            ->assertSee('Recognition Day')
            ->assertSee(route('office.requirements.show', $requirement->id));
    }

    public function test_items_are_sorted_by_nearest_date(): void
    {
        [$teacher, $student, $studentUser] = $this->studentScenario();

        $this->makeRequirement((int) $teacher->id, ['title' => 'Far', 'due_date' => now()->addDays(10)->toDateString()]);
        $this->makeRequirement((int) $teacher->id, ['title' => 'Near', 'due_date' => now()->addDays(2)->toDateString()]);
        $this->makeEvent(['title' => 'Middle Event', 'event_date' => now()->addDays(5)->toDateString()]);

        $dates = app(ImportantDatesService::class)
            ->forUser($studentUser)
            ->pluck('date')
            ->map(fn ($date) => $date->toDateString())
            ->values()
            ->all();

        $this->assertSame($dates, collect($dates)->sort()->values()->all());
        $this->assertCount(3, $dates);
    }

    public function test_relative_and_urgency_labels(): void
    {
        [$teacher, $student, $studentUser] = $this->studentScenario();

        $today = $this->makeRequirement((int) $teacher->id, ['title' => 'Due Today', 'due_date' => now()->toDateString()]);
        $tomorrow = $this->makeRequirement((int) $teacher->id, ['title' => 'Due Tomorrow', 'due_date' => now()->addDay()->toDateString()]);
        $later = $this->makeRequirement((int) $teacher->id, ['title' => 'Due Later', 'due_date' => now()->addDays(10)->toDateString()]);

        $items = app(ImportantDatesService::class)->forUser($studentUser);

        $byId = $items->keyBy('title');
        $this->assertSame('Today', $byId['Due Today']->relative);
        $this->assertSame('urgent', $byId['Due Today']->urgency);
        $this->assertSame('Tomorrow', $byId['Due Tomorrow']->relative);
        $this->assertSame('urgent', $byId['Due Tomorrow']->urgency);
        $this->assertSame('normal', $byId['Due Later']->urgency);
    }

    public function test_view_all_page_lists_every_upcoming_date(): void
    {
        [$teacher, $student, $studentUser] = $this->studentScenario();

        for ($i = 1; $i <= 6; $i++) {
            $this->makeRequirement((int) $teacher->id, [
                'title' => "Requirement Number {$i}",
                'due_date' => now()->addDays($i)->toDateString(),
            ]);
        }

        $this->actingAs($studentUser)
            ->get(route('student.important-dates'))
            ->assertOk()
            ->assertSee('Requirement Number 1')
            ->assertSee('Requirement Number 6');
    }

    public function test_view_all_routes_are_role_gated(): void
    {
        [$teacher, $student, $studentUser] = $this->studentScenario();
        $officeUser = $this->makeUser('office_admin', 'office_b');

        $this->actingAs($studentUser)
            ->get(route('teacher.important-dates'))
            ->assertRedirect(route('login'));

        $this->actingAs($studentUser)
            ->get(route('office.important-dates'))
            ->assertRedirect(route('login'));

        $this->actingAs($officeUser)
            ->get(route('student.important-dates'))
            ->assertRedirect(route('login'));
    }

    public function test_empty_state_shows_no_upcoming_dates(): void
    {
        [$teacher, $student, $studentUser] = $this->studentScenario();

        $this->actingAs($studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('No upcoming dates.');
    }
}
