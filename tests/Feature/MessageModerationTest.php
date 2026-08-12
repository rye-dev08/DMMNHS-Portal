<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\MessageSenderBlock;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MessageModerationTest extends TestCase
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

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin Message',
            'username' => 'admin.messages',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('Secret123!'),
            'role' => 'office_admin',
            'status' => 'active',
        ]);
    }

    private function makeStudent(): User
    {
        self::$counter++;
        $user = User::create([
            'name' => 'Jane Doe',
            'username' => 'jane.message'.self::$counter,
            'email' => 'jane'.self::$counter.'@example.com',
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
            'name' => 'Mr. Smith',
            'username' => 'smith.message'.self::$counter,
            'email' => 'smith'.self::$counter.'@example.com',
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

    private function sendMessage(User $user, string $body = 'Hello admin', string $subject = 'Inquiry')
    {
        return $this->actingAs($user)
            ->from(route('contact'))
            ->post(route('contact.submit'), [
                'name' => $user->name,
                'email' => $user->email,
                'subject' => $subject,
                'message' => $body,
            ]);
    }

    private function messageCount(): int
    {
        return DB::table('contact_messages')->count();
    }

    public function test_student_and_teacher_see_limit_and_prefilled_name_on_contact_page(): void
    {
        $this->seedSettings();
        $student = $this->makeStudent();
        $teacher = $this->makeTeacher();

        $this->actingAs($student)
            ->get(route('contact'))
            ->assertOk()
            ->assertSee('3 of 3 messages remaining today', false)
            ->assertSee('value="'.$student->name.'"', false)
            ->assertSee('value="'.$student->email.'"', false);

        $this->actingAs($teacher)
            ->get(route('contact'))
            ->assertOk()
            ->assertSee('3 of 3 messages remaining today', false);
    }

    public function test_student_contact_message_is_attributed_to_account_not_form_fields(): void
    {
        $this->seedSettings();
        $student = $this->makeStudent();

        // A troll can type anything in the form; the account must win.
        $this->actingAs($student)
            ->from(route('contact'))
            ->post(route('contact.submit'), [
                'name' => 'Fake Troll Name',
                'email' => 'troll@fake.com',
                'subject' => 'Schedule concern',
                'message' => 'I need help with my schedule.',
            ])
            ->assertRedirect(route('contact'));

        $this->assertSame(1, $this->messageCount());
        $message = ContactMessage::first();
        $this->assertSame('pending', $message->status);
        $this->assertSame($student->id, $message->user_id);
        $this->assertSame('student', $message->sender_role);
        $this->assertSame('Schedule concern', $message->subject);
        $this->assertSame('I need help with my schedule.', $message->message);
        $this->assertSame($student->name, $message->name);
        $this->assertSame($student->email, $message->email);
        $this->assertNull($message->moderated_at);
    }

    public function test_daily_limit_is_enforced_server_side(): void
    {
        $this->seedSettings();
        $student = $this->makeStudent();

        $service = app(MessageService::class);
        $service->submit($student, ['subject' => 'S', 'message' => 'One']);
        $service->submit($student, ['subject' => 'S', 'message' => 'Two']);
        $service->submit($student, ['subject' => 'S', 'message' => 'Three']);
        $this->assertSame(3, $this->messageCount());

        // UI reflects the limit.
        $this->actingAs($student)
            ->get(route('contact'))
            ->assertSee('0 of 3 messages remaining today', false)
            ->assertSee('You have reached your daily message limit. You can send another message tomorrow.', false);

        // 4th attempt must be rejected by the backend regardless of the UI.
        $this->expectException(ValidationException::class);
        $service->submit($student, ['subject' => 'S', 'message' => 'Four']);
    }

    public function test_blocked_sender_cannot_submit_message(): void
    {
        $this->seedSettings();
        $student = $this->makeStudent();

        MessageSenderBlock::create([
            'user_id' => $student->id,
            'blocked_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('contact'))
            ->assertSee('You are currently unable to send messages to the administration.', false);

        $this->sendMessage($student)
            ->assertSessionHasErrors('message');

        $this->assertSame(0, $this->messageCount());
    }

    public function test_admin_can_mark_valid_sets_status_and_notifies_generically(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();
        $this->sendMessage($student);

        $message = ContactMessage::first();

        $this->actingAs($admin)
            ->post(route('office.messages.valid', $message->id))
            ->assertRedirect();

        $message->refresh();
        $this->assertSame('valid', $message->status);
        $this->assertNotNull($message->moderated_at);
        $this->assertNull($message->expires_at);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_admin_can_mark_invalid_sets_expiry_and_identical_notification(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();
        $this->sendMessage($student);

        $message = ContactMessage::first();

        $this->actingAs($admin)
            ->post(route('office.messages.invalid', $message->id))
            ->assertRedirect();

        $message->refresh();
        $this->assertSame('invalid', $message->status);
        $this->assertNotNull($message->moderated_at);
        $this->assertNotNull($message->expires_at);
        $this->assertEqualsWithDelta(now()->addDay()->timestamp, $message->expires_at->timestamp, 60);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_marking_invalid_does_not_block_the_sender(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();
        $this->sendMessage($student);

        $this->actingAs($admin)
            ->post(route('office.messages.invalid', ContactMessage::first()->id));

        $this->assertDatabaseCount('message_sender_blocks', 0);
        $this->assertFalse(app(MessageService::class)->isBlocked($student));
    }

    public function test_expired_invalid_messages_are_archived_and_hidden(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();
        $this->sendMessage($student, 'Old invalid message');

        $message = ContactMessage::first();
        $message->update([
            'status' => 'invalid',
            'moderated_at' => now()->subDays(2),
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->get(route('office.message-center'))
            ->assertOk()
            ->assertDontSee('Old invalid message', false);

        $message->refresh();
        $this->assertNotNull($message->archived_at);
    }

    public function test_admin_center_shows_summary_and_message_details(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();
        $this->sendMessage($student, 'A pending message body', 'Pending subject');

        $this->actingAs($admin)
            ->get(route('office.message-center'))
            ->assertOk()
            ->assertSee('Jane Doe', false)
            ->assertSee('PENDING', false)
            ->assertSee('A pending message body', false)
            ->assertSee('Pending Review', false);
    }

    public function test_admin_can_filter_messages_by_status_role_and_blocked(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();
        $teacher = $this->makeTeacher();

        $this->sendMessage($student, 'Student message');
        $this->sendMessage($teacher, 'Teacher message');

        // Status filter.
        $this->actingAs($admin)
            ->get(route('office.message-center', ['status' => 'pending']))
            ->assertSee('Student message', false)
            ->assertSee('Teacher message', false);

        // Role filter.
        $this->actingAs($admin)
            ->get(route('office.message-center', ['role' => 'teacher']))
            ->assertSee('Teacher message', false)
            ->assertDontSee('Student message', false);

        // Blocked-sender filter.
        $blocked = $this->makeStudent();
        $this->sendMessage($blocked, 'From blocked sender');
        MessageSenderBlock::create(['user_id' => $blocked->id, 'blocked_at' => now()]);

        $this->actingAs($admin)
            ->get(route('office.message-center', ['blocked' => 1]))
            ->assertSee('From blocked sender', false)
            ->assertDontSee('Student message', false);
    }

    public function test_admin_can_block_sender_with_reason_and_unblock(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();
        $this->sendMessage($student);

        $message = ContactMessage::first();

        $this->actingAs($admin)
            ->post(route('office.messages.block', $message->id), ['reason' => 'Spam messages'])
            ->assertRedirect();

        $block = MessageSenderBlock::first();
        $this->assertNotNull($block);
        $this->assertSame($student->id, $block->user_id);
        $this->assertSame('Spam messages', $block->reason);
        $this->assertSame($admin->id, $block->blocked_by);
        $this->assertNull($block->unblocked_at);

        // Message status must remain unchanged by blocking.
        $message->refresh();
        $this->assertSame('pending', $message->status);

        // Shown in the blocked senders page.
        $this->actingAs($admin)
            ->get(route('office.message-center.blocked'))
            ->assertOk()
            ->assertSee('Jane Doe', false)
            ->assertSee('Spam messages', false);

        // Unblock restores sending.
        $this->actingAs($admin)
            ->post(route('office.message-sender-blocks.unblock', $block->id))
            ->assertRedirect(route('office.message-center.blocked'));

        $block->refresh();
        $this->assertNotNull($block->unblocked_at);
        $this->assertFalse(app(MessageService::class)->isBlocked($student));
    }

    public function test_admin_can_delete_message(): void
    {
        $this->seedSettings();
        $admin = $this->makeAdmin();
        $student = $this->makeStudent();
        $this->sendMessage($student);

        $message = ContactMessage::first();

        $this->actingAs($admin)
            ->delete(route('office.messages.destroy', $message->id))
            ->assertRedirect();

        $this->assertSame(0, $this->messageCount());
    }

    public function test_guest_contact_form_still_creates_anonymous_messages(): void
    {
        $this->seedSettings();

        $this->from(route('contact'))
            ->post(route('contact.submit'), [
                'name' => 'Guest User',
                'email' => 'guest@example.com',
                'subject' => 'Hello',
                'message' => 'An anonymous inquiry.',
            ])
            ->assertRedirect(route('contact'));

        $message = ContactMessage::first();
        $this->assertNotNull($message);
        $this->assertSame('pending', $message->status);
        $this->assertNull($message->user_id);
        $this->assertNull($message->sender_role);
        $this->assertSame('Guest User', $message->name);
    }

    public function test_admin_center_requires_admin_role(): void
    {
        $this->seedSettings();
        $student = $this->makeStudent();

        $this->actingAs($student)
            ->get(route('office.message-center'))
            ->assertRedirect(route('login'));
    }
}
