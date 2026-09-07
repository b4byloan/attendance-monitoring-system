<?php

namespace App\Http\Controllers\Concerns;

trait LoadsNotificationBell
{
    protected function notificationBellData(): array
    {
        $user = auth()->user();

        return [
            'unreadCount' => $user->unreadNotifications()->count(),
            'recentNotifications' => $user->notifications()->limit(8)->get(),
        ];
    }
}