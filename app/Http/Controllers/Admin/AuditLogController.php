<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\LoadsNotificationBell;

class AuditLogController extends Controller
{
    use LoadsNotificationBell;
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%");
            });
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        $logs = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        $users = User::orderBy('name')->get();
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-logs.index', compact('logs', 'users', 'actions'))->with($this->notificationBellData());
    }

    public function show(ActivityLog $log)
    {
        $log->load('user');

        return view('admin.audit-logs.show', compact('log'))->with($this->notificationBellData());
    }
}