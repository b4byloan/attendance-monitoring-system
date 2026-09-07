<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\LoadsNotificationBell;

class GuardianController extends Controller
{
    use LoadsNotificationBell;
    public function index(Request $request)
    {
        $query = Guardian::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $guardians = $query->withCount('students')->orderBy('last_name')->paginate(20)->withQueryString();

        return view('admin.guardians.index', compact('guardians'))->with($this->notificationBellData());
    }

    public function create()
    {
        return view('admin.guardians.create')->with($this->notificationBellData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateGuardian($request);
        $validated['status'] = 'active';

        Guardian::create($validated);

        return redirect()->route('admin.guardians.index')->with('success', 'Guardian added.');
    }

    public function show(Guardian $guardian)
    {
        $guardian->load('students.section', 'user');

        return view('admin.guardians.show', compact('guardian'))->with($this->notificationBellData());
    }

    public function edit(Guardian $guardian)
    {
        return view('admin.guardians.edit', compact('guardian'))->with($this->notificationBellData());
    }

    public function update(Request $request, Guardian $guardian)
    {
        $validated = $this->validateGuardian($request, $guardian->id);
        $validated['status'] = $request->input('status', 'active');

        $guardian->update($validated);

        return redirect()->route('admin.guardians.show', $guardian)->with('success', 'Guardian updated.');
    }

    public function destroy(Guardian $guardian)
    {
        $guardian->delete();

        return redirect()->route('admin.guardians.index')->with('success', 'Guardian deleted.');
    }

    public function linkStudent(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'lrn' => ['required', 'string'],
        ]);

        $student = Student::where('lrn', $validated['lrn'])->first();

        if (! $student) {
            return back()->withErrors(['lrn' => 'No student found with that LRN.']);
        }

        // syncWithoutDetaching is idempotent - linking an already-linked
        // student is a safe no-op instead of a duplicate-key error.
        $guardian->students()->syncWithoutDetaching([$student->id]);

        return back()->with('success', "{$student->full_name} linked.");
    }

    public function unlinkStudent(Guardian $guardian, Student $student)
    {
        $guardian->students()->detach($student->id);

        return back()->with('success', 'Student link removed.');
    }

    public function createParentAccount(Request $request, Guardian $guardian)
    {
        if ($guardian->user_id) {
            return back()->withErrors(['account' => 'This guardian already has a Parent account.']);
        }

        $validated = $request->validate([
            'account_email' => ['required', 'email', 'unique:users,email'],
            'account_password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => trim("{$guardian->first_name} {$guardian->last_name}"),
            'email' => $validated['account_email'],
            'password' => $validated['account_password'],
            'role' => 'parent',
            'is_active' => true,
        ]);

        $guardian->update(['user_id' => $user->id]);

        return back()->with('success', 'Parent account created and linked.');
    }

    private function validateGuardian(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:guardians,email' . ($ignoreId ? ",{$ignoreId}" : '')],
            'phone_number' => ['required', 'regex:/^09\d{9}$/'],
            'relationship_to_student' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
    }
}