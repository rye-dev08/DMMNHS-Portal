<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
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

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('office.dashboard'))->assertRedirect(route('login'));
        $this->get(route('teacher.dashboard'))->assertRedirect(route('login'));
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
    }

    public function test_system_admin_can_access_admin_area_but_not_office_or_teacher_area(): void
    {
        $this->seedSettings();
        $user = $this->makeUser('system_admin', 'admin');

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('System Administrator Dashboard', false);

        $this->actingAs($user)
            ->get(route('admin.accounts'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('office.dashboard'))
            ->assertRedirect(route('login'));

        $this->actingAs($user)
            ->get(route('teacher.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_office_admin_can_access_office_area_but_not_admin_or_student_area(): void
    {
        $this->seedSettings();
        $user = $this->makeUser('office_admin', 'office');

        $this->actingAs($user)
            ->get(route('office.dashboard'))
            ->assertOk()
            ->assertSee('Office Administrator Dashboard', false);

        $this->actingAs($user)
            ->get(route('office.announcements'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.accounts'))
            ->assertRedirect(route('login'));

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_teacher_cannot_access_admin_or_office_area(): void
    {
        $this->seedSettings();
        $user = $this->makeUser('teacher', 'teacher');
        Teacher::create([
            'user_id' => $user->id,
            'advisory_class' => 'Grade 7-A',
            'max_students' => 30,
            'max_subjects' => 8,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('teacher.dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->actingAs($user)
            ->get(route('office.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_admin_or_office_area(): void
    {
        $this->seedSettings();
        $user = $this->makeUser('student', 'student');
        Student::create([
            'user_id' => $user->id,
            'grade_level' => 7,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->actingAs($user)
            ->get(route('office.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_new_staff_roles_are_persistable(): void
    {
        $this->makeUser('system_admin', 'sysadmin');
        $this->makeUser('office_admin', 'office2');

        $this->assertDatabaseHas('users', ['username' => 'sysadmin', 'role' => 'system_admin']);
        $this->assertDatabaseHas('users', ['username' => 'office2', 'role' => 'office_admin']);
    }
}
