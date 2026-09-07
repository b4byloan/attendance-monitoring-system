<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\Announcement;
use App\Http\Controllers\Concerns\LoadsNotificationBell;

class DashboardController extends Controller
{
    use LoadsNotificationBell;
    public function index()
    {
        $today = now()->toDateString();

        $totalStudents = Student::count();
        $totalSections = Section::count();
        $totalParents = Guardian::count();

        $presentTodayIds = AttendanceRecord::where('type', 'check_in')
            ->where('attendance_date', $today)
            ->pluck('student_id')
            ->unique();

        $presentToday = $presentTodayIds->count();
        $activeStudents = Student::where('status', 'active')->count();
        $absentToday = max($activeStudents - $presentToday, 0);

        $totalAttendanceToday = AttendanceRecord::where('attendance_date', $today)->count();

        $dailyLabels = [];
        $dailyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dailyLabels[] = $date->format('M j');
            $dailyData[] = AttendanceRecord::where('type', 'check_in')
                ->where('attendance_date', $date->toDateString())
                ->count();
        }

        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $monthlyData[] = AttendanceRecord::where('type', 'check_in')
                ->where('attendance_date', 'like', $month->format('Y-m') . '-%')
                ->count();
        }

        $presentStudents = Student::with('section')->whereIn('id', $presentTodayIds)->get();
        $byGradeLevel = $presentStudents->groupBy(fn ($s) => $s->section->grade_level ?? 'Unassigned')->map->count();
        $bySection = $presentStudents->groupBy(fn ($s) => $s->section->name ?? 'Unassigned')->map->count();
        $latestAnnouncements = Announcement::visible()->orderByDesc('is_pinned')->orderByDesc('publish_date')->limit(5)->get();
        $notificationData = $this->notificationBellData();

        return view('admin.dashboard', array_merge(
    compact(
        'totalStudents',
        'presentToday',
        'absentToday',
        'totalSections',
        'totalParents',
        'totalAttendanceToday',
        'dailyLabels',
        'dailyData',
        'monthlyLabels',
        'monthlyData',
        'byGradeLevel',
        'bySection',
        'latestAnnouncements'
    ),
    $notificationData
));
    }
}