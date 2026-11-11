<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Models\ActivityLog;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\User;
use App\Notifications\AttendanceCorrectionRequestedNotification;
use App\Notifications\AttendanceCorrectionReviewedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceCorrectionController extends Controller
{
    use LoadsNotificationBell;

    public function index(Request $request)
    {
        $user = $request->user();
        $canReview = $user->isAdmin() || $user->isSubAdmin();

        if ($canReview) {
            $query = AttendanceCorrection::with('student', 'requestedBy', 'reviewedBy', 'attendanceRecord');

            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }
        } else {
            $query = AttendanceCorrection::with('student', 'reviewedBy', 'attendanceRecord')
                ->where('requested_by', $user->id);
        }

        $corrections = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $pendingCount = $canReview ? AttendanceCorrection::where('status', 'pending')->count() : null;

        return view('attendance-corrections.index', compact('corrections', 'pendingCount', 'canReview'))
            ->with($this->notificationBellData());
    }

    public function create(Request $request)
    {
        $students = collect();

        if ($search = $request->input('search')) {
            $students = Student::where('lrn', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->limit(10)
                ->get();
        }

        return view('attendance-corrections.create', compact('students'))->with($this->notificationBellData());
    }

    public function createForRecord(AttendanceRecord $attendanceRecord)
    {
        $attendanceRecord->load('student');

        return view('attendance-corrections.form', compact('attendanceRecord'))->with($this->notificationBellData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'attendance_record_id' => ['required', 'exists:attendance_records,id'],
            'requested_check_in_time' => ['nullable', 'date_format:H:i'],
            'requested_check_out_time' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if (! $validated['requested_check_in_time'] && ! $validated['requested_check_out_time']) {
            return back()->withErrors(['requested_check_in_time' => 'Provide at least one corrected time.'])->withInput();
        }

        $attendanceRecord = AttendanceRecord::findOrFail($validated['attendance_record_id']);

        $duplicatePending = AttendanceCorrection::where('attendance_record_id', $attendanceRecord->id)
            ->where('status', 'pending')
            ->exists();

        if ($duplicatePending) {
            return back()->withErrors(['duplicate' => 'There is already a pending correction request for this attendance record.']);
        }

        $correction = AttendanceCorrection::create([
            'attendance_record_id' => $attendanceRecord->id,
            'student_id' => $attendanceRecord->student_id,
            'requested_check_in_time' => $validated['requested_check_in_time'] ?? null,
            'requested_check_out_time' => $validated['requested_check_out_time'] ?? null,
            'reason' => $validated['reason'],
            'requested_by' => $request->user()->id,
        ]);

        User::where('role', 'admin')->where('is_active', true)->get()
            ->each(fn ($admin) => $admin->notify(new AttendanceCorrectionRequestedNotification(
                $attendanceRecord->student->full_name,
                $correction->id
            )));

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'requested_attendance_correction',
            'description' => "Requested correction #{$correction->id} for {$attendanceRecord->student->full_name}.",
            'subject_type' => 'AttendanceCorrection',
            'subject_id' => $correction->id,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('attendance-corrections.index')->with('success', 'Correction request submitted.');
    }

    public function approve(Request $request, AttendanceCorrection $correction)
    {
        if ($correction->status !== 'pending') {
            return back()->withErrors(['status' => 'This request has already been reviewed.']);
        }

        if ($correction->requested_by === $request->user()->id) {
            return back()->withErrors(['status' => 'You cannot approve your own correction request — another Admin or Teacher needs to review it.']);
        }

        $date = $correction->attendanceRecord->attendance_date;

        if ($correction->requested_check_in_time) {
            AttendanceRecord::updateOrCreate(
                ['student_id' => $correction->student_id, 'type' => 'check_in', 'attendance_date' => $date],
                ['scanned_at' => Carbon::parse($date->toDateString().' '.$correction->requested_check_in_time), 'recorded_by' => $request->user()->id]
            );
        }

        if ($correction->requested_check_out_time) {
            AttendanceRecord::updateOrCreate(
                ['student_id' => $correction->student_id, 'type' => 'check_out', 'attendance_date' => $date],
                ['scanned_at' => Carbon::parse($date->toDateString().' '.$correction->requested_check_out_time), 'recorded_by' => $request->user()->id]
            );
        }

        $correction->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $correction->requestedBy?->notify(new AttendanceCorrectionReviewedNotification(
            $correction->student->full_name,
            'approved'
        ));

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'approved_attendance_correction',
            'description' => "Approved correction #{$correction->id} for {$correction->student->full_name} ({$date->format('M j, Y')}).",
        ]);

        return back()->with('success', 'Correction approved and attendance updated.');
    }

    public function reject(Request $request, AttendanceCorrection $correction)
    {
        if ($correction->status !== 'pending') {
            return back()->withErrors(['status' => 'This request has already been reviewed.']);
        }

        if ($correction->requested_by === $request->user()->id) {
            return back()->withErrors(['status' => 'You cannot reject your own correction request — another Admin or Teacher needs to review it.']);
        }

        $validated = $request->validate([
            'admin_remarks' => ['required', 'string', 'max:1000'],
        ]);

        $correction->update([
            'status' => 'rejected',
            'admin_remarks' => $validated['admin_remarks'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $correction->requestedBy?->notify(new AttendanceCorrectionReviewedNotification(
            $correction->student->full_name,
            'rejected',
            $validated['admin_remarks']
        ));

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'rejected_attendance_correction',
            'description' => "Rejected correction #{$correction->id} for {$correction->student->full_name}: {$validated['admin_remarks']}",
        ]);

        return back()->with('success', 'Correction rejected.');
    }
}
