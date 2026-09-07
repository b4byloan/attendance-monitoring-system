<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Jobs\SendAttendanceSms;
use App\Models\SmsNotification;
use App\Http\Controllers\Concerns\LoadsNotificationBell;

class ScannerController extends Controller
{
    use LoadsNotificationBell;
    public function index()
    {
        return view('scanner.index')->with($this->notificationBellData());
    }

    public function verify(Request $request)
    {
        
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        $student = Student::with('section')
            ->where('qr_code', $validated['token'])
            ->first();

        if (! $student) {
            return response()->json(['found' => false]);
        }

        $studentInfo = [
            'full_name' => $student->full_name,
            'lrn' => $student->lrn,
            'section' => $student->section?->name,
            'status' => $student->status,
            'photo_url' => $student->photo_path ? asset('storage/' . $student->photo_path) : null,
        ];
        if ($student->status !== 'active') {
            return response()->json([
                'found' => true,
                'active' => false,
                'student' => $studentInfo,
            ]);
        }

        if ($this->hasRecentScan($student->id)) {
            return response()->json([
                'found' => true,
                'active' => true,
                'attendance' => 'recent_duplicate',
                'student' => $studentInfo,
            ]);
        }

        $today = now()->toDateString();

        $hasCheckIn = AttendanceRecord::where('student_id', $student->id)
            ->where('type', 'check_in')
            ->where('attendance_date', $today)
            ->exists();

        $hasCheckOut = AttendanceRecord::where('student_id', $student->id)
            ->where('type', 'check_out')
            ->where('attendance_date', $today)
            ->exists();

        if ($hasCheckIn && $hasCheckOut) {
            return response()->json([
                'found' => true,
                'active' => true,
                'attendance' => 'already_completed',
                'student' => $studentInfo,
            ]);
        }

        $type = $hasCheckIn ? 'check_out' : 'check_in';
        

        try {
            $record = AttendanceRecord::create([
                'student_id' => $student->id,
                'type' => $type,
                'recorded_by' => $request->user()->id,
                'source' => 'online',
                'scanned_at' => now(),
                'attendance_date' => $today,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Final safety net - the unique constraint on
            // (student_id, type, attendance_date) blocks a genuine
            // duplicate even if two scans land at nearly the same instant.
            return response()->json([
                'found' => true,
                'active' => true,
                'attendance' => 'error',
                'student' => $studentInfo,
            ]);
        }
            
        $this->dispatchAttendanceSms($record, $student);
        
        return response()->json([
            'found' => true,
            'active' => true,
            'attendance' => $type,
            'time' => $record->scanned_at->format('h:i A'),
            'student' => $studentInfo,
        ]);
        
           
    } 
    public function directory()
    {
        $students = Student::with('section')->get()->map(fn ($s) => [
            'qr_code' => $s->qr_code,
            'full_name' => $s->full_name,
            'section' => $s->section?->name,
            'status' => $s->status,
        ]);

        return response()->json($students);
    }
        private function hasRecentScan(int $studentId): bool
    {
        return AttendanceRecord::where('student_id', $studentId)
            ->where('scanned_at', '>=', now()->subMinutes(2))
            ->exists();
    }
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'qr_code' => ['required', 'string'],
            'scanned_at' => ['required', 'date'],
            'attendance_date' => ['required', 'date'],
        ]);

        $student = Student::where('qr_code', $validated['qr_code'])->first();

        if (! $student) {
            return response()->json(['status' => 'error', 'message' => 'Student not found.']);
        }

        if ($student->status !== 'active') {
            return response()->json(['status' => 'error', 'message' => 'Student is not active.']);
        }

        if ($this->hasRecentScan($student->id)) {
            return response()->json(['status' => 'duplicate']);
        }

        $hasCheckIn = AttendanceRecord::where('student_id', $student->id)
            ->where('type', 'check_in')
            ->where('attendance_date', $validated['attendance_date'])
            ->exists();

        $hasCheckOut = AttendanceRecord::where('student_id', $student->id)
            ->where('type', 'check_out')
            ->where('attendance_date', $validated['attendance_date'])
            ->exists();

        if ($hasCheckIn && $hasCheckOut) {
            return response()->json(['status' => 'duplicate']);
        }

        $type = $hasCheckIn ? 'check_out' : 'check_in';

        try {
            $record = AttendanceRecord::create([
                'student_id' => $student->id,
                'type' => $type,
                'recorded_by' => $request->user()->id,
                'source' => 'offline_sync',
                'scanned_at' => $validated['scanned_at'],
                'attendance_date' => $validated['attendance_date'],
                'synced_at' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // The unique constraint caught a race we missed above -
            // e.g. two devices syncing the same student seconds apart.
            return response()->json(['status' => 'duplicate']);
        }

        $this->dispatchAttendanceSms($record, $student);

        return response()->json(['status' => 'synced', 'type' => $type]);
    }
    
     private function dispatchAttendanceSms($record, Student $student): void
    {
        if (! \App\Models\Setting::current()->sms_enabled) {
            $record->update(['sms_status' => 'not_applicable']);
            return;
        }
        $guardians = $student->guardians->where('status', 'active');

        if ($guardians->isEmpty()) {
            $record->update(['sms_status' => 'not_applicable']);
            return;
        }

        $actionText = $record->type === 'check_in' ? 'checked in' : 'checked out';
        $message = "{$student->full_name} {$actionText} at {$record->scanned_at->format('h:i A, F j, Y')}.";

        foreach ($guardians as $guardian) {
            $notification = SmsNotification::create([
                'attendance_record_id' => $record->id,
                'guardian_id' => $guardian->id,
                'phone_number' => $guardian->phone_number,
                'message' => $message,
                'status' => 'pending',
            ]);

            SendAttendanceSms::dispatch($notification);
        }
    }
    
}