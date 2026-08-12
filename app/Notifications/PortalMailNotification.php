<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PortalMailNotification extends Notification
{
    /**
     * Portal + email notification stored in the notifications table and
     * delivered as an email when the recipient has an address on file.
     *
     * @param  array{title?: string, message: string, kind?: string, link?: string, subject?: string, greeting?: string, lines?: array<int, string>, action_text?: string, action_url?: string}  $data
     */
    public function __construct(
        public readonly array $data,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->data;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->data['subject'] ?? $this->data['title'] ?? 'DMMNHS Student Portal';

        return (new MailMessage)
            ->subject($subject)
            ->view('mail.layout', ['notification' => $this->data]);
    }
}
