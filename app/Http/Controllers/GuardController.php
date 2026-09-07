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
        $todaysScans = AttendanceRecord::with('student.section')
            ->where('recorded_by', $request->user()->id)
            ->where('attendance_date', now()->toDateString())
            ->orderByDesc('scanned_at')
            ->get();

        $checkInsToday = $todaysScans->where('type', 'check_in')->count();
        $checkOutsToday = $todaysScans->where('type', 'check_out')->count();

        return view('guard.dashboard', compact('todaysScans', 'checkInsToday', 'checkOutsToday'))
            ->with($this->notificationBellData());
    }
}