<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class GuardianLinkRequestedNotification extends Notification
{
    public function __construct(
        public string $guardianName,
        public string $relationship,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Guardian link request',
            'message' => "{$this->guardianName} ({$this->relationship}) wants to link to your student account. Review the request to accept or deny it.",
            'url' => route('student.link-requests.index'),
        ];
    }
}
