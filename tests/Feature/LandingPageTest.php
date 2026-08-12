<?php

namespace Tests\Feature;

use App\Models\AcademicCalendarEvent;
use App\Models\Announcement;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    private function seedSettings(string $year = '2026-2027', int $term = 1, string $phase = 'none'): void
    {
        Setting::create([
            'id' => 1,
            'current_term' => $term,
            'current_school_year' => $year,
            'max_students_per_class' => 30,
            'max_subjects_per_teacher' => 8,
            'enrollment_phase' => $phase,
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

    private function makeTeacher(User $user): void
    {
        Teacher::create([
            'user_id' => $user->id,
            'advisory_class' => 'Grade 7-A',
            'max_students' => 30,
            'max_subjects' => 8,
            'status' => 'active',
        ]);
    }

    private function makeStudent(User $user, int $grade = 7): void
    {
        Student::create([
            'user_id' => $user->id,
            'grade_level' => $grade,
            'status' => 'active',
        ]);
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

    public function test_landing_page_renders_as_public_homepage(): void
    {
        $this->seedSettings();

        $this->get('/')
            ->assertOk()
            ->assertSee('Don Mariano Marcos National High School')
            ->assertSee('Student Information')
            ->assertSee('Grade Management Portal')
            ->assertSee('Access Portal')
            ->assertSee('Learn More')
            ->assertSee('Why this Portal?')
            ->assertSee('Academic Calendar')
            ->assertSee('Digital Student ID')
            ->assertSee('Requirement & Submission Tracker')
            ->assertSee('Announcements')
            ->assertSee('Messaging')
            ->assertSee('Grades')
            ->assertSee('Teacher Workload Dashboard')
            ->assertSee('Important Dates')
            ->assertSee('Student')
            ->assertSee('Teacher')
            ->assertSee('Office Administrator')
            ->assertSee('System Administrator')
            ->assertSee('System Status')
            ->assertSee('Online')
            ->assertSee('The Portal in Numbers')
            ->assertSee('Get in Touch')
            ->assertSee('Privacy Policy')
            ->assertSee('Terms of Use');
    }

    public function test_landing_page_navigates_to_login_page(): void
    {
        $this->seedSettings();

        $this->get('/')
            ->assertSee(route('login'), false);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Login to Your Account', false)
            ->assertSee('Email or Student ID')
            ->assertSee(route('home'), false)
            ->assertSee('Back to Home');
    }

    public function test_login_is_the_only_route_used_for_authentication_redirects(): void
    {
        $this->seedSettings();
        $user = $this->makeUser('student', 'student_landing');
        $this->makeStudent($user);

        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->assertSame('login', app('router')->getRoutes()->getByName('login')->uri);
        $this->assertSame('/', app('router')->getRoutes()->getByName('home')->uri);
    }

    public function test_landing_page_shows_system_status_from_settings(): void
    {
        $this->seedSettings('2026-2027', 1, 'enrollment');

        $this->get('/')
            ->assertOk()
            ->assertSee('2026-2027')
            ->assertSee('Term 1')
            ->assertSee('Open for Enrollment');
    }

    public function test_landing_page_surfaces_latest_announcements(): void
    {
        $this->seedSettings();
        $this->makeAnnouncement(['title' => 'Landing Announcement One', 'priority' => 'urgent']);
        $this->makeAnnouncement(['title' => 'Landing Announcement Two']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Landing Announcement One')
            ->assertSee('Landing Announcement Two')
            ->assertSee('Urgent');
    }

    public function test_landing_page_ignores_unpublished_and_expired_announcements(): void
    {
        $this->seedSettings();
        $this->makeAnnouncement(['title' => 'Hidden Draft', 'status' => 'unpublished']);
        $this->makeAnnouncement(['title' => 'Hidden Expired', 'expiration_date' => now()->subDay()->toDateString()]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Hidden Draft')
            ->assertDontSee('Hidden Expired');
    }

    public function test_landing_page_surfaces_upcoming_events(): void
    {
        $this->seedSettings();
        AcademicCalendarEvent::create([
            'title' => 'Foundation Day',
            'event_date' => now()->addDays(5)->toDateString(),
            'category' => 'Event',
            'school_year' => '2026-2027',
            'term' => 1,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Foundation Day')
            ->assertSee('School Event');
    }

    public function test_landing_page_shows_statistics_from_live_data(): void
    {
        $this->seedSettings();
        $studentUser = $this->makeUser('student', 'stats_student');
        $this->makeStudent($studentUser);
        $teacherUser = $this->makeUser('teacher', 'stats_teacher');
        $this->makeTeacher($teacherUser);

        Subject::create([
            'student_id' => Student::where('user_id', $studentUser->id)->value('id'),
            'teacher_id' => Teacher::where('user_id', $teacherUser->id)->value('id'),
            'subject_name' => 'Mathematics',
            'course_code' => '',
            'teacher_code' => '',
            'room_no' => '',
        ]);

        $this->makeAnnouncement();

        $this->get('/')
            ->assertOk()
            ->assertSee('Students')
            ->assertSee('Teachers')
            ->assertSee('Academic Programs')
            ->assertSee('Announcements')
            ->assertSee('Requirements Processed');
    }

    public function test_landing_page_renders_without_any_seed_data(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Access Portal')
            ->assertSee('Why this Portal?')
            ->assertSee('System Status')
            ->assertSee('No announcements yet');
    }

    public function test_requirement_submissions_feed_statistics(): void
    {
        $this->seedSettings();
        $teacherUser = $this->makeUser('teacher', 'req_teacher');
        $this->makeTeacher($teacherUser);
        $studentUser = $this->makeUser('student', 'req_student');
        $this->makeStudent($studentUser);

        $requirement = Requirement::create([
            'teacher_id' => Teacher::where('user_id', $teacherUser->id)->value('id'),
            'title' => 'Research Paper',
            'requirement_type' => Requirement::TYPE_ACADEMIC,
            'description' => 'Body',
            'due_date' => now()->addDays(5)->toDateString(),
            'submission_required' => true,
            'section' => 'Grade 7-A',
            'school_year' => '2026-2027',
            'term' => 1,
            'status' => Requirement::STATUS_ACTIVE,
        ]);

        RequirementSubmission::create([
            'requirement_id' => $requirement->id,
            'student_id' => Student::where('user_id', $studentUser->id)->value('id'),
            'teacher_id' => Teacher::where('user_id', $teacherUser->id)->value('id'),
            'status' => RequirementSubmission::STATUS_APPROVED,
            'submitted_at' => now()->subDay(),
            'reviewed_at' => now(),
        ]);

        $this->assertSame(1, RequirementSubmission::count());

        $this->get('/')
            ->assertOk()
            ->assertSee('Requirements Processed');
    }
}
