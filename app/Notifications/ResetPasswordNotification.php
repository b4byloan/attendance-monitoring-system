<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        $schoolName = Setting::current()->school_name ?: config('app.name');

        return (new MailMessage)
            ->from(config('mail.from.address'), $schoolName)
            ->subject("Reset your {$schoolName} password")
            ->view('emails.auth.reset-password', [
                'url' => $url,
                'schoolName' => $schoolName,
                'expiresInMinutes' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            ]);
    }
}
