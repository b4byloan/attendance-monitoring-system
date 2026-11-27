<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class GuardianLinkRequestedNotification extends Notification
{
    public function __construct(
        public string $guardianName,
        public string $relationship,
        public ?int $linkRequestId = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'guardian_link_request',
            'title' => 'Guardian link request',
            'message' => "{$this->guardianName} ({$this->relationship}) wants to link to your student account. Accept or deny the request right here.",
            'link_request_id' => $this->linkRequestId,
            'url' => route('notifications.index'),
        ];
    }
}
