<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public const CODE_LENGTH = 6;

    public const EXPIRES_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    /**
     * Create a fresh one-time password for the account and email it.
     */
    public function send(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'otp_code' => Hash::make($code),
            'otp_expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
            'otp_attempts' => 0,
        ])->save();

        $user->notify(new OtpCodeNotification($code, self::EXPIRES_MINUTES));
    }

    /**
     * Check the submitted code and mark the account as verified on success.
     */
    public function verify(User $user, string $code): bool
    {
        if (! $user->otp_code || ! $user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return false;
        }

        if ($user->otp_attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (! Hash::check($code, $user->otp_code)) {
            $user->forceFill(['otp_attempts' => $user->otp_attempts + 1])->save();

            return false;
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
            'last_login_at' => now(),
        ])->save();

        return true;
    }

    /**
     * Whether a usable code is already waiting for this account.
     */
    public function hasPendingCode(User $user): bool
    {
        return $user->otp_code !== null
            && $user->otp_expires_at !== null
            && $user->otp_expires_at->isFuture()
            && $user->otp_attempts < self::MAX_ATTEMPTS;
    }
}
