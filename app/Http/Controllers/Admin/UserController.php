<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use LoadsNotificationBell;

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
                    $query->where(function ($query) use ($search): void {
                        $query->where('students.id', $search)
                            ->orWhere('students.lrn', 'like', "%{$search}%")
                            ->orWhere('students.first_name', 'like', "%{$search}%")
                            ->orWhere('students.last_name', 'like', "%{$search}%")
                            ->orWhereHas('user', fn ($user) => $user->where('email', 'like', "%{$search}%"));
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
                    $query->where(function ($query) use ($search): void {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
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

        return redirect()->route('admin.users.index', ['tab' => $this->tabForRole($user->role)])
            ->with('success', 'Account created.');
    }

    public function edit(User $user): View
    {
        $user->load('sections', 'subjects');

        return view('admin.users.edit', [
            'user' => $user,
            'sections' => Section::orderBy('name')->get(),
        ])->with($this->notificationBellData());
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(User::roleOptions()))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'section_ids' => ['nullable', 'array'],
            'section_ids.*' => ['integer', 'exists:sections,id'],
        ]);

        if ($user->role === 'admin' && $validated['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['role' => 'Cannot change the last remaining Admin to another role - promote someone else to Admin first.']);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update(collect($validated)->except(['section_ids'])->all());

        if (in_array($user->role, User::teachingRoles(), true)) {
            $user->sections()->sync($validated['section_ids'] ?? []);
        } else {
            $user->sections()->detach();
            $user->subjects()->detach();
        }

        return redirect()->route('admin.users.index', ['tab' => $this->tabForRole($user->role)])
            ->with('success', 'Account updated.');
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

    private function applyStudentSorting(Builder $query, string $sort, string $direction): void
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
            'parent' => 'guardians',
            'student' => 'students',
            'sub_admin', 'teacher' => 'teachers',
            default => $role,
        };
    }
}
