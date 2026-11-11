<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    use LoadsNotificationBell;

    public function index(Request $request)
    {
        $query = Student::with('section');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('lrn', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('last_name')->paginate(20)->withQueryString();

        return view('admin.students.index', compact('students'))->with($this->notificationBellData());
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

    public function show(Student $student)
    {
        $student->load('section', 'guardians', 'attendanceRecords');

        return view('admin.students.show', compact('student'))->with($this->notificationBellData());
    }

    public function edit(Student $student)
    {
        $sections = Section::orderBy('name')->get();

        return view('admin.students.edit', compact('student', 'sections'))->with($this->notificationBellData());
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

    public function createAccount(Request $request, Student $student)
    {
        if ($student->user_id) {
            return back()->withErrors(['account' => 'This student already has a portal account.']);
        }

        $validated = $request->validate([
            'account_email' => ['required', 'email', 'unique:users,email'],
            'account_password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $student->full_name,
            'email' => $validated['account_email'],
            'password' => $validated['account_password'],
            'role' => 'student',
            'is_active' => true,
        ]);

        $student->update(['user_id' => $user->id]);

        return back()->with('success', 'Student portal account created.');
    }
}
