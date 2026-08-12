<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PortalNotification extends Notification
{
    /**
     * Portal-only notification stored in the notifications table.
     * The $data array holds the fields rendered by the bell dropdown
     * and the /notifications page.
     *
     * @param  array{title?: string, message: string, kind?: string, link?: string}  $data
     */
    public function __construct(
        public readonly array $data,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->data;
    }
}
