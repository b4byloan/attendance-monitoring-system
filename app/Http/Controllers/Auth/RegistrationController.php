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
use Illuminate\Support\Str;
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
            'gradeLevels' => $sections->pluck('grade_level')->unique()->values(),
            'activeTab' => old('registration_type') === 'student' ? 'student' : 'parent',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['required', 'string', 'max:30'],
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
                'relationship_to_student' => 'Parent',
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
            'lrn' => ['required', 'digits:12', Rule::unique('students', 'lrn')],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', Rule::exists('sections', 'grade_level')],
            'section_id' => [
                'required',
                'integer',
                Rule::exists('sections', 'id')->where('grade_level', $request->input('grade_level')),
            ],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $fullName = collect([
            $validated['first_name'],
            $validated['middle_name'] ?? null,
            $validated['last_name'],
        ])->filter()->implode(' ');

        $user = DB::transaction(function () use ($validated, $fullName): User {
            $user = User::create([
                'name' => $fullName,
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'student',
                'is_active' => true,
            ]);

            Student::create([
                'lrn' => $validated['lrn'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'section_id' => $validated['section_id'],
                'qr_code' => Str::random(32),
                'status' => 'active',
                'user_id' => $user->id,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard')
            ->with('success', 'Your student account has been created.');
    }
}
