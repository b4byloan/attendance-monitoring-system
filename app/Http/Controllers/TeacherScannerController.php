<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendanceRecord;
use App\Models\User;
use App\Services\AttendanceNotifier;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherScannerController extends Controller
{
    use LoadsNotificationBell;

    public function index(Request $request): View
    {
        return view('scanner.subject', [
            'subjects' => $this->assignedSubjects($request->user()),
        ])->with($this->notificationBellData());
    }

    public function verify(Request $request, AttendanceNotifier $notifier): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where('status', 'active'),
            ],
        ]);

        $user = $request->user();
        $subject = Subject::findOrFail($validated['subject_id']);

        if (! $user->isAdmin() && ! $user->subjects()->whereKey($subject->id)->exists()) {
            return response()->json([
                'found' => false,
                'message' => 'You are not assigned to this subject.',
            ], 403);
        }

        $student = Student::findByQrCode($validated['token'])?->loadMissing('section');

        if (! $student) {
            return response()->json(['found' => false]);
        }

        $studentInfo = [
            'full_name' => $student->full_name,
            'lrn' => $student->lrn,
            'section' => $student->section?->name,
            'status' => $student->status,
            'photo_url' => $student->photo_path ? asset('storage/'.$student->photo_path) : null,
        ];

        if ($student->status !== 'active') {
            return response()->json([
                'found' => true,
                'active' => false,
                'student' => $studentInfo,
            ]);
        }

        if (! $this->canAccessStudent($user, $student)) {
            return response()->json([
                'found' => true,
                'active' => true,
                'assigned' => false,
                'student' => $studentInfo,
                'message' => 'This student is not in one of your assigned sections.',
            ], 403);
        }

        $today = now()->toDateString();

        if (SubjectAttendanceRecord::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('attendance_date', $today)
            ->exists()) {
            return response()->json([
                'found' => true,
                'active' => true,
                'attendance' => 'already_recorded',
                'student' => $studentInfo,
                'subject' => $subject->name,
            ]);
        }

        try {
            $record = SubjectAttendanceRecord::create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'recorded_by' => $user->id,
                'status' => 'present',
                'attendance_date' => $today,
                'scanned_at' => now(),
            ]);
        } catch (QueryException) {
            return response()->json([
                'found' => true,
                'active' => true,
                'attendance' => 'already_recorded',
                'student' => $studentInfo,
                'subject' => $subject->name,
            ]);
        }

        $notifier->notifyGuardians($student, [
            'date' => $record->attendance_date->format('F j, Y'),
            'time' => $record->scanned_at->format('h:i A'),
            'subject' => $subject->name,
            'status' => 'Present (class attendance)',
            'recorded_by' => $user->name,
        ]);

        return response()->json([
            'found' => true,
            'active' => true,
            'attendance' => 'present',
            'subject' => $subject->name,
            'time' => $record->scanned_at->format('h:i A'),
            'student' => $studentInfo,
        ]);
    }

    /** @return Collection<int, Subject> */
    private function assignedSubjects(User $user): Collection
    {
        $query = Subject::query()->where('status', 'active')->orderBy('name');

        if (! $user->isAdmin()) {
            $query->whereHas('teachers', fn ($query) => $query->whereKey($user->id));
        }

        return $query->get();
    }

    private function canAccessStudent(User $user, Student $student): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $student->section_id !== null
            && $user->sections()->whereKey($student->section_id)->exists();
    }
}
