<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Models\AttendanceRecord;
use App\Models\GuardianLinkRequest;
use App\Notifications\GuardianLinkRespondedNotification;
use App\Services\QrCodeService;
use App\Services\SubjectAttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentPortalController extends Controller
{
    use LoadsNotificationBell;

    public function dashboard(Request $request, QrCodeService $qrCode)
    {
        $student = $request->user()->student;

        if (! $student) {
            return view('student.no-account')->with($this->notificationBellData());
        }

        $today = now()->toDateString();

        $todayCheckIn = AttendanceRecord::where('student_id', $student->id)
            ->where('type', 'check_in')->where('attendance_date', $today)->first();

        $todayCheckOut = AttendanceRecord::where('student_id', $student->id)
            ->where('type', 'check_out')->where('attendance_date', $today)->first();

        $recent = AttendanceRecord::where('student_id', $student->id)
            ->orderByDesc('scanned_at')->limit(10)->get();

        return view('student.dashboard', [
            'student' => $student,
            'todayCheckIn' => $todayCheckIn,
            'todayCheckOut' => $todayCheckOut,
            'recent' => $recent,
            'qrDataUri' => $qrCode->dataUri($student->qr_code),
            'pendingLinkRequests' => GuardianLinkRequest::query()
                ->where('student_id', $student->id)
                ->where('status', 'pending')
                ->count(),
        ])->with($this->notificationBellData());
    }

    public function attendance(Request $request, SubjectAttendanceService $subjectAttendance)
    {
        $student = $request->user()->student;

        if (! $student) {
            return view('student.no-account')->with($this->notificationBellData());
        }

        $query = AttendanceRecord::where('student_id', $student->id);

        if ($request->filled('date')) {
            $query->where('attendance_date', $request->input('date'));
        } elseif ($request->filled('month')) {
            $query->where('attendance_date', 'like', $request->input('month').'-%');
        }

        $records = $query->orderByDesc('attendance_date')->orderByDesc('scanned_at')
            ->paginate(10)->withQueryString();

        $subjectAttendance = $subjectAttendance->forStudent(
            $student,
            $request->input('date'),
            $request->input('month'),
        );

        return view('student.attendance', [
            'student' => $student,
            'records' => $records,
            'subjectRows' => $subjectAttendance['rows'],
            'subjectSessions' => $subjectAttendance['sessions'],
        ])->with($this->notificationBellData());
    }

    public function acceptLinkRequest(Request $request, GuardianLinkRequest $linkRequest)
    {
        return $this->respondToLinkRequest($request, $linkRequest, true);
    }

    public function denyLinkRequest(Request $request, GuardianLinkRequest $linkRequest)
    {
        return $this->respondToLinkRequest($request, $linkRequest, false);
    }

    private function respondToLinkRequest(Request $request, GuardianLinkRequest $linkRequest, bool $accepted)
    {
        $student = $request->user()->student;

        abort_unless($student && $linkRequest->student_id === $student->id, 403);
        abort_unless($linkRequest->status === 'pending', 400, 'This link request has already been answered.');

        DB::transaction(function () use ($linkRequest, $student, $accepted): void {
            $linkRequest->update([
                'status' => $accepted ? 'accepted' : 'denied',
                'responded_at' => now(),
            ]);

            if ($accepted) {
                $linkRequest->guardian->students()->syncWithoutDetaching([$student->id]);
            }
        });

        $linkRequest->guardian->user?->notify(
            new GuardianLinkRespondedNotification($student->full_name, $accepted)
        );

        return back()->with('success', $accepted
            ? 'Guardian link request accepted.'
            : 'Guardian link request denied.');
    }
}
