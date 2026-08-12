<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\DigitalIdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DigitalIdTest extends TestCase
{
    use RefreshDatabase;

    private static int $studentCounter = 0;

    private static int $teacherCounter = 0;

    private function seedSettings(string $year = '2026-2027', int $term = 2): void
    {
        Setting::updateOrCreate(['id' => 1], [
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
            'username' => 'admin.digitalid',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'office_admin',
            'status' => 'active',
        ]);
    }

    private function makeStudent(int $grade = 11, string $advisory = 'Grade 11-Rizal (Academic)', bool $enrolled = true, string $status = 'active'): array
    {
        self::$studentCounter++;
        $user = User::create([
            'name' => 'Juan Dela Cruz',
            'username' => 'juan.digital'.self::$studentCounter,
            'email' => 'juan'.self::$studentCounter.'@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'student',
            'status' => $status === 'inactive' ? 'active' : 'active',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'grade_level' => $grade,
            'status' => $status,
        ]);

        if ($enrolled && $advisory !== null) {
            $teacher = $this->makeTeacher($advisory);
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

    private function makeTeacher(string $advisory = 'Grade 11-Rizal (Academic)'): User
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

    public function test_student_can_view_digital_id_with_stable_id_number_and_qr(): void
    {
        $this->seedSettings();
        [$user, $student] = $this->makeStudent();

        $this->actingAs($user)
            ->get(route('student.digital-id'))
            ->assertOk()
            ->assertSee('Digital Student ID', false)
            ->assertSee($user->name, false);

        $student->refresh();
        $this->assertNotNull($student->student_id_no);
        $this->assertNotNull($student->id_token);
        $this->assertNotNull($student->id_token_generated_at);
        $this->assertSame(64, strlen((string) $student->id_token));

        // The ID number is derived from the current school year + student id.
        $this->assertStringStartsWith('2026-', $student->student_id_no);

        $this->actingAs($user)
            ->get(route('student.digital-id'))
            ->assertSee(route('verify.student', $student->id_token), false)
            ->assertSee('svg', false);
    }

    public function test_digital_id_status_uses_current_academic_period(): void
    {
        $this->seedSettings('2026-2027', 2);
        [$user] = $this->makeStudent();

        $this->actingAs($user)
            ->get(route('student.digital-id'))
            ->assertOk()
            ->assertSee('2026-2027', false)
            ->assertSee('Term 2', false);
    }

    public function test_verification_returns_verified_for_enrolled_active_student(): void
    {
        $this->seedSettings('2026-2027', 2);
        [$user, $student] = $this->makeStudent();

        $token = app(DigitalIdService::class)->ensureToken($student);

        $this->get(route('verify.student', $token))
            ->assertOk()
            ->assertSee('Verified Student', false)
            ->assertSee('This student ID is currently valid.', false)
            ->assertSee($user->name, false)
            ->assertSee($student->student_id_no, false)
            ->assertSee('2026-2027', false)
            ->assertSee('Term 2', false);
    }

    public function test_verification_returns_not_enrolled_for_student_without_approved_enrollment(): void
    {
        $this->seedSettings();
        [$user, $student] = $this->makeStudent(enrolled: false);

        $token = app(DigitalIdService::class)->ensureToken($student);

        $this->get(route('verify.student', $token))
            ->assertOk()
            ->assertSee('Not Currently Enrolled', false)
            ->assertSee('does not have an active enrollment', false)
            ->assertDontSee('Verified Student', false);
    }

    public function test_verification_returns_inactive_for_inactive_student(): void
    {
        $this->seedSettings();
        [$user, $student] = $this->makeStudent(status: 'inactive', enrolled: true);

        $token = app(DigitalIdService::class)->ensureToken($student);

        $this->get(route('verify.student', $token))
            ->assertOk()
            ->assertSee('ID Inactive', false)
            ->assertSee('no longer active', false);
    }

    public function test_invalid_token_shows_invalid_without_exposing_student(): void
    {
        $this->seedSettings();
        [$user] = $this->makeStudent();

        $this->get(route('verify.student', 'bogus-token-that-does-not-exist'))
            ->assertOk()
            ->assertSee('Invalid ID', false)
            ->assertSee('could not be verified', false)
            ->assertDontSee($user->name, false);
    }

    public function test_verification_reflects_settings_changes_dynamically(): void
    {
        $this->seedSettings('2025-2026', 1);
        [$user, $student] = $this->makeStudent();

        $token = app(DigitalIdService::class)->ensureToken($student);

        $this->get(route('verify.student', $token))
            ->assertSee('2025-2026', false)
            ->assertSee('Term 1', false);

        $this->seedSettings('2027-2028', 3);
        $this->get(route('verify.student', $token))
            ->assertOk()
            ->assertSee('2027-2028', false)
            ->assertSee('Term 3', false);
    }

    public function test_admin_can_list_search_and_see_status(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        [$user, $student] = $this->makeStudent();
        $no = app(DigitalIdService::class)->ensureStudentIdNo($student);

        $this->actingAs($admin)
            ->get(route('office.digital-ids'))
            ->assertOk()
            ->assertSee($user->name, false)
            ->assertSee($no, false)
            ->assertSee('Active', false);

        $this->actingAs($admin)
            ->get(route('office.digital-ids', ['q' => $user->name]))
            ->assertOk()
            ->assertSee($user->name, false);
    }

    public function test_admin_can_regenerate_and_revoke_token(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        [, $student] = $this->makeStudent();
        $service = app(DigitalIdService::class);

        $original = $service->ensureToken($student);

        $this->actingAs($admin)
            ->post(route('office.digital-ids.regenerate', $student->id))
            ->assertRedirect(route('office.digital-ids'));

        $student->refresh();
        $this->assertNotSame($original, $student->id_token);
        $this->assertNotNull($student->id_token_generated_at);

        $this->actingAs($admin)
            ->post(route('office.digital-ids.revoke', $student->id))
            ->assertRedirect(route('office.digital-ids'));

        $student->refresh();
        $this->assertNull($student->id_token);
        $this->assertNull($student->id_token_generated_at);
    }

    public function test_admin_can_view_single_digital_id(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        [$user, $student] = $this->makeStudent();

        $this->actingAs($admin)
            ->get(route('office.digital-ids.show', $student->id))
            ->assertOk()
            ->assertSee($user->name, false)
            ->assertSee('svg', false);
    }

    public function test_student_can_upload_profile_photo(): void
    {
        $this->seedSettings();
        Storage::fake('public');
        [$user, $student] = $this->makeStudent();

        $file = UploadedFile::fake()->image('portrait.jpg', 200, 200);

        $this->actingAs($user)
            ->post(route('student.digital-id.photo'), ['photo' => $file])
            ->assertRedirect(route('student.digital-id'));

        $student->refresh();
        $this->assertNotNull($student->photo);
        Storage::disk('public')->assertExists($student->photo);
    }

    public function test_photo_upload_requires_valid_image(): void
    {
        $this->seedSettings();
        [$user] = $this->makeStudent();

        $this->actingAs($user)
            ->from(route('student.digital-id'))
            ->post(route('student.digital-id.photo'), ['photo' => UploadedFile::fake()->create('doc.pdf', 100)])
            ->assertSessionHasErrors('photo');
    }
}
