<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpCodeNotification extends Notification
{
    public function __construct(
        public string $code,
        public int $expiresInMinutes,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $school = Setting::current()->school_name ?: config('app.name');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name') ?: $school;

        $message = (new MailMessage)
            ->subject("Your {$school} verification code")
            ->view('emails.auth.otp-code', [
                'schoolName' => $school,
                'code' => $this->code,
                'expiresInMinutes' => $this->expiresInMinutes,
                'recipientName' => $notifiable->name ?? null,
            ]);

        if ($fromAddress) {
            $message->from($fromAddress, $fromName);
        }

        return $message;
    }
}
