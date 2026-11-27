<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->dashboardRoute());
        }

        $sections = Section::query()
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        return view('auth.register', [
            'schoolSettings' => Setting::current(),
            'sections' => $sections,
            'gradeLevels' => $sections->pluck('grade_level')
                ->filter(fn ($level) => in_array($level, Section::GRADE_LEVELS, true))
                ->unique()
                ->sort()
                ->values(),
            'activeTab' => old('registration_type') === 'student' ? 'student' : 'parent',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'phone_number' => preg_replace('/\D+/', '', (string) $request->input('phone_number')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['required', 'digits:11', 'regex:/^09\d{9}$/'],
            'relationship_to_student' => ['required', 'string', Rule::in(Guardian::RELATIONSHIPS)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $nameParts = preg_split('/\s+/', trim($validated['name']), 2) ?: [$validated['name']];
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? $firstName;

        $user = DB::transaction(function () use ($validated, $firstName, $lastName) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'parent',
                'is_active' => true,
            ]);

            Guardian::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
                'relationship_to_student' => $validated['relationship_to_student'],
                'status' => 'active',
                'user_id' => $user->id,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('parent.dashboard')
            ->with('success', 'Your parent account has been created.');
    }

    public function storeStudent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lrn' => ['required', 'digits:12'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'in:Jr.,Sr.,II,III,IV,V'],
            'grade_level' => ['required', 'string', Rule::exists('sections', 'grade_level')],
            'section_id' => [
                'required',
                'integer',
                Rule::exists('sections', 'id')->where('grade_level', $request->input('grade_level')),
            ],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $student = Student::matchSchoolRecord(
            lrn: $validated['lrn'],
            firstName: $validated['first_name'],
            lastName: $validated['last_name'],
            sectionId: (int) $validated['section_id'],
            gradeLevel: $validated['grade_level'],
            middleName: $validated['middle_name'] ?? '',
            suffix: $validated['suffix'] ?? '',
        );

        if (! $student) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'lrn' => 'The details you entered do not match any student record. Check your LRN, name including middle name and suffix, grade level, and section, or contact your school administrator.',
                ]);
        }

        if ($student->user_id) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'lrn' => 'This student record already has a registered account. Please sign in instead.',
                ]);
        }

        $user = DB::transaction(function () use ($validated, $student): User {
            $user = User::create([
                'name' => $student->full_name,
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'student',
                'is_active' => true,
            ]);

            $student->update(['user_id' => $user->id]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard')
            ->with('success', 'Your account has been linked to your school record.');
    }
}
