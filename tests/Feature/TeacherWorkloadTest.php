<?php

namespace Tests\Feature;

use App\Models\EnrollmentRequest;
use App\Models\GradeSubmissionDeadline;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TeacherWorkloadTest extends TestCase
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
            'date_requested' => now()->subDays(3),
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

    private function addTeacherSubject(int $teacherId, string $name): void
    {
        TeacherSubject::create([
            'teacher_id' => $teacherId,
            'subject_name' => $name,
            'course_code' => '',
            'teacher_code' => '',
            'room_no' => '',
        ]);
    }

    private function addGrade(int $studentId, int $subjectId, string $quarter = 'Term 1'): void
    {
        DB::table('grades')->insert([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'grade' => '92',
            'remarks' => 'Outstanding',
            'quarter' => $quarter,
            'date_submitted' => now()->subDays(2)->toDateTimeString(),
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

    /**
     * @return array{0: User, 1: Teacher, 2: Student}
     */
    private function teacherScenario(): array
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'teacher_a');
        $teacher = $this->makeTeacher($teacherUser);
        $studentUser = $this->makeUser('student', 'student_a');
        $student = $this->makeStudent($studentUser);
        $this->approveEnrollment((int) $student->id, (int) $teacher->id);
        $this->addTeacherSubject((int) $teacher->id, 'Mathematics');

        return [$teacherUser, $teacher, $student];
    }

    public function test_teacher_workload_dashboard_renders(): void
    {
        [$teacherUser, $teacher, $student] = $this->teacherScenario();
        $this->addSubject((int) $student->id, (int) $teacher->id, 'Mathematics');

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Today\'s Workload')
            ->assertSee('Upcoming Deadlines', false)
            ->assertSee('Pending Requirements', false)
            ->assertSee('Grade Submission Progress', false)
            ->assertSee('Class Summary', false)
            ->assertSee('Recent Activity', false)
            ->assertSee('Quick Actions', false)
            ->assertSee('Mathematics');
    }

    public function test_non_teacher_roles_are_blocked_from_teacher_dashboard(): void
    {
        $this->seedSettings();
        $studentUser = $this->makeUser('student', 'student_only');
        $this->makeStudent($studentUser);

        $this->actingAs($studentUser)
            ->get(route('teacher.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_summary_cards_reflect_teacher_data(): void
    {
        [$teacherUser, $teacher, $student] = $this->teacherScenario();
        $this->addSubject((int) $student->id, (int) $teacher->id, 'Mathematics');

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Students', false)
            ->assertSee('1')
            ->assertSee('Subjects Handled', false)
            ->assertSee('Subjects currently taught', false);
    }

    public function test_pending_grade_submission_appears_in_workload(): void
    {
        [$teacherUser, $teacher, $student] = $this->teacherScenario();
        $subjectId = $this->addSubject((int) $student->id, (int) $teacher->id, 'Science');

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Grade remaining: 1 student(s)', false)
            ->assertSee('Pending Grade Submissions', false);

        $this->addGrade((int) $student->id, $subjectId);

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertDontSee('Grade remaining: 1 student(s)', false);
    }

    public function test_pending_requirement_shows_progress(): void
    {
        [$teacherUser, $teacher, $student] = $this->teacherScenario();
        $requirement = $this->makeRequirement((int) $teacher->id, ['title' => 'Science Activity']);

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Science Activity')
            ->assertSee('View Submissions →', false)
            ->assertSee('Remaining: 1', false);

        RequirementSubmission::create([
            'requirement_id' => $requirement->id,
            'student_id' => (int) $student->id,
            'teacher_id' => (int) $teacher->id,
            'status' => RequirementSubmission::STATUS_SUBMITTED,
            'submitted_at' => now()->subDay(),
        ]);

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Review 1 submission(s)', false);
    }

    public function test_upcoming_deadline_appears(): void
    {
        [$teacherUser, $teacher] = $this->teacherScenario();
        $this->makeRequirement((int) $teacher->id, [
            'title' => 'Research Proposal Review',
            'due_date' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Research Proposal Review')
            ->assertSee('Tomorrow');
    }

    public function test_grade_submission_deadline_appears_in_upcoming(): void
    {
        [$teacherUser, $teacher, $student] = $this->teacherScenario();
        $this->addSubject((int) $student->id, (int) $teacher->id, 'Mathematics');

        GradeSubmissionDeadline::create([
            'school_year' => '2026-2027',
            'term' => 1,
            'subject_name' => '',
            'deadline' => now()->addDays(3)->toDateString(),
        ]);

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Grade Submission')
            ->assertSee('In 3 days');
    }

    public function test_empty_states_render(): void
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'teacher_empty');
        $this->makeTeacher($teacherUser);

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('No pending workload.', false)
            ->assertSee('No upcoming deadlines.', false)
            ->assertSee('No pending requirements.', false)
            ->assertSee('No recent activity.', false);
    }

    public function test_class_summary_shows_average_and_students(): void
    {
        [$teacherUser, $teacher, $student] = $this->teacherScenario();
        $subjectId = $this->addSubject((int) $student->id, (int) $teacher->id, 'Mathematics');
        $this->addGrade((int) $student->id, $subjectId);

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Grade 7-A', false)
            ->assertSee('92.0', false)
            ->assertSee('Class Summary', false);
    }

    public function test_recent_activity_shows_grades_submitted(): void
    {
        [$teacherUser, $teacher, $student] = $this->teacherScenario();
        $this->addTeacherSubject((int) $teacher->id, 'English');
        $subjectId = $this->addSubject((int) $student->id, (int) $teacher->id, 'English');
        $this->addGrade((int) $student->id, $subjectId);

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Grades submitted', false)
            ->assertSee('Updated English', false);
    }

    public function test_quick_actions_link_to_existing_pages(): void
    {
        [$teacherUser] = $this->teacherScenario();

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Submit Grades', false)
            ->assertSee('Review Requirements', false)
            ->assertSee('Open Messages', false)
            ->assertSee('Academic Calendar', false)
            ->assertSee(route('teacher.submit-grades'))
            ->assertSee(route('teacher.requirements'))
            ->assertSee(route('teacher.calendar'));
    }

    public function test_only_own_data_is_aggregated(): void
    {
        [$teacherUser, $teacher, $student] = $this->teacherScenario();
        $this->addSubject((int) $student->id, (int) $teacher->id, 'Mathematics');

        $otherUser = $this->makeUser('teacher', 'teacher_other');
        $other = $this->makeTeacher($otherUser);
        $otherStudentUser = $this->makeUser('student', 'student_other');
        $otherStudent = $this->makeStudent($otherStudentUser);
        $this->approveEnrollment((int) $otherStudent->id, (int) $other->id);
        $this->addSubject((int) $otherStudent->id, (int) $other->id, 'Filipino');
        $this->makeRequirement((int) $other->id, ['title' => 'Colleague Private Requirement']);

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Mathematics')
            ->assertDontSee('Filipino')
            ->assertDontSee('Colleague Private Requirement');
    }

    public function test_teacher_without_profile_gets_empty_dashboard(): void
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'teacher_noprofile');

        $this->actingAs($teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('No pending workload.', false)
            ->assertSee('No classes assigned yet.', false);
    }
}
