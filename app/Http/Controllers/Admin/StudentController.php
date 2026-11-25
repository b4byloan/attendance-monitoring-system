<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    use LoadsNotificationBell;

    public function index(Request $request)
    {
        $sort = (string) $request->input('sort');
        $direction = strtolower((string) $request->input('direction')) === 'desc' ? 'desc' : 'asc';

        $query = Student::with('section', 'user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('students.id', $search)
                    ->orWhere('students.lrn', 'like', "%{$search}%")
                    ->orWhere('students.first_name', 'like', "%{$search}%")
                    ->orWhere('students.last_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('email', 'like', "%{$search}%"));
            });
        }

        $this->applySorting($query, $sort, $direction);

        $students = $query->paginate(10)->withQueryString();

        return view('admin.students.index', compact('students'))->with($this->notificationBellData());
    }

    private function applySorting(Builder $query, string $sort, string $direction): void
    {
        $sortable = [
            'lrn' => 'students.lrn',
            'name' => 'students.last_name',
            'section' => 'sections.name',
            'email' => 'users.email',
            'status' => 'students.status',
        ];

        if (! array_key_exists($sort, $sortable)) {
            $query->orderBy('students.last_name')->orderBy('students.first_name');

            return;
        }

        if ($sort === 'section') {
            $query->leftJoin('sections', 'sections.id', '=', 'students.section_id')->select('students.*');
        }

        if ($sort === 'email') {
            $query->leftJoin('users', 'users.id', '=', 'students.user_id')->select('students.*');
        }

        $query->orderBy($sortable[$sort], $direction);

        if ($sort === 'name') {
            $query->orderBy('students.first_name', $direction);
        }
    }

    public function create()
    {
        $sections = Section::orderBy('name')->get();

        return view('admin.students.create', compact('sections'))->with($this->notificationBellData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lrn' => ['required', 'digits:12', 'unique:students,lrn'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'section_id' => ['required', 'exists:sections,id'],
        ]);

        // Temporary placeholder until the real QR Code Management module exists.
        // Not shown anywhere in the UI - just satisfies the required, unique column.
        $validated['qr_code'] = Str::random(32);
        $validated['status'] = 'active';

        Student::create($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student added.');
    }

    public function show(Request $request, Student $student)
    {
        $student->load('section.adviser', 'guardians', 'user');

        $subjects = Subject::query()->where('status', 'active')->orderBy('name')->get();
        $selectedSubjectId = $request->integer('subject') ?: null;

        if ($selectedSubjectId && ! $subjects->contains('id', $selectedSubjectId)) {
            $selectedSubjectId = null;
        }

        $gateRecords = $student->attendanceRecords()
            ->with('recordedBy')
            ->orderByDesc('attendance_date')
            ->orderByDesc('scanned_at')
            ->paginate(10, ['*'], 'gate_page')
            ->withQueryString();

        $subjectRecords = $student->subjectAttendanceRecords()
            ->with('subject', 'recordedBy')
            ->when($selectedSubjectId, fn ($query) => $query->where('subject_id', $selectedSubjectId))
            ->orderByDesc('attendance_date')
            ->orderByDesc('scanned_at')
            ->paginate(10, ['*'], 'subject_page')
            ->withQueryString();

        return view('admin.students.show', [
            'student' => $student,
            'subjects' => $subjects,
            'sections' => Section::orderBy('name')->get(['id', 'name']),
            'selectedSubjectId' => $selectedSubjectId,
            'gateRecords' => $gateRecords,
            'subjectRecords' => $subjectRecords,
            'attendanceCount' => $student->attendanceRecords()->count(),
            'activeTab' => $request->input('tab', 'information'),
        ])->with($this->notificationBellData());
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'lrn' => ['required', 'digits:12', 'unique:students,lrn,'.$student->id],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'section_id' => ['required', 'exists:sections,id'],
            'status' => ['required', 'in:active,inactive,transferred,graduated'],
        ]);

        $student->update($validated);

        return redirect()->route('admin.students.show', $student)->with('success', 'Student updated.');
    }

    public function toggleStatus(Student $student)
    {
        $student->update([
            'status' => $student->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Status updated.');
    }

    public function destroy(Student $student)
    {
        // App-level check for a friendly message. The database itself already
        // refuses this at the schema level (no cascade on attendance_records),
        // so this can never actually destroy attendance history either way.
        if ($student->attendanceRecords()->exists()) {
            return back()->with('error', 'This student has attendance history and cannot be deleted. Deactivate instead.');
        }

        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student deleted.');
    }

    public function resetPassword(Student $student)
    {
        $user = $student->user;

        if (! $user || ! $user->email) {
            return back()->withErrors(['password' => 'This student has not registered a portal account yet.']);
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Password reset email sent to '.$user->email.'.');
        }

        return back()->withErrors(['password' => __($status)]);
    }
}
