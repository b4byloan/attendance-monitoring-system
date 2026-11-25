<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Models\AttendanceRecord;
use App\Models\GuardianLinkRequest;
use App\Notifications\GuardianLinkRespondedNotification;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
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

    public function attendance(Request $request)
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

        return view('student.attendance', compact('student', 'records'))->with($this->notificationBellData());
    }

    public function linkRequests(Request $request)
    {
        $student = $request->user()->student;

        if (! $student) {
            return view('student.no-account')->with($this->notificationBellData());
        }

        $requests = GuardianLinkRequest::with('guardian')
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        return view('student.link-requests', compact('student', 'requests'))->with($this->notificationBellData());
    }

    public function acceptLinkRequest(Request $request, GuardianLinkRequest $linkRequest)
    {
        return $this->respondToLinkRequest($request, $linkRequest, true);
    }

    public function denyLinkRequest(Request $request, GuardianLinkRequest $linkRequest)
    {
        return $this->respondToLinkRequest($request, $linkRequest, false);
    }

    public function destroyLinkRequest(Request $request, GuardianLinkRequest $linkRequest): RedirectResponse
    {
        $student = $request->user()->student;

        abort_unless($student && $linkRequest->student_id === $student->id, 403);

        $linkRequest->delete();

        return redirect()->route('student.link-requests.index')
            ->with('success', 'Link request deleted.');
    }

    public function destroyAllLinkRequests(Request $request): RedirectResponse
    {
        $student = $request->user()->student;

        abort_unless($student, 403);

        GuardianLinkRequest::where('student_id', $student->id)->delete();

        return redirect()->route('student.link-requests.index')
            ->with('success', 'All link requests deleted.');
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
