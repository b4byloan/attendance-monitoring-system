<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Models\Announcement;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubAdminController extends Controller
{
    use LoadsNotificationBell;

    public function dashboard(Request $request): View
    {
        $today = now()->toDateString();

        $sections = Section::query()
            ->where('adviser_id', $request->user()->id)
            ->withCount('students')
            ->orderBy('name')
            ->get();

        $sectionIds = $sections->pluck('id');

        $presentBySection = AttendanceRecord::query()
            ->join('students', 'students.id', '=', 'attendance_records.student_id')
            ->whereIn('students.section_id', $sectionIds)
            ->where('attendance_records.type', 'check_in')
            ->whereDate('attendance_records.attendance_date', $today)
            ->selectRaw('students.section_id, COUNT(DISTINCT attendance_records.student_id) as present_count')
            ->groupBy('students.section_id')
            ->pluck('present_count', 'section_id');

        $sections->each(function (Section $section) use ($presentBySection): void {
            $section->present_today = (int) $presentBySection->get($section->id, 0);
        });

        $studentIds = Student::query()->whereIn('section_id', $sectionIds)->pluck('id');

        $recentActivity = AttendanceRecord::with('student')
            ->whereIn('student_id', $studentIds)
            ->orderByDesc('scanned_at')
            ->limit(10)
            ->get();

        $pendingCorrections = AttendanceCorrection::where('requested_by', $request->user()->id)
            ->where('status', 'pending')
            ->count();

        $latestAnnouncements = Announcement::visible()
            ->orderByDesc('is_pinned')
            ->orderByDesc('publish_date')
            ->limit(5)
            ->get();

        return view('sub-admin.dashboard', [
            'sections' => $sections,
            'totalStudents' => (int) $sections->sum('students_count'),
            'presentTodayCount' => (int) $presentBySection->sum(),
            'recentActivity' => $recentActivity,
            'pendingCorrections' => $pendingCorrections,
            'latestAnnouncements' => $latestAnnouncements,
        ])->with($this->notificationBellData());
    }
}
