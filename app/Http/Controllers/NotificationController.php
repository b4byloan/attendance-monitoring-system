<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function readAndRedirect(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        return redirect($notification->data['url'] ?? route('notifications.index'));
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}