<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class GuardianLinkRespondedNotification extends Notification
{
    public function __construct(
        public string $studentName,
        public bool $accepted,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->accepted ? 'Link request accepted' : 'Link request denied',
            'message' => $this->accepted
                ? "{$this->studentName} accepted your link request. You can now view their records."
                : "{$this->studentName} denied your link request.",
            'url' => route('parent.link-student.index'),
        ];
    }
}
