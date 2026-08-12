<?php

namespace Tests\Feature;

use App\Models\AcademicCalendarEvent;
use App\Models\Announcement;
use App\Models\AssessmentScore;
use App\Models\EnrollmentRequest;
use App\Models\Grade;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\PortalNotification;
use App\Services\StudentTimelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentTimelineTest extends TestCase
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

    private function makeStudent(User $user, array $overrides = []): Student
    {
        return Student::create(array_merge([
            'user_id' => $user->id,
            'grade_level' => 7,
            'status' => 'active',
        ], $overrides));
    }

    private function approveEnrollment(int $studentId, int $teacherId, string $schoolYear = '2026-2027'): void
    {
        EnrollmentRequest::create([
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'status' => 'approved',
            'date_requested' => now()->subDays(3),
        ]);
    }

    private function addSubject(int $studentId, int $teacherId, string $name, string $schoolYear = '2026-2027'): int
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
            'event_date' => now()->subDays(2)->toDateString(),
            'category' => 'Event',
            'school_year' => '2026-2027',
            'term' => 1,
        ], $overrides));
    }

    private function makeAnnouncement(array $overrides = []): Announcement
    {
        return Announcement::create(array_merge([
            'title' => 'Announcement '.uniqid(),
            'short_summary' => 'School announcement summary',
            'content' => 'Body',
            'priority' => 'normal',
            'status' => Announcement::STATUS_PUBLISHED,
            'target_role' => 'all',
            'publish_date' => now()->subDay()->toDateString(),
            'school_year' => '2026-2027',
            'term' => 1,
        ], $overrides));
    }

    private function addGrade(int $studentId, int $subjectId, array $overrides = []): void
    {
        Grade::create(array_merge([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'grade' => 90,
            'remarks' => '',
            'quarter' => 'Term 1',
            'date_submitted' => now()->subDay(),
        ], $overrides));
    }

    private function addAssessment(int $studentId, int $teacherId, array $overrides = []): void
    {
        AssessmentScore::create(array_merge([
            'teacher_id' => $teacherId,
            'student_id' => $studentId,
            'score_type' => 'quiz',
            'item_no' => 1,
            'score' => 20,
            'max_score' => 25,
        ], $overrides));
    }

    private function fullStudentScenario(): array
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'teacher_a');
        $teacher = $this->makeTeacher($teacherUser);
        $studentUser = $this->makeUser('student', 'student_a');
        $student = $this->makeStudent($studentUser, [
            'student_id_no' => '2026-0001',
            'id_token_generated_at' => now()->subDays(4),
        ]);
        $this->approveEnrollment((int) $student->id, (int) $teacher->id);

        $subjectId = $this->addSubject((int) $student->id, (int) $teacher->id, 'Math');
        $this->addGrade((int) $student->id, $subjectId);
        $this->addAssessment((int) $student->id, (int) $teacher->id);

        $requirement = $this->makeRequirement((int) $teacher->id, ['title' => 'Research Paper']);
        RequirementSubmission::create([
            'requirement_id' => $requirement->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'status' => RequirementSubmission::STATUS_APPROVED,
            'submitted_at' => now()->subDays(2),
            'reviewed_at' => now()->subDay(),
        ]);

        $studentUser->notify(new PortalNotification([
            'title' => 'All Grades Complete',
            'message' => 'All your grades for Term 1 (2026-2027) have been submitted.',
        ]));
        $studentUser->notify(new PortalNotification([
            'title' => 'Enrollment Approved',
            'message' => 'Your enrollment request to Teacher A has been approved for 2026-2027.',
        ]));

        $this->makeEvent(['title' => 'School Foundation Day']);
        $this->makeAnnouncement(['title' => 'Welcome Back Notice']);

        return [$teacher, $student, $studentUser];
    }

    public function test_student_timeline_renders_events_across_sources(): void
    {
        [, , $studentUser] = $this->fullStudentScenario();

        $this->actingAs($studentUser)
            ->get(route('student.timeline'))
            ->assertOk()
            ->assertSee('My Academic Journey', false)
            ->assertSee('Student Account Activated')
            ->assertSee('Digital Student ID Generated')
            ->assertSee('Enrollment Submitted')
            ->assertSee('Enrollment Approved')
            ->assertSee('Teacher Assigned')
            ->assertSee('Requirement Assigned')
            ->assertSee('Requirement Submitted')
            ->assertSee('Requirement Approved')
            ->assertSee('Grade Released')
            ->assertSee('Quiz Completed')
            ->assertSee('Semester Completed')
            ->assertSee('School Foundation Day')
            ->assertSee('New Announcement')
            ->assertSee(route('student.grades'))
            ->assertSee(route('student.enrollment'));
    }

    public function test_timeline_is_newest_first(): void
    {
        [, , $studentUser] = $this->fullStudentScenario();

        $events = app(StudentTimelineService::class)->forUser($studentUser);

        $timestamps = $events->map(fn ($event) => $event->at->timestamp)->all();
        $this->assertSame($timestamps, collect($timestamps)->sortDesc()->values()->all());
    }

    public function test_timeline_route_is_role_gated(): void
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'teacher_a');
        $this->makeTeacher($teacherUser);
        $officeUser = $this->makeUser('office_admin', 'office_a');
        $adminUser = $this->makeUser('system_admin', 'admin_a');

        foreach ([$teacherUser, $officeUser, $adminUser] as $user) {
            $this->actingAs($user)
                ->get(route('student.timeline'))
                ->assertRedirect(route('login'));
        }
    }

    public function test_empty_state_when_student_has_no_history(): void
    {
        $this->seedSettings();
        $studentUser = $this->makeUser('student', 'newbie');

        $this->actingAs($studentUser)
            ->get(route('student.timeline'))
            ->assertOk()
            ->assertSee('My academic journey has not started yet.');

        $this->assertTrue(app(StudentTimelineService::class)->forUser($studentUser)->isEmpty());
    }

    public function test_search_filters_timeline_events(): void
    {
        [, , $studentUser] = $this->fullStudentScenario();

        $this->actingAs($studentUser)
            ->get(route('student.timeline', ['q' => 'math']))
            ->assertOk()
            ->assertSee('Grade Released')
            ->assertSee('Math')
            ->assertDontSee('data-timeline-id="enrollment_submitted-')
            ->assertDontSee('data-timeline-id="requirement_assigned-');
    }

    public function test_category_filter_limits_events(): void
    {
        [, , $studentUser] = $this->fullStudentScenario();

        $this->actingAs($studentUser)
            ->get(route('student.timeline', ['category' => 'Grades']))
            ->assertOk()
            ->assertSee('Grade Released')
            ->assertDontSee('data-timeline-id="enrollment_submitted-')
            ->assertDontSee('data-timeline-id="requirement_assigned-');
    }

    public function test_school_year_and_term_filters_work(): void
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'teacher_a');
        $teacher = $this->makeTeacher($teacherUser);
        $studentUser = $this->makeUser('student', 'student_a');
        $student = $this->makeStudent($studentUser);
        $this->approveEnrollment((int) $student->id, (int) $teacher->id);

        $current = $this->makeRequirement((int) $teacher->id, [
            'title' => 'Current Year Item',
            'school_year' => '2026-2027',
            'term' => 1,
        ]);
        $previous = $this->makeRequirement((int) $teacher->id, [
            'title' => 'Previous Year Item',
            'school_year' => '2025-2026',
            'term' => 2,
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.timeline', ['school_year' => '2025-2026']))
            ->assertOk()
            ->assertSee('Previous Year Item')
            ->assertDontSee('Current Year Item');

        $this->actingAs($studentUser)
            ->get(route('student.timeline', ['term' => 2, 'school_year' => '2025-2026']))
            ->assertOk()
            ->assertSee('Previous Year Item')
            ->assertDontSee(route('student.requirements.show', $current->id));

        $this->actingAs($studentUser)
            ->get(route('student.timeline', ['school_year' => '2026-2027', 'term' => 1]))
            ->assertOk()
            ->assertSee('Current Year Item');
    }

    public function test_filter_options_expose_distinct_years_terms_and_categories(): void
    {
        [, , $studentUser] = $this->fullStudentScenario();

        $options = app(StudentTimelineService::class)->filterOptions(
            app(StudentTimelineService::class)->forUser($studentUser)
        );

        $this->assertContains('2026-2027', $options->schoolYears->all());
        $this->assertContains(1, $options->terms->all());
        $this->assertContains('Grades', $options->categories);
        $this->assertContains('Enrollment', $options->categories);
        $this->assertContains('Documents', $options->categories);
    }

    public function test_student_only_sees_own_timeline_data(): void
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'teacher_a');
        $teacher = $this->makeTeacher($teacherUser);
        $studentUser = $this->makeUser('student', 'student_a');
        $student = $this->makeStudent($studentUser);
        $this->approveEnrollment((int) $student->id, (int) $teacher->id);

        $this->makeRequirement((int) $teacher->id, ['title' => 'My Own Requirement']);

        $otherTeacherUser = $this->makeUser('teacher', 'teacher_b');
        $otherTeacher = $this->makeTeacher($otherTeacherUser);
        $otherUser = $this->makeUser('student', 'student_b');
        $other = $this->makeStudent($otherUser, ['student_id_no' => '2026-0002']);
        $this->approveEnrollment((int) $other->id, (int) $otherTeacher->id);
        $this->makeRequirement((int) $otherTeacher->id, ['title' => 'Other Student Requirement']);

        $this->actingAs($studentUser)
            ->get(route('student.timeline'))
            ->assertOk()
            ->assertSee('My Own Requirement')
            ->assertDontSee('Other Student Requirement');

        $this->assertSame(
            0,
            app(StudentTimelineService::class)
                ->forUser($studentUser)
                ->where('type', 'requirement_assigned')
                ->filter(fn ($event) => str_contains($event->detail, 'Other Student Requirement'))
                ->count()
        );
    }

    public function test_dashboard_shows_recent_academic_activity_widget(): void
    {
        [, , $studentUser] = $this->fullStudentScenario();

        $this->actingAs($studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Recent Academic Activity', false)
            ->assertSee('View Full Timeline', false)
            ->assertSee('Student Account Activated')
            ->assertSee('Enrollment Approved')
            ->assertSee(route('student.timeline'));
    }

    public function test_recent_limits_to_five_events(): void
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'teacher_a');
        $teacher = $this->makeTeacher($teacherUser);
        $studentUser = $this->makeUser('student', 'student_a');
        $student = $this->makeStudent($studentUser);
        $this->approveEnrollment((int) $student->id, (int) $teacher->id);

        foreach ([1, 2, 3, 4, 5, 6] as $i) {
            $subject = Subject::create([
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'subject_name' => "Subject {$i}",
                'course_code' => '',
                'teacher_code' => '',
                'room_no' => '',
            ]);
            DB::table('subjects')
                ->where('id', $subject->id)
                ->update(['created_at' => now()->subMinutes(6 - $i)]);
        }
        DB::table('users')
            ->where('id', $studentUser->id)
            ->update(['created_at' => now()->subDay()]);
        $studentUser->refresh();

        $recent = app(StudentTimelineService::class)->recent($studentUser, 5);
        $this->assertLessThanOrEqual(5, $recent->count());
        $this->assertSame(
            'teacher_assigned',
            $recent->first()->type
        );
        $this->assertSame(
            6,
            app(StudentTimelineService::class)->forUser($studentUser)->where('type', 'teacher_assigned')->count()
        );
    }

    public function test_relative_label_renders_human_reading(): void
    {
        $this->assertSame(
            'Today',
            StudentTimelineService::relativeLabel(now()->toImmutable())
        );

        $this->assertStringContainsString(
            'days ago',
            StudentTimelineService::relativeLabel(now()->subDays(3)->toImmutable())
        );

        $this->assertSame(
            now()->subYear()->toImmutable()->format('M d, Y'),
            StudentTimelineService::relativeLabel(now()->subYear()->toImmutable())
        );
    }
}
