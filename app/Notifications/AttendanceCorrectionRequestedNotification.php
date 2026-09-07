<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AttendanceCorrectionRequestedNotification extends Notification
{
    public function __construct(
        public string $studentName,
        public int $correctionId,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'New attendance correction request',
            'message' => "A correction was requested for {$this->studentName}.",
            'url' => route('attendance-corrections.index'),
        ];
    }
}