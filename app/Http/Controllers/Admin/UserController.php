<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Concerns\RedirectsAfterForm;
use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use LoadsNotificationBell;
    use RedirectsAfterForm;

    /** @var array<int, string> */
    private const ROLE_TABS = ['admin', 'teachers', 'guard'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $tab = (string) $request->input('tab', 'students');
        $role = (string) $request->input('role');
        $sort = (string) $request->input('sort');
        $direction = strtolower((string) $request->input('direction')) === 'desc' ? 'desc' : 'asc';

        if ($role !== '') {
            $tab = match ($role) {
                'parent' => 'guardians',
                'student' => 'students',
                'sub_admin', 'teacher' => 'teachers',
                default => $role,
            };
        }

        $tab = match ($tab) {
            'sub_admin', 'teacher' => 'teachers',
            default => $tab,
        };

        if ($tab !== 'students' && $tab !== 'guardians' && ! in_array($tab, self::ROLE_TABS, true)) {
            $tab = 'students';
        }

        $data = [
            'tab' => $tab,
            'search' => $search,
            'role' => $role,
            'sections' => Section::orderBy('name')->get(['id', 'name']),
            'tabCounts' => [
                'students' => Student::count(),
                'guardians' => Guardian::count(),
                'admin' => User::where('role', 'admin')->count(),
                'teachers' => User::whereIn('role', User::teachingRoles())->count(),
                'guard' => User::where('role', 'guard')->count(),
            ],
        ];

        if ($tab === 'students') {
            $studentsQuery = Student::query()
                ->with('section', 'user')
                ->when($search !== '', function ($query) use ($search): void {
                    $terms = $this->searchTerms($search);

                    $query->where(function ($query) use ($terms): void {
                        foreach ($terms as $term) {
                            $query->where(function ($query) use ($term): void {
                                if (ctype_digit($term)) {
                                    $query->orWhere('students.id', $term);
                                }

                                $query->orWhere('students.lrn', 'like', "%{$term}%")
                                    ->orWhere('students.first_name', 'like', "%{$term}%")
                                    ->orWhere('students.middle_name', 'like', "%{$term}%")
                                    ->orWhere('students.last_name', 'like', "%{$term}%")
                                    ->orWhereHas('user', fn ($user) => $user->where('email', 'like', "%{$term}%"));
                            });
                        }
                    });
                })
                ->when($request->integer('section'), fn ($query, $section) => $query->where('students.section_id', $section));

            $this->applyStudentSorting($studentsQuery, $sort, $direction);

            $data['students'] = $studentsQuery
                ->paginate(10, ['*'], 'students_page')
                ->withQueryString();
        } elseif ($tab === 'guardians') {
            $guardiansQuery = Guardian::query()
                ->withCount('students')
                ->when($search !== '', function ($query) use ($search): void {
                    $terms = $this->searchTerms($search);

                    $query->where(function ($query) use ($terms): void {
                        foreach ($terms as $term) {
                            $query->where(function ($query) use ($term): void {
                                $query->where('first_name', 'like', "%{$term}%")
                                    ->orWhere('last_name', 'like', "%{$term}%")
                                    ->orWhere('email', 'like', "%{$term}%")
                                    ->orWhere('phone_number', 'like', "%{$term}%");
                            });
                        }
                    });
                });

            $this->applyGuardianSorting($guardiansQuery, $sort, $direction);

            $data['guardians'] = $guardiansQuery
                ->paginate(10, ['*'], 'guardians_page')
                ->withQueryString();
        } else {
            $roles = $tab === 'teachers' ? User::teachingRoles() : [$tab];

            if ($role !== '' && in_array($role, $roles, true)) {
                $roles = [$role];
            }

            $usersQuery = $this->accountQuery($roles, $search)->with('sections');
            $this->applyAccountSorting($usersQuery, $sort, $direction);

            $data['users'] = $usersQuery
                ->paginate(10)
                ->withQueryString();
        }

        return view('admin.users.index', $data)->with($this->notificationBellData());
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'sections' => Section::orderBy('name')->get(),
        ])->with($this->notificationBellData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_keys(User::roleOptions()))],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['integer', 'exists:sections,id'],
        ]);

        $validated['is_active'] = true;

        $user = User::create(collect($validated)->except(['section_ids'])->all());

        if (in_array($user->role, User::teachingRoles(), true)) {
            $user->sections()->sync($validated['section_ids'] ?? []);
        }

        return $this->redirectAfterForm($request, route('admin.users.index', ['tab' => $this->tabForRole($user->role)]))
            ->with('success', 'Account created.');
    }

    /**
     * Create any supported account type from the single unified add-user form.
     */
    public function storeAccount(Request $request): RedirectResponse
    {
        $request->merge([
            'lrn' => preg_replace('/\D+/', '', (string) $request->input('lrn')),
            'phone_number' => preg_replace('/\D+/', '', (string) $request->input('phone_number')),
        ]);

        $common = $request->validate([
            'account_type' => ['required', Rule::in(['student', 'guardian', 'teacher', 'admin', 'guard'])],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ]);

        $type = $common['account_type'];

        match ($type) {
            'student' => $this->createStudentAccount($request, $common),
            'guardian' => $this->createGuardianAccount($request, $common),
            default => $this->createStaffAccount($request, $common, $type),
        };

        return $this->redirectAfterForm($request, route('admin.users.index', ['tab' => $this->tabForRole($type)]))
            ->with('success', match ($type) {
                'student' => 'Student added.',
                'guardian' => 'Guardian added.',
                default => 'Account created.',
            });
    }

    /**
     * @param  array<string, mixed>  $common
     */
    private function createStudentAccount(Request $request, array $common): void
    {
        $validated = $request->validate([
            'lrn' => ['required', 'digits:12', Rule::unique('students', 'lrn')],
            'suffix' => ['nullable', Rule::in(Student::SUFFIXES)],
            'section_id' => ['required', 'exists:sections,id'],
        ]);

        Student::create([
            'lrn' => $validated['lrn'],
            'first_name' => $common['first_name'],
            'middle_name' => $common['middle_name'] ?? null,
            'last_name' => $common['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'section_id' => $validated['section_id'],
            // Every student gets one permanent QR token that is never regenerated.
            'qr_code' => Str::random(32),
            'status' => 'active',
        ]);
    }

    /**
     * @param  array<string, mixed>  $common
     */
    private function createGuardianAccount(Request $request, array $common): void
    {
        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255', Rule::unique('guardians', 'email')],
            'phone_number' => ['required', 'digits:11', 'regex:/^09\d{9}$/'],
            'relationship_to_student' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        Guardian::create([
            'first_name' => $common['first_name'],
            'middle_name' => $common['middle_name'] ?? null,
            'last_name' => $common['last_name'],
            'email' => $validated['email'] ?? null,
            'phone_number' => $validated['phone_number'],
            'relationship_to_student' => $validated['relationship_to_student'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => 'active',
        ]);
    }

    /**
     * @param  array<string, mixed>  $common
     */
    private function createStaffAccount(Request $request, array $common, string $type): void
    {
        $role = in_array($type, array_keys(User::roleOptions()), true) ? $type : 'teacher';

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['integer', 'exists:sections,id'],
        ]);

        $name = collect([$common['first_name'], $common['middle_name'] ?? null, $common['last_name']])
            ->filter()
            ->implode(' ');

        $user = User::create([
            'name' => $name,
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $role,
            'is_active' => true,
        ]);

        if (in_array($user->role, User::teachingRoles(), true)) {
            $user->sections()->sync($validated['section_ids'] ?? []);
        }
    }

    public function edit(User $user): View
    {
        $user->load('sections', 'subjects', 'student', 'guardian');

        return view('admin.users.edit', [
            'user' => $user,
            'sections' => Section::orderBy('name')->get(),
        ])->with($this->notificationBellData());
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->merge([
            'lrn' => $request->filled('lrn') ? preg_replace('/\D+/', '', (string) $request->input('lrn')) : null,
            'phone_number' => $request->filled('phone_number') ? preg_replace('/\D+/', '', (string) $request->input('phone_number')) : null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(User::roleOptions()))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['integer', 'exists:sections,id'],
            'lrn' => ['nullable', 'required_if:role,student', 'digits:12'],
            'phone_number' => ['nullable', 'required_if:role,parent', 'digits:11', 'regex:/^09\d{9}$/'],
            'relationship_to_student' => ['nullable', 'string', 'max:50'],
        ]);

        if ($user->role === 'admin' && $validated['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['role' => 'Cannot change the last remaining Admin to another role - promote someone else to Admin first.']);
        }

        if ($validated['role'] === 'student' && $error = $this->linkStudentRecord($user, $validated['lrn'] ?? null)) {
            return back()->withErrors(['lrn' => $error])->withInput();
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update(collect($validated)
            ->except(['section_ids', 'lrn', 'phone_number', 'relationship_to_student'])
            ->all());

        if (in_array($user->role, User::teachingRoles(), true)) {
            $user->sections()->sync($validated['section_ids'] ?? []);
        } else {
            $user->sections()->detach();
            $user->subjects()->detach();
        }

        if ($user->role === 'parent') {
            $this->syncGuardianRecord($user, $validated);
        }

        return redirect()->route('admin.users.index', ['tab' => $this->tabForRole($user->role)])
            ->with('success', 'Account updated.');
    }

    /**
     * Link the account to the existing student record with the given LRN.
     */
    private function linkStudentRecord(User $user, ?string $lrn): ?string
    {
        $student = Student::where('lrn', $lrn)->first();

        if (! $student) {
            return 'No student record was found with that LRN. Add the student under Student Records first.';
        }

        if ($student->user_id && $student->user_id !== $user->id) {
            return 'That student record is already linked to another account.';
        }

        $student->update(['user_id' => $user->id]);

        return null;
    }

    /**
     * Create or update the guardian record that belongs to this account.
     *
     * @param  array<string, mixed>  $validated
     */
    private function syncGuardianRecord(User $user, array $validated): void
    {
        $guardian = $user->guardian ?? new Guardian(['user_id' => $user->id]);

        $nameParts = preg_split('/\s+/', trim($user->name)) ?: [];
        $firstName = $nameParts[0] ?? $user->name;
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $user->name;

        $guardian->fill([
            'first_name' => $guardian->first_name ?: $firstName,
            'last_name' => $guardian->last_name ?: $lastName,
            'email' => $user->email,
            'phone_number' => $validated['phone_number'] ?? $guardian->phone_number,
            'relationship_to_student' => $validated['relationship_to_student'] ?? $guardian->relationship_to_student,
            'status' => $guardian->status ?: 'active',
            'user_id' => $user->id,
        ]);

        $guardian->save();
    }

    public function setPassword(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== 'admin') {
            return back()->withErrors(['password' => 'Only admin accounts can be given a password here. Use the reset email for this account.']);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => $validated['password']]);

        return back()->with('success', 'Password updated for '.$user->name.'.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Password reset email sent to '.$user->email.'.');
        }

        return back()->withErrors(['password' => __($status)]);
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['status' => 'You cannot deactivate your own account.']);
        }

        if ($user->role === 'admin' && $user->is_active && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['status' => 'Cannot deactivate the last active Admin account.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Account status updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['delete' => 'Cannot delete the last remaining Admin account.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index', ['tab' => $this->tabForRole($user->role)])
            ->with('success', 'Account deleted.');
    }

    /**
     * Split a search string into terms so "Juan Cruz" matches a student whose
     * first and last names are stored in separate columns.
     *
     * @return array<int, string>
     */
    private function searchTerms(string $search): array
    {
        return preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    private function applyStudentSorting(Builder $query, string $sort, string $direction): void
    {
        $sortable = [
            'lrn' => 'students.lrn',
            'name' => 'students.last_name',
            'grade_level' => 'sections.grade_level',
            'section' => 'sections.name',
            'email' => 'users.email',
            'status' => 'students.status',
        ];

        if (! array_key_exists($sort, $sortable)) {
            $query->orderBy('students.last_name')->orderBy('students.first_name');

            return;
        }

        if ($sort === 'section' || $sort === 'grade_level') {
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

    private function applyGuardianSorting(Builder $query, string $sort, string $direction): void
    {
        $sortable = [
            'name' => 'guardians.last_name',
            'contact' => 'guardians.phone_number',
            'relationship' => 'guardians.relationship_to_student',
            'status' => 'guardians.status',
            'students' => 'students_count',
        ];

        if (! array_key_exists($sort, $sortable)) {
            $query->orderBy('guardians.last_name')->orderBy('guardians.first_name');

            return;
        }

        $query->orderBy($sortable[$sort], $direction);

        if ($sort === 'name') {
            $query->orderBy('guardians.first_name', $direction);
        }
    }

    private function applyAccountSorting(Builder $query, string $sort, string $direction): void
    {
        $sortable = [
            'name' => 'name',
            'email' => 'email',
            'role' => 'role',
            'status' => 'is_active',
            'last_login' => 'last_login_at',
        ];

        $query->orderBy($sortable[$sort] ?? 'name', $direction);
    }

    /**
     * @param  string|array<int, string>  $roles
     */
    private function accountQuery(string|array $roles, string $search): Builder
    {
        return User::query()
            ->whereIn('role', (array) $roles)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
    }

    private function tabForRole(string $role): string
    {
        return match ($role) {
            'parent', 'guardian' => 'guardians',
            'student' => 'students',
            'sub_admin', 'teacher' => 'teachers',
            default => $role,
        };
    }
}
