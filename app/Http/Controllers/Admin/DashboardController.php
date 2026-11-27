<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsNotificationBell;

    public function index(): View
    {
        $today = now()->toDateString();

        $totalStudents = Student::count();
        $totalSections = Section::count();
        $totalParents = Guardian::count();
        $activeStudents = Student::where('status', 'active')->count();

        $presentTodayIds = AttendanceRecord::query()
            ->where('type', 'check_in')
            ->whereDate('attendance_date', $today)
            ->whereHas('student', fn ($query) => $query->where('status', 'active'))
            ->pluck('student_id')
            ->unique();

        $presentToday = $presentTodayIds->count();
        $absentToday = max($activeStudents - $presentToday, 0);
        $presentRate = $activeStudents > 0 ? (int) round(($presentToday / $activeStudents) * 100) : 0;

        $totalAttendanceToday = AttendanceRecord::whereDate('attendance_date', $today)->count();

        $dailyLabels = [];
        $dailyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dailyLabels[] = $date->format('M j');
            $dailyData[] = AttendanceRecord::where('type', 'check_in')
                ->whereDate('attendance_date', $date->toDateString())
                ->count();
        }

        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $monthlyData[] = AttendanceRecord::where('type', 'check_in')
                ->whereBetween('attendance_date', [
                    $month->copy()->startOfMonth()->toDateString(),
                    $month->copy()->endOfMonth()->toDateString(),
                ])
                ->count();
        }

        $presentStudents = Student::with('section')->whereIn('id', $presentTodayIds)->get();
        $byGradeLevel = $presentStudents->groupBy(fn ($student) => $student->section->grade_level ?? 'Unassigned')->map->count();
        $bySection = $presentStudents->groupBy(fn ($student) => $student->section->name ?? 'Unassigned')->map->count();
        $latestAnnouncements = Announcement::visible()->orderByDesc('is_pinned')->orderByDesc('publish_date')->limit(5)->get();

        return view('admin.dashboard', array_merge(
            compact(
                'totalStudents',
                'activeStudents',
                'presentToday',
                'absentToday',
                'presentRate',
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
            $this->notificationBellData()
        ));
    }
}
