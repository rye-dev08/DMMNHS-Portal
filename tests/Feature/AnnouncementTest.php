<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AnnouncementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    private static int $studentCounter = 0;

    private static int $teacherCounter = 0;

    private function seedSettings(string $year = '2026-2027', int $term = 1): void
    {
        Setting::create([
            'id' => 1,
            'current_term' => $term,
            'current_school_year' => $year,
            'max_students_per_class' => 30,
            'max_subjects_per_teacher' => 8,
            'enrollment_phase' => 'none',
        ]);
    }

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin One',
            'username' => 'admin.announce',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'office_admin',
            'status' => 'active',
        ]);
    }

    private function makeStudent(int $grade = 7, ?string $section = null): array
    {
        self::$studentCounter++;
        $user = User::create([
            'name' => 'Juan Dela Cruz',
            'username' => 'juan.announce'.self::$studentCounter,
            'email' => 'juan'.self::$studentCounter.'@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'grade_level' => $grade,
            'status' => 'active',
        ]);

        if ($section !== null) {
            $teacher = $this->makeTeacher($section);
            $teacherId = DB::table('teachers')->where('user_id', $teacher->id)->value('id');
            DB::table('enrollment_requests')->insert([
                'student_id' => $student->id,
                'teacher_id' => (int) $teacherId,
                'status' => 'approved',
                'date_requested' => now(),
            ]);
        }

        return [$user, $student];
    }

    private function makeTeacher(string $advisory = 'Grade 7-A'): User
    {
        self::$teacherCounter++;
        $user = User::create([
            'name' => 'Mr. Smith',
            'username' => 'smith'.self::$teacherCounter,
            'email' => 'smith'.self::$teacherCounter.'@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        Teacher::create([
            'user_id' => $user->id,
            'advisory_class' => $advisory,
            'max_students' => 30,
            'max_subjects' => 8,
            'status' => 'active',
        ]);

        return $user;
    }

    private function announcementData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Quarterly Examinations Schedule',
            'short_summary' => 'Final exams begin next week.',
            'content' => 'Please prepare your review materials and report on time.',
            'priority' => 'normal',
            'status' => 'published',
            'target_role' => 'all',
            'publish_date' => now()->format('Y-m-d'),
            'expiration_date' => '',
            'school_year' => '2026-2027',
            'term' => 1,
        ], $overrides);
    }

    private function feedIds(User $user): array
    {
        return app(AnnouncementService::class)->feed($user)['items']
            ->pluck('id')
            ->all();
    }

    public function test_admin_can_create_announcement_and_see_it_in_management_page(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('office.announcements.store'), $this->announcementData())
            ->assertRedirect(route('office.announcements'));

        $this->assertDatabaseHas('announcements', [
            'title' => 'Quarterly Examinations Schedule',
            'target_role' => 'all',
            'school_year' => '2026-2027',
            'term' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('office.announcements'))
            ->assertOk()
            ->assertSee('Quarterly Examinations Schedule', false);
    }

    public function test_all_announcements_reach_every_role(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        [$student] = $this->makeStudent();
        $teacher = $this->makeTeacher();

        $announcement = Announcement::create($this->announcementData(['target_role' => 'all']));

        foreach ([$admin, $student, $teacher] as $user) {
            $this->assertContains($announcement->id, $this->feedIds($user), "Role {$user->role} should see the announcement");
        }
    }

    public function test_role_targeting_is_respected(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        [$student] = $this->makeStudent();
        $teacher = $this->makeTeacher();

        $forStudents = Announcement::create($this->announcementData(['target_role' => 'students', 'title' => 'For Students']));
        $forTeachers = Announcement::create($this->announcementData(['target_role' => 'teachers', 'title' => 'For Teachers']));
        $forAdmins = Announcement::create($this->announcementData(['target_role' => 'admins', 'title' => 'For Admins']));

        $this->assertContains($forStudents->id, $this->feedIds($student));
        $this->assertNotContains($forStudents->id, $this->feedIds($teacher));
        $this->assertNotContains($forStudents->id, $this->feedIds($admin));

        $this->assertContains($forTeachers->id, $this->feedIds($teacher));
        $this->assertNotContains($forTeachers->id, $this->feedIds($student));

        $this->assertContains($forAdmins->id, $this->feedIds($admin));
        $this->assertNotContains($forAdmins->id, $this->feedIds($student));
    }

    public function test_grade_level_refinement_targets_only_that_grade(): void
    {
        $this->seedSettings();
        [$grade7Student] = $this->makeStudent(7);
        [$grade9Student] = $this->makeStudent(9);

        $announcement = Announcement::create($this->announcementData([
            'target_role' => 'students',
            'title' => 'Grade 7 Meeting',
        ]));
        $announcement->audiences()->create(['target_type' => 'grade_level', 'target_value' => '7']);

        $this->assertContains($announcement->id, $this->feedIds($grade7Student));
        $this->assertNotContains($announcement->id, $this->feedIds($grade9Student));
    }

    public function test_section_refinement_uses_approved_enrollment_advisory(): void
    {
        $this->seedSettings();
        [$inSection] = $this->makeStudent(7, 'Grade 7-Rizal');
        [$otherStudent] = $this->makeStudent(7, 'Grade 7-Bonifacio');

        $announcement = Announcement::create($this->announcementData([
            'target_role' => 'students',
            'title' => 'Section Rizal Notice',
        ]));
        $announcement->audiences()->create(['target_type' => 'section', 'target_value' => 'Grade 7-Rizal']);

        $this->assertContains($announcement->id, $this->feedIds($inSection));
        $this->assertNotContains($announcement->id, $this->feedIds($otherStudent));
    }

    public function test_specific_student_refinement_reaches_only_that_student(): void
    {
        $this->seedSettings();
        [$targetStudent, $targetProfile] = $this->makeStudent();
        [$otherStudent] = $this->makeStudent();

        $announcement = Announcement::create($this->announcementData([
            'target_role' => 'students',
            'title' => 'Personal Notice',
        ]));
        $announcement->audiences()->create(['target_type' => 'student', 'target_value' => (string) $targetProfile->id]);

        $this->assertContains($announcement->id, $this->feedIds($targetStudent));
        $this->assertNotContains($announcement->id, $this->feedIds($otherStudent));
    }

    public function test_specific_teacher_refinement_reaches_only_that_teacher(): void
    {
        $this->seedSettings();
        $teacherA = $this->makeTeacher('Grade 7-Rizal');
        $teacherB = $this->makeTeacher('Grade 8-Rizal');
        $teacherAId = DB::table('teachers')->where('user_id', $teacherA->id)->value('id');

        $announcement = Announcement::create($this->announcementData([
            'target_role' => 'teachers',
            'title' => 'Faculty Meeting',
        ]));
        $announcement->audiences()->create(['target_type' => 'teacher', 'target_value' => (string) $teacherAId]);

        $this->assertContains($announcement->id, $this->feedIds($teacherA));
        $this->assertNotContains($announcement->id, $this->feedIds($teacherB));
    }

    public function test_unpublished_and_expired_announcements_are_hidden_from_feed(): void
    {
        $this->seedSettings();
        [$student] = $this->makeStudent();

        Announcement::create($this->announcementData([
            'title' => 'Unpublished Draft',
            'status' => 'unpublished',
        ]));
        Announcement::create($this->announcementData([
            'title' => 'Expired Notice',
            'expiration_date' => now()->subDay()->format('Y-m-d'),
        ]));
        Announcement::create($this->announcementData([
            'title' => 'Future Year Notice',
            'school_year' => '2027-2028',
        ]));
        Announcement::create($this->announcementData([
            'title' => 'Other Term Notice',
            'term' => 2,
        ]));

        $visible = $this->feedIds($student);

        $this->assertCount(0, $visible);
        $this->assertDatabaseHas('announcements', ['title' => 'Unpublished Draft']);
        $this->assertDatabaseHas('announcements', ['title' => 'Expired Notice']);
    }

    public function test_marking_read_tracks_read_state_and_updates_unread_count(): void
    {
        $this->seedSettings();
        [$student] = $this->makeStudent();
        $service = app(AnnouncementService::class);

        $announcement = Announcement::create($this->announcementData(['target_role' => 'students']));

        $this->assertSame(1, $service->unreadCount($student));

        $service->markRead($announcement, $student);

        $this->assertSame(0, $service->unreadCount($student));

        $item = $service->feed($student)['items']->first();
        $this->assertTrue($item->is_read);
    }

    public function test_mark_read_route_updates_read_state(): void
    {
        $this->seedSettings();
        [$student] = $this->makeStudent();
        $announcement = Announcement::create($this->announcementData(['target_role' => 'students']));

        $this->actingAs($student)
            ->post(route('announcements.mark-read'), ['id' => $announcement->id])
            ->assertOk()
            ->assertJson(['ok' => true, 'unread' => 0]);

        $this->assertDatabaseHas('announcement_reads', [
            'announcement_id' => $announcement->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_user_announcement_page_requires_auth_and_lists_feed(): void
    {
        $this->seedSettings();
        [$student] = $this->makeStudent();
        Announcement::create($this->announcementData(['target_role' => 'students', 'title' => 'Visible Notice']));

        $this->actingAs($student)
            ->get(route('announcements'))
            ->assertOk()
            ->assertSee('Visible Notice', false);

        Auth::logout();
        $this->get(route('announcements'))->assertRedirect(route('login'));
    }

    public function test_admin_can_toggle_publish_status(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        $announcement = Announcement::create($this->announcementData());

        $this->actingAs($admin)
            ->post(route('office.announcements.toggle-status', $announcement->id))
            ->assertRedirect(route('office.announcements'));

        $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'status' => 'unpublished']);

        $this->actingAs($admin)
            ->post(route('office.announcements.toggle-status', $announcement->id))
            ->assertRedirect(route('office.announcements'));

        $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'status' => 'published']);
    }

    public function test_admin_can_update_announcement_and_replace_audience(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        [$student] = $this->makeStudent(7);

        $announcement = Announcement::create($this->announcementData([
            'target_role' => 'students',
            'title' => 'Original Title',
        ]));
        $announcement->audiences()->create(['target_type' => 'grade_level', 'target_value' => '7']);

        $this->actingAs($admin)
            ->put(route('office.announcements.update', $announcement->id), $this->announcementData([
                'target_role' => 'students',
                'title' => 'Updated Title',
                'grade_levels' => ['8'],
            ]))
            ->assertRedirect(route('office.announcements'));

        $announcement->refresh();

        $this->assertSame('Updated Title', $announcement->title);
        $this->assertCount(1, $announcement->audiences);
        $this->assertSame('8', $announcement->audiences->first()->target_value);
    }

    public function test_admin_can_delete_announcement(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        $announcement = Announcement::create($this->announcementData());

        $this->actingAs($admin)
            ->delete(route('office.announcements.destroy', $announcement->id))
            ->assertRedirect(route('office.announcements'));

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_create_announcement_requires_valid_fields(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->from(route('office.announcements'))
            ->post(route('office.announcements.store'), $this->announcementData([
                'title' => '',
                'target_role' => 'bogus',
                'term' => 9,
            ]))
            ->assertSessionHasErrors(['title', 'target_role', 'term']);
    }

    public function test_student_and_teacher_dashboards_render_announcement_feed(): void
    {
        $this->seedSettings();
        [$student] = $this->makeStudent();
        $teacher = $this->makeTeacher();
        Announcement::create($this->announcementData(['target_role' => 'all', 'title' => 'Dashboard Notice']));

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Notice', false);

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Notice', false);
    }

    public function test_audience_labels_are_descriptive(): void
    {
        $this->seedSettings();
        $service = app(AnnouncementService::class);

        $all = Announcement::create($this->announcementData(['target_role' => 'all']));
        $this->assertSame('All Users', $service->audienceLabel($all));

        $targeted = Announcement::create($this->announcementData([
            'target_role' => 'students',
            'title' => 'Targeted',
        ]));
        $targeted->audiences()->create(['target_type' => 'grade_level', 'target_value' => '7']);
        $targeted->audiences()->create(['target_type' => 'section', 'target_value' => 'Grade 7-Rizal']);

        $label = $service->audienceLabel($targeted);
        $this->assertStringContainsString('Students', $label);
        $this->assertStringContainsString('Grade 7', $label);
        $this->assertStringContainsString('Grade 7-Rizal', $label);
    }

    public function test_published_announcement_notifies_students_and_teachers(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        [$student] = $this->makeStudent();
        $teacher = $this->makeTeacher();

        $this->actingAs($admin)
            ->post(route('office.announcements.store'), $this->announcementData([
                'target_role' => 'all',
                'title' => 'Whole School Notice',
            ]))
            ->assertRedirect(route('office.announcements'));

        foreach (['student' => $student, 'teacher' => $teacher] as $role => $user) {
            $this->assertSame(
                1,
                $user->unreadNotifications()->count(),
                "{$role} should receive one announcement notification"
            );
            $this->assertSame(
                'New Announcement',
                $user->notifications()->first()->data['title'],
                "{$role} notification title mismatch"
            );
            $this->assertStringContainsString(
                'Whole School Notice',
                $user->notifications()->first()->data['message']
            );
        }
    }

    public function test_role_targeted_announcement_only_notifies_matching_role(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        [$student] = $this->makeStudent();
        $teacher = $this->makeTeacher();

        $this->actingAs($admin)
            ->post(route('office.announcements.store'), $this->announcementData([
                'target_role' => 'teachers',
                'title' => 'Faculty Only Notice',
            ]))
            ->assertRedirect(route('office.announcements'));

        $this->assertSame(1, $teacher->unreadNotifications()->count());
        $this->assertSame(0, $student->unreadNotifications()->count());
    }

    public function test_grade_refined_announcement_only_notifies_matching_students(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        [$grade7Student] = $this->makeStudent(7);
        [$grade9Student] = $this->makeStudent(9);

        $this->actingAs($admin)
            ->post(route('office.announcements.store'), $this->announcementData([
                'target_role' => 'students',
                'title' => 'Grade 7 Only Notice',
                'grade_levels' => ['7'],
            ]))
            ->assertRedirect(route('office.announcements'));

        $this->assertSame(1, $grade7Student->unreadNotifications()->count());
        $this->assertSame(0, $grade9Student->unreadNotifications()->count());
    }

    public function test_unpublished_announcement_does_not_notify(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        [$student] = $this->makeStudent();
        $teacher = $this->makeTeacher();

        $this->actingAs($admin)
            ->post(route('office.announcements.store'), $this->announcementData([
                'status' => 'unpublished',
                'title' => 'Draft Notice',
            ]))
            ->assertRedirect(route('office.announcements'));

        $this->assertSame(0, $student->unreadNotifications()->count());
        $this->assertSame(0, $teacher->unreadNotifications()->count());
    }

    public function test_publishing_an_unpublished_announcement_notifies(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        [$student] = $this->makeStudent();

        $announcement = Announcement::create($this->announcementData([
            'status' => 'unpublished',
            'title' => 'Late Publish Notice',
        ]));

        $this->assertSame(0, $student->unreadNotifications()->count());

        $this->actingAs($admin)
            ->post(route('office.announcements.toggle-status', $announcement->id))
            ->assertRedirect(route('office.announcements'));

        $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'status' => 'published']);
        $this->assertSame(1, $student->unreadNotifications()->count());
    }
}
