<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Models\Announcement;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\Section;
use Illuminate\Http\Request;

class SubAdminController extends Controller
{
    use LoadsNotificationBell;

    public function dashboard(Request $request)
    {
        $section = Section::with('students')->where('adviser_id', $request->user()->id)->first();
        $studentIds = $section?->students->pluck('id') ?? collect();
        $today = now()->toDateString();

        $todaysRecords = AttendanceRecord::whereIn('student_id', $studentIds)
            ->where('attendance_date', $today)
            ->get()
            ->groupBy('student_id');

        $students = ($section?->students ?? collect())->map(function ($student) use ($todaysRecords) {
            $records = $todaysRecords->get($student->id, collect());
            $student->today_status = match (true) {
                $records->contains('type', 'check_in') && $records->contains('type', 'check_out') => 'checked_out',
                $records->contains('type', 'check_in') => 'checked_in',
                default => 'not_yet',
            };
            return $student;
        });

        $presentTodayCount = $students->whereIn('today_status', ['checked_in', 'checked_out'])->count();

        $recentActivity = AttendanceRecord::with('student')
            ->whereIn('student_id', $studentIds)
            ->orderByDesc('scanned_at')
            ->limit(10)
            ->get();

        $pendingCorrections = AttendanceCorrection::where('requested_by', $request->user()->id)
            ->where('status', 'pending')
            ->count();

        $latestAnnouncements = Announcement::visible()->orderByDesc('is_pinned')->orderByDesc('publish_date')->limit(5)->get();

        return view('sub-admin.dashboard', compact(
            'section', 'students', 'presentTodayCount', 'recentActivity', 'pendingCorrections', 'latestAnnouncements'
        ))->with($this->notificationBellData());
    }
}