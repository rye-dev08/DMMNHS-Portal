<?php

namespace Tests\Feature;

use App\Models\AcademicCalendarEvent;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCalendarTest extends TestCase
{
    use RefreshDatabase;

    private function seedSettings(string $year = '2026-2027'): void
    {
        Setting::create([
            'id' => 1,
            'current_term' => 1,
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
            'username' => 'admin.one',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'office_admin',
            'status' => 'active',
        ]);
    }

    private function makeStudent(): User
    {
        $user = User::create([
            'name' => 'Juan Dela Cruz',
            'username' => 'juan.calendar',
            'email' => 'juan@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'student',
            'status' => 'active',
        ]);

        Student::create([
            'user_id' => $user->id,
            'grade_level' => 8,
            'status' => 'active',
        ]);

        return $user;
    }

    private function makeTeacher(): User
    {
        $user = User::create([
            'name' => 'Ms. Guro',
            'username' => 'guro.calendar',
            'email' => 'guro@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'teacher',
            'status' => 'active',
        ]);

        Teacher::create([
            'user_id' => $user->id,
            'advisory_class' => 'Grade 8-A',
            'max_students' => 30,
            'max_subjects' => 8,
            'status' => 'active',
        ]);

        return $user;
    }

    private function eventData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Quarterly Examinations',
            'event_date' => now()->format('Y-m-d'),
            'start_time' => '08:00',
            'end_time' => '11:00',
            'location' => 'Covered Court',
            'category' => 'Exam',
            'school_year' => '2026-2027',
            'term' => 1,
            'short_description' => 'First quarter exams begin.',
            'full_description' => 'All students are required to bring their own writing materials.',
        ], $overrides);
    }

    public function test_admin_can_create_event_and_it_appears_on_student_calendar(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();

        $this->actingAs($admin)
            ->post(route('office.academic-calendar.store'), $this->eventData())
            ->assertRedirect(route('office.academic-calendar'));

        $this->assertDatabaseHas('academic_calendar_events', [
            'title' => 'Quarterly Examinations',
            'school_year' => '2026-2027',
            'term' => 1,
        ]);

        $this->actingAs($student)
            ->get(route('student.calendar'))
            ->assertOk()
            ->assertSee('Quarterly Examinations', false)
            ->assertSee('Academic Calendar', false);
    }

    public function test_teacher_can_view_calendar(): void
    {
        $this->seedSettings();
        $teacher = $this->makeTeacher();

        AcademicCalendarEvent::create($this->eventData());

        $this->actingAs($teacher)
            ->get(route('teacher.calendar'))
            ->assertOk()
            ->assertSee('Quarterly Examinations', false);
    }

    public function test_admin_can_update_event(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();

        $event = AcademicCalendarEvent::create($this->eventData());

        $this->actingAs($admin)
            ->put(route('office.academic-calendar.update', $event->id), $this->eventData([
                'title' => 'Updated Title',
                'location' => 'Library',
            ]))
            ->assertRedirect(route('office.academic-calendar'));

        $this->assertDatabaseHas('academic_calendar_events', [
            'id' => $event->id,
            'title' => 'Updated Title',
            'location' => 'Library',
        ]);
    }

    public function test_admin_can_delete_event(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();

        $event = AcademicCalendarEvent::create($this->eventData());

        $this->actingAs($admin)
            ->delete(route('office.academic-calendar.destroy', $event->id))
            ->assertRedirect(route('office.academic-calendar'));

        $this->assertDatabaseMissing('academic_calendar_events', ['id' => $event->id]);
    }

    public function test_create_event_requires_valid_fields(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->from(route('office.academic-calendar'))
            ->post(route('office.academic-calendar.store'), $this->eventData([
                'title' => '',
                'term' => 5,
                'category' => 'Bogus',
            ]))
            ->assertSessionHasErrors(['title', 'term', 'category']);
    }

    public function test_admin_index_filters_by_school_year(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();

        AcademicCalendarEvent::create($this->eventData(['school_year' => '2026-2027', 'title' => 'Current Year Event']));
        AcademicCalendarEvent::create($this->eventData(['school_year' => '2027-2028', 'title' => 'Future Year Event']));

        $this->actingAs($admin)
            ->get(route('office.academic-calendar', ['school_year' => '2026-2027']))
            ->assertOk()
            ->assertSee('Current Year Event', false)
            ->assertDontSee('Future Year Event', false);

        $this->actingAs($admin)
            ->get(route('office.academic-calendar', ['school_year' => '2027-2028']))
            ->assertOk()
            ->assertSee('Future Year Event', false)
            ->assertDontSee('Current Year Event', false);
    }

    public function test_student_calendar_only_reaches_future_year_when_events_exist(): void
    {
        $this->seedSettings();
        $student = $this->makeStudent();

        // An event in the next school year makes it reachable.
        $nextYearEvent = AcademicCalendarEvent::create($this->eventData([
            'school_year' => '2027-2028',
            'event_date' => '2027-07-15',
            'title' => 'Next Year Orientation',
        ]));

        // Navigate to July 2027 (inside SY 2027-2028).
        $this->actingAs($student)
            ->get(route('student.calendar', ['year' => 2027, 'month' => 7]))
            ->assertOk()
            ->assertSee('Next Year Orientation', false);

        // June 2026 is the earliest reachable month; navigation clamps there.
        $response = $this->actingAs($student)
            ->get(route('student.calendar', ['year' => 2025, 'month' => 6]))
            ->assertOk();

        $response->assertSee('2026', false);

        $this->assertDatabaseHas('academic_calendar_events', ['id' => $nextYearEvent->id]);
    }
}
