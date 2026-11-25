<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    use LoadsNotificationBell;

    public function create()
    {
        return view('admin.guardians.create')->with($this->notificationBellData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateGuardian($request);
        $validated['status'] = 'active';

        Guardian::create($validated);

        return redirect()->route('admin.users.index', ['tab' => 'guardians'])->with('success', 'Guardian added.');
    }

    public function show(Guardian $guardian)
    {
        $guardian->load('students.section');

        return view('admin.guardians.show', compact('guardian'))->with($this->notificationBellData());
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

        return redirect()->route('admin.users.index', ['tab' => 'guardians'])->with('success', 'Guardian deleted.');
    }

    public function linkStudent(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'lrn' => ['required', 'digits:12'],
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

    private function validateGuardian(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'phone_number' => preg_replace('/\D+/', '', (string) $request->input('phone_number')),
        ]);

        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:guardians,email'.($ignoreId ? ",{$ignoreId}" : '')],
            'phone_number' => ['required', 'digits:11', 'regex:/^09\d{9}$/'],
            'relationship_to_student' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
