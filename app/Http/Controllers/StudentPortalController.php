<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    use LoadsNotificationBell;

    public function dashboard(Request $request)
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

        return view('student.dashboard', compact('student', 'todayCheckIn', 'todayCheckOut', 'recent'))
            ->with($this->notificationBellData());
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
            ->paginate(20)->withQueryString();

        return view('student.attendance', compact('student', 'records'))->with($this->notificationBellData());
    }

    public function profile(Request $request)
    {
        $student = $request->user()->student;

        if (! $student) {
            return view('student.no-account')->with($this->notificationBellData());
        }

        $student->load('section.adviser');

        return view('student.profile', compact('student'))->with($this->notificationBellData());
    }

    public function accountSettings(Request $request)
    {
        return view('student.account-settings')->with($this->notificationBellData());
    }

    public function updateAccount(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'current_password' => ['required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $validated['name'];

        if (! empty($validated['new_password'])) {
            $user->password = $validated['new_password'];
        }

        $user->save();

        return back()->with('success', 'Account updated.');
    }
}
