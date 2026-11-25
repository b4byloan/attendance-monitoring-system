<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Concerns\LoadsSectionOverview;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubAdminController extends Controller
{
    use LoadsNotificationBell;
    use LoadsSectionOverview;

    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $sections = $this->sectionsWithTodayCounts($user);

        $studentIds = Student::query()->whereIn('section_id', $sections->pluck('id'))->pluck('id');

        $recentActivity = AttendanceRecord::with('student')
            ->whereIn('student_id', $studentIds)
            ->orderByDesc('scanned_at')
            ->limit(10)
            ->get();

        $latestAnnouncements = Announcement::visible()
            ->orderByDesc('is_pinned')
            ->orderByDesc('publish_date')
            ->limit(5)
            ->get();

        return view('sub-admin.dashboard', [
            'sections' => $sections,
            'totalStudents' => (int) $sections->sum('students_count'),
            'presentTodayCount' => (int) $sections->sum('present_today'),
            'recentActivity' => $recentActivity,
            'latestAnnouncements' => $latestAnnouncements,
        ])->with($this->notificationBellData());
    }
}
