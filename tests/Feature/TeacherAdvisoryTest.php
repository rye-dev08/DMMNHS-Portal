<?php

namespace Tests\Feature;

use App\Http\Controllers\OfficeAdmin\TeacherAssignmentController;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TeacherAdvisoryTest extends TestCase
{
    use RefreshDatabase;

    private static int $counter = 0;

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Office Admin',
            'username' => 'office.advisory',
            'email' => 'office.advisory@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'office_admin',
            'status' => 'active',
        ]);
    }

    private function makeTeacher(?string $advisory = null): User
    {
        self::$counter++;
        $user = User::create([
            'name' => 'Teacher '.self::$counter,
            'username' => 'teacher.advisory'.self::$counter,
            'email' => 'teacher.advisory'.self::$counter.'@example.com',
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

    private function assignData(array $overrides = []): array
    {
        return array_merge([
            'grade_level' => '7',
            'section_name' => 'Rizal',
            'track' => '',
        ], $overrides);
    }

    private function advisoryOf(int $userId): ?string
    {
        return DB::table('teachers')->where('user_id', $userId)->value('advisory_class');
    }

    public function test_admin_can_assign_class_to_teacher(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeTeacher();

        $this->actingAs($admin)
            ->post(route('office.assign-class.store'), array_merge(
                ['teacher_user_id' => $teacher->id],
                $this->assignData(['section_name' => 'A'])
            ))
            ->assertRedirect(route('office.teacher-advisory'));

        $this->assertSame('Grade 7-A', $this->advisoryOf($teacher->id));
    }

    public function test_taken_section_is_rejected_across_any_grade(): void
    {
        $admin = $this->makeAdmin();
        $this->makeTeacher('Grade 7-Rizal');
        $teacherB = $this->makeTeacher();

        $this->actingAs($admin)
            ->post(route('office.assign-class.store'), array_merge(
                ['teacher_user_id' => $teacherB->id],
                $this->assignData(['grade_level' => '9', 'section_name' => 'Rizal'])
            ))
            ->assertSessionHasErrors('section_name');

        $this->assertNull($this->advisoryOf($teacherB->id));
    }

    public function test_taken_section_is_case_insensitive_across_any_grade(): void
    {
        $admin = $this->makeAdmin();
        $this->makeTeacher('Grade 11-Rizal (Academic)');
        $teacherB = $this->makeTeacher();

        $this->actingAs($admin)
            ->post(route('office.assign-class.store'), array_merge(
                ['teacher_user_id' => $teacherB->id],
                $this->assignData(['grade_level' => '8', 'section_name' => '  rizal  '])
            ))
            ->assertSessionHasErrors('section_name');

        $this->assertNull($this->advisoryOf($teacherB->id));
    }

    public function test_similar_but_distinct_section_names_are_allowed(): void
    {
        $admin = $this->makeAdmin();
        $this->makeTeacher('Grade 7-A');
        $teacherB = $this->makeTeacher();

        $this->actingAs($admin)
            ->post(route('office.assign-class.store'), array_merge(
                ['teacher_user_id' => $teacherB->id],
                $this->assignData(['section_name' => 'AB'])
            ))
            ->assertRedirect(route('office.teacher-advisory'));

        $this->assertSame('Grade 7-AB', $this->advisoryOf($teacherB->id));
    }

    public function test_edit_returns_inline_error_when_section_is_taken(): void
    {
        $admin = $this->makeAdmin();
        $this->makeTeacher('Grade 7-Rizal');
        $teacherB = $this->makeTeacher('Grade 8-Bonifacio');

        $response = $this->actingAs($admin)
            ->put(
                route('office.advisory.update', $teacherB->id),
                $this->assignData(['grade_level' => '9', 'section_name' => 'Rizal']),
                ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'text/html']
            );

        $response->assertStatus(422);
        $response->assertSee('This section name is already taken by another class.');
        $this->assertSame('Grade 8-Bonifacio', $this->advisoryOf($teacherB->id));
    }

    public function test_edit_validation_error_is_returned_inline_in_modal(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeTeacher('Grade 8-Bonifacio');

        $response = $this->actingAs($admin)
            ->put(
                route('office.advisory.update', $teacher->id),
                ['grade_level' => '', 'section_name' => 'Bonifacio'],
                ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'text/html']
            );

        $response->assertStatus(422);
        $response->assertSee('The grade level field is required');
        $this->assertSame('Grade 8-Bonifacio', $this->advisoryOf($teacher->id));
    }

    public function test_teacher_can_keep_their_own_section(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeTeacher('Grade 7-Rizal');

        $this->actingAs($admin)
            ->put(
                route('office.advisory.update', $teacher->id),
                $this->assignData(['section_name' => 'Rizal'])
            )
            ->assertRedirect(route('office.teacher-advisory'));

        $this->assertSame('Grade 7-Rizal', $this->advisoryOf($teacher->id));
    }

    public function test_edit_updates_to_a_free_section(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeTeacher('Grade 7-Rizal');

        $this->actingAs($admin)
            ->put(
                route('office.advisory.update', $teacher->id),
                $this->assignData(['grade_level' => '10', 'section_name' => 'Bonifacio'])
            )
            ->assertRedirect(route('office.teacher-advisory'));

        $this->assertSame('Grade 10-Bonifacio', $this->advisoryOf($teacher->id));
    }

    public function test_edit_success_returns_json_redirect_for_ajax(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeTeacher('Grade 7-Rizal');

        $this->actingAs($admin)
            ->put(
                route('office.advisory.update', $teacher->id),
                $this->assignData(['section_name' => 'Bonifacio']),
                ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json']
            )
            ->assertOk()
            ->assertJson(['ok' => true, 'redirect' => route('office.teacher-advisory')]);

        $this->assertSame('Grade 7-Bonifacio', $this->advisoryOf($teacher->id));
    }

    public function test_section_cannot_be_renamed_to_a_taken_name(): void
    {
        $admin = $this->makeAdmin();
        $this->makeTeacher('Grade 7-Bonifacio');
        $teacherB = $this->makeTeacher('Grade 8-Rizal');

        $response = $this->actingAs($admin)
            ->put(
                route('office.advisory.update', $teacherB->id),
                $this->assignData(['grade_level' => '8', 'section_name' => 'Bonifacio']),
                ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'text/html']
            );

        $response->assertStatus(422);
        $response->assertSee('This section name is already taken by another class.');
        $this->assertSame('Grade 8-Rizal', $this->advisoryOf($teacherB->id));
    }

    public function test_taken_sections_returns_distinct_section_names(): void
    {
        $this->makeTeacher('Grade 7-Rizal');
        $this->makeTeacher('Grade 9-Rizal (Academic)');
        $this->makeTeacher('Grade 7-A');
        $this->makeTeacher(null);

        $taken = TeacherAssignmentController::takenSections();

        $this->assertContains('Rizal', $taken);
        $this->assertContains('A', $taken);
        $this->assertCount(2, $taken);
    }

    public function test_section_exists_is_true_after_class_is_assigned(): void
    {
        $teacher = $this->makeTeacher('Grade 7-Rizal');

        $this->assertTrue(TeacherAssignmentController::sectionExists('Rizal'));
        $this->assertTrue(TeacherAssignmentController::sectionExists('  RIZAL  '));
        $this->assertFalse(TeacherAssignmentController::sectionExists('A'));
        $this->assertFalse(TeacherAssignmentController::sectionExists('', $teacher->id));
    }

    public function test_assign_class_page_shows_taken_sections_hint(): void
    {
        $admin = $this->makeAdmin();
        $this->makeTeacher('Grade 7-Rizal');

        $this->actingAs($admin)
            ->get(route('office.assign-class'))
            ->assertOk()
            ->assertSee('Already in use')
            ->assertSee('rizal', false);
    }
}
