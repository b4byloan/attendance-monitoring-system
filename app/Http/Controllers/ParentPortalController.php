<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\GuardianLinkRequest;
use App\Models\Section;
use App\Models\Student;
use App\Notifications\GuardianLinkRequestedNotification;
use App\Services\SubjectAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            ->get();

        return view('parent.dashboard', compact(
            'student', 'students', 'todayCheckIn', 'todayCheckOut', 'totalPresentDays', 'recent', 'announcements'
        ))->with($this->notificationBellData());
    }

    public function attendance(Request $request, Student $student, SubjectAttendanceService $subjectAttendance)
    {
        $this->authorizeAccess($student);

        $students = $request->user()->guardian->students;

        $query = AttendanceRecord::where('student_id', $student->id);

        if ($request->filled('date')) {
            $query->where('attendance_date', $request->input('date'));
        } elseif ($request->filled('month')) {
            $query->where('attendance_date', 'like', $request->input('month').'-%');
        }

        $records = $query->orderByDesc('attendance_date')
            ->orderByDesc('scanned_at')
            ->paginate(10)
            ->withQueryString();

        $subjectAttendance = $subjectAttendance->forStudent(
            $student,
            $request->input('date'),
            $request->input('month'),
        );

        return view('parent.attendance', [
            'student' => $student,
            'students' => $students,
            'records' => $records,
            'subjectRows' => $subjectAttendance['rows'],
            'subjectSessions' => $subjectAttendance['sessions'],
        ])->with($this->notificationBellData());
    }

    public function linkStudent(Request $request)
    {
        $guardian = $request->user()->guardian;

        $requests = $guardian
            ? GuardianLinkRequest::with('student.section')
                ->where('guardian_id', $guardian->id)
                ->latest()
                ->get()
            : collect();

        $sections = Section::query()
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        return view('parent.link-student', [
            'guardian' => $guardian,
            'requests' => $requests,
            'sections' => $sections,
            'gradeLevels' => $sections->pluck('grade_level')
                ->filter(fn ($level) => in_array($level, Section::GRADE_LEVELS, true))
                ->unique()
                ->sort()
                ->values(),
        ])->with($this->notificationBellData());
    }

    public function storeLinkRequest(Request $request)
    {
        $guardian = $request->user()->guardian;

        abort_unless($guardian, 403, 'No guardian profile is linked to this account.');

        $validated = $request->validate([
            'lrn' => ['required', 'digits:12'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', Rule::exists('sections', 'grade_level')],
            'section_id' => [
                'required',
                'integer',
                Rule::exists('sections', 'id')->where('grade_level', $request->input('grade_level')),
            ],
        ]);

        $student = Student::matchSchoolRecord(
            lrn: $validated['lrn'],
            firstName: $validated['first_name'],
            lastName: $validated['last_name'],
            sectionId: (int) $validated['section_id'],
            gradeLevel: $validated['grade_level'],
            middleName: $validated['middle_name'] ?? '',
        );

        if (! $student) {
            return back()->withInput()->withErrors([
                'lrn' => 'The details do not match any student record. Check the LRN, name including middle name, grade level, and section.',
            ]);
        }

        if (! $student->user) {
            return back()->withInput()->withErrors([
                'lrn' => 'This student has not registered a portal account yet. Ask them to register first so they can approve the link.',
            ]);
        }

        if ($guardian->students()->whereKey($student->id)->exists()) {
            return back()->withInput()->withErrors(['lrn' => 'This student is already linked to your account.']);
        }

        $linkRequest = GuardianLinkRequest::firstOrNew([
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
        ]);

        if ($linkRequest->exists && $linkRequest->status === 'pending') {
            return back()->withInput()->withErrors(['lrn' => 'A link request for this student is already waiting for approval.']);
        }

        $linkRequest->fill(['status' => 'pending', 'responded_at' => null])->save();

        $student->user->notify(new GuardianLinkRequestedNotification(
            guardianName: trim($guardian->first_name.' '.$guardian->last_name),
            relationship: $guardian->relationship_to_student ?: 'Guardian',
            linkRequestId: $linkRequest->id,
        ));

        return redirect()->route('parent.link-student.index')
            ->with('success', 'Link request sent. The student will approve or deny it from their portal.');
    }

    public function destroyLinkRequest(Request $request, GuardianLinkRequest $linkRequest): RedirectResponse
    {
        $guardian = $request->user()->guardian;

        abort_unless($guardian && $linkRequest->guardian_id === $guardian->id, 403);

        $linkRequest->delete();

        return redirect()->route('parent.link-student.index')
            ->with('success', 'Link request deleted.');
    }

    public function destroyAllLinkRequests(Request $request): RedirectResponse
    {
        $guardian = $request->user()->guardian;

        abort_unless($guardian, 403, 'No guardian profile is linked to this account.');

        GuardianLinkRequest::where('guardian_id', $guardian->id)->delete();

        return redirect()->route('parent.link-student.index')
            ->with('success', 'All link requests deleted.');
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
            ->paginate(10);

        return view('parent.announcements', compact('announcements'))
            ->with($this->notificationBellData());
    }
}
