<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Concerns\RedirectsAfterForm;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    use LoadsNotificationBell;
    use RedirectsAfterForm;

    public function index(Request $request): View
    {
        $query = Subject::withCount(['teachers', 'attendanceRecords']);

        if ($search = $request->input('search')) {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $subjects = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.subjects.index', [
            'subjects' => $subjects,
            'teachers' => $this->teacherOptions(),
        ])->with($this->notificationBellData());
    }

    public function create(): View
    {
        return view('admin.subjects.create', [
            'teachers' => $this->teacherOptions(),
        ])->with($this->notificationBellData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSubject($request);
        $validated['code'] = Str::upper($validated['code']);

        $subject = Subject::create($validated);
        $subject->teachers()->sync($validated['teacher_ids'] ?? []);

        return $this->redirectAfterForm($request, route('admin.subjects.index'))
            ->with('success', 'Subject created.');
    }

    public function show(Subject $subject): View
    {
        $subject->load('teachers');
        $subject->loadCount('attendanceRecords');

        return view('admin.subjects.show', [
            'subject' => $subject,
            'teachers' => $this->teacherOptions(),
        ])->with($this->notificationBellData());
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $this->validateSubject($request, $subject->id);
        $validated['code'] = Str::upper($validated['code']);

        $subject->update($validated);
        $subject->teachers()->sync($validated['teacher_ids'] ?? []);

        return redirect()->route('admin.subjects.index')->with('success', 'Subject updated.');
    }

    public function toggleStatus(Subject $subject): RedirectResponse
    {
        $subject->update([
            'status' => $subject->isActive() ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Subject status updated.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        if ($subject->attendanceRecords()->exists()) {
            return back()->withErrors([
                'delete' => 'This subject already has attendance records. Deactivate it instead of deleting it.',
            ]);
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Subject deleted.');
    }

    /** @return array<string, mixed> */
    private function validateSubject(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('subjects', 'code')->ignore($ignoreId)],
            'description' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->whereIn('role', User::teachingRoles()),
            ],
        ]);
    }

    /** @return Collection<int, User> */
    private function teacherOptions()
    {
        return User::query()
            ->whereIn('role', User::teachingRoles())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
