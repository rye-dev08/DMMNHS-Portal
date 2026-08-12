<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\MessageSenderBlock;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Central logic for the Teacher/Student -> Admin messaging feature built on
 * top of the existing contact_messages table. Enforces the daily send limit
 * and the blocked-sender restriction server-side, and owns the moderation
 * transitions (pending -> valid/invalid) plus the invalid-message retention
 * timestamp.
 */
class MessageService
{
    public const DAILY_LIMIT = 3;

    public const BLOCKED_MESSAGE = 'You are currently unable to send messages to the administration.';

    public const LIMIT_MESSAGE = 'You have reached your daily message limit. You can send another message tomorrow.';

    public const RECEIVED_MESSAGE = 'Message received!';

    /**
     * Number of messages the given user can still send today.
     */
    public function remainingToday(User $user): int
    {
        $sent = ContactMessage::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        return max(0, self::DAILY_LIMIT - $sent);
    }

    /**
     * Whether the user has reached their daily message limit.
     */
    public function limitReached(User $user): bool
    {
        return $this->remainingToday($user) <= 0;
    }

    /**
     * Whether the user is currently blocked from sending messages.
     */
    public function isBlocked(User $user): bool
    {
        return $this->activeBlock($user) !== null;
    }

    /**
     * The currently active block for the user, if any.
     */
    public function activeBlock(User $user): ?MessageSenderBlock
    {
        return MessageSenderBlock::query()
            ->where('user_id', $user->id)
            ->whereNull('unblocked_at')
            ->latest()
            ->first();
    }

    /**
     * Create a pending message on behalf of a logged-in student/teacher.
     * Throws a validation error when blocked or over the daily limit so the
     * restriction can never be bypassed through the UI.
     */
    public function submit(User $user, array $validated): ContactMessage
    {
        if ($this->isBlocked($user)) {
            throw ValidationException::withMessages([
                'message' => self::BLOCKED_MESSAGE,
            ]);
        }

        if ($this->limitReached($user)) {
            throw ValidationException::withMessages([
                'message' => self::LIMIT_MESSAGE,
            ]);
        }

        return ContactMessage::create([
            'name' => $user->name,
            'email' => $user->email,
            'user_id' => $user->id,
            'sender_role' => $user->role,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'status' => ContactMessage::STATUS_PENDING,
        ]);
    }

    /**
     * Moderate a message. VALID keeps the message in the center indefinitely;
     * INVALID gets an explicit 24-hour expiry timestamp.
     */
    public function moderate(ContactMessage $message, string $status): void
    {
        $message->update([
            'status' => $status,
            'moderated_at' => now(),
            'expires_at' => $status === ContactMessage::STATUS_INVALID
                ? now()->addDay()
                : null,
        ]);
    }

    /**
     * Manually block a message sender. Separate from message moderation.
     */
    public function blockSender(User $sender, ?string $reason, User $admin): MessageSenderBlock
    {
        return MessageSenderBlock::updateOrCreate(
            ['user_id' => $sender->id],
            [
                'reason' => $reason ? trim($reason) ?: null : null,
                'blocked_by' => $admin->id,
                'blocked_at' => now(),
                'unblocked_at' => null,
            ]
        );
    }

    /**
     * Unblock a sender (closes the active block).
     */
    public function unblock(MessageSenderBlock $block): void
    {
        $block->update(['unblocked_at' => now()]);
    }

    /**
     * Archive invalid messages whose 24-hour retention has elapsed. Called on
     * the admin Message Center so expired items disappear without a cron.
     */
    public function pruneExpiredInvalid(): int
    {
        $expired = ContactMessage::query()
            ->where('status', ContactMessage::STATUS_INVALID)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->whereNull('archived_at');

        $count = (clone $expired)->count();

        $expired->update(['archived_at' => now()]);

        return $count;
    }
}
