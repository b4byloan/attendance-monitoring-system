<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AttendanceCorrectionReviewedNotification extends Notification
{
    public function __construct(
        public string $studentName,
        public string $status,
        public ?string $remarks = null,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $message = $this->status === 'approved'
            ? "Your correction request for {$this->studentName} was approved."
            : "Your correction request for {$this->studentName} was rejected." . ($this->remarks ? " Reason: {$this->remarks}" : '');

        return [
            'title' => 'Correction request ' . ucfirst($this->status),
            'message' => $message,
            'url' => route('attendance-corrections.index'),
        ];
    }
}