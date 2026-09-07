<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement;
use App\Http\Controllers\Concerns\LoadsNotificationBell;

class ParentPortalController extends Controller
{
     use LoadsNotificationBell;
    public function index(Request $request)
    {
        $guardian = $request->user()->guardian;
        $students = $guardian?->students ?? collect();

        if ($students->isEmpty()) {
            return view('parent.no-students');
        }

        return redirect()->route('parent.students.dashboard', $students->first());
    }

    public function dashboard(Request $request, Student $student)
    {
        $this->authorizeAccess($student);

        $students = $request->user()->guardian->students;
        $today = now()->toDateString();

        $todayCheckIn = AttendanceRecord::where('student_id', $student->id)
            ->where('type', 'check_in')
            ->where('attendance_date', $today)
            ->first();

        $todayCheckOut = AttendanceRecord::where('student_id', $student->id)
            ->where('type', 'check_out')
            ->where('attendance_date', $today)
            ->first();

        $totalPresentDays = AttendanceRecord::where('student_id', $student->id)
            ->where('type', 'check_in')
            ->distinct('attendance_date')
            ->count('attendance_date');

        $recent = AttendanceRecord::where('student_id', $student->id)
            ->orderByDesc('scanned_at')
            ->limit(10)
            ->get();

        $announcements = Announcement::visible()
            ->where(function ($q) use ($student) {
                $q->whereNull('section_id')->orWhere('section_id', $student->section_id);
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('publish_date')
            ->limit(5)
            ->get();

        return view('parent.dashboard', compact(
            'student', 'students', 'todayCheckIn', 'todayCheckOut', 'totalPresentDays', 'recent', 'announcements'
        ))->with($this->notificationBellData());
    }

    public function attendance(Request $request, Student $student)
    {
        $this->authorizeAccess($student);

        $students = $request->user()->guardian->students;

        $query = AttendanceRecord::where('student_id', $student->id);

        if ($request->filled('date')) {
            $query->where('attendance_date', $request->input('date'));
        } elseif ($request->filled('month')) {
            $query->where('attendance_date', 'like', $request->input('month') . '-%');
        }

        $records = $query->orderByDesc('attendance_date')
            ->orderByDesc('scanned_at')
            ->paginate(20)
            ->withQueryString();

        return view('parent.attendance', compact('student', 'students', 'records'));
    }

    private function authorizeAccess(Student $student): void
    {
        $guardian = Auth::user()?->guardian;

        if (! $guardian || ! $guardian->students->contains('id', $student->id)) {
            abort(403, "You are not authorized to view this student's records.");
        }
    }

        public function announcements(Request $request)
    {
        $guardian = $request->user()->guardian;
        $students = $guardian?->students ?? collect();
        $sectionIds = $students->pluck('section_id')->filter()->unique();

        $announcements = Announcement::visible()
            ->where(function ($q) use ($sectionIds) {
                $q->whereNull('section_id')->orWhereIn('section_id', $sectionIds);
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('publish_date')
            ->paginate(20);

        return view('parent.announcements', compact('announcements'));
    }
}