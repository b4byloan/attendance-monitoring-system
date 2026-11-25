<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class GuardController extends Controller
{
    use LoadsNotificationBell;

    public function dashboard(Request $request)
    {
        $query = AttendanceRecord::with('student.section')
            ->where('recorded_by', $request->user()->id)
            ->where('attendance_date', now()->toDateString());

        $checkInsToday = (clone $query)->where('type', 'check_in')->count();
        $checkOutsToday = (clone $query)->where('type', 'check_out')->count();

        $todaysScans = $query->orderByDesc('scanned_at')->paginate(10);

        return view('guard.dashboard', compact('todaysScans', 'checkInsToday', 'checkOutsToday'))
            ->with($this->notificationBellData());
    }
}
