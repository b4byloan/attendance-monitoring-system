<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendanceRecorded extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $studentName,
        public readonly string $guardianName,
        public readonly string $date,
        public readonly string $time,
        public readonly ?string $subjectName,
        public readonly string $status,
        public readonly ?string $recordedBy = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Attendance Notification - '.$this->studentName,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.attendance-recorded',
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [];
    }
}
