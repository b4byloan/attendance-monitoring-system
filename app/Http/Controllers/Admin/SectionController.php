<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Concerns\RedirectsAfterForm;
use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SectionController extends Controller
{
    use LoadsNotificationBell;
    use RedirectsAfterForm;

    public function index(Request $request): View
    {
        $query = Section::withCount('students')->with(['adviser', 'teachers']);

        if ($search = $request->input('search')) {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('grade_level', 'like', "%{$search}%")
                    ->orWhere('school_year', 'like', "%{$search}%");
            });
        }

        $sections = $query->orderByDesc('school_year')->orderBy('name')->paginate(10)->withQueryString();

        $currentYear = now()->year;

        return view('admin.sections.index', [
            'sections' => $sections,
            'teachers' => $this->teacherOptions(),
            'schoolYearStart' => $currentYear,
            'schoolYearEnd' => $currentYear + 1,
        ])->with($this->notificationBellData());
    }

    public function create(): View
    {
        $currentYear = now()->year;

        return view('admin.sections.create', [
            'teachers' => $this->teacherOptions(),
            'schoolYearStart' => $currentYear,
            'schoolYearEnd' => $currentYear + 1,
        ])->with($this->notificationBellData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSection($request);
        $teacherIds = $validated['teacher_ids'] ?? [];
        unset($validated['teacher_ids']);

        $validated['status'] = 'active';
        $validated['adviser_id'] = $teacherIds[0] ?? null;

        $section = Section::create($validated);
        $section->teachers()->sync($teacherIds);

        return $this->redirectAfterForm($request, route('admin.sections.index'))
            ->with('success', 'Section created.');
    }

    public function show(Section $section): View
    {
        $section->load('adviser', 'teachers');

        $students = $section->students()->orderBy('last_name')->paginate(10);
        [$schoolYearStart, $schoolYearEnd] = array_pad(explode('-', $section->school_year, 2), 2, '');

        return view('admin.sections.show', [
            'section' => $section,
            'students' => $students,
            'teachers' => $this->teacherOptions(),
            'schoolYearStart' => $schoolYearStart,
            'schoolYearEnd' => $schoolYearEnd,
        ])->with($this->notificationBellData());
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $validated = $this->validateSection($request, $section->id);
        $teacherIds = $validated['teacher_ids'] ?? [];
        unset($validated['teacher_ids']);

        $validated['status'] = $request->input('status', 'active');
        $validated['adviser_id'] = $teacherIds[0] ?? null;

        $section->update($validated);
        $section->teachers()->sync($teacherIds);

        return redirect()->route('admin.sections.show', $section)->with('success', 'Section updated.');
    }

    public function toggleStatus(Section $section): RedirectResponse
    {
        $section->update([
            'status' => $section->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Section status updated.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        if ($section->students()->exists()) {
            return back()->withErrors(['delete' => 'This section still has students assigned. Reassign or deactivate them first, or deactivate the section instead of deleting it.']);
        }

        $section->delete();

        return redirect()->route('admin.sections.index')->with('success', 'Section deleted.');
    }

    /** @return array<string, mixed> */
    private function validateSection(Request $request, ?int $ignoreId = null): array
    {
        $schoolYearStart = $request->input('school_year_start');
        $schoolYearEnd = $request->input('school_year_end');
        $schoolYear = is_scalar($schoolYearStart) && is_scalar($schoolYearEnd)
            ? $schoolYearStart.'-'.$schoolYearEnd
            : '';
        $nextSchoolYear = is_scalar($schoolYearStart)
            ? (string) ((int) $schoolYearStart + 1)
            : '';

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('sections', 'name')
                    ->where(fn ($query) => $query->where('school_year', $schoolYear))
                    ->ignore($ignoreId),
            ],
            'grade_level' => ['required', Rule::in(Section::GRADE_LEVELS)],
            'school_year_start' => ['required', 'integer', 'digits:4', 'min:1000', 'max:9999'],
            'school_year_end' => ['required', 'integer', 'digits:4', 'min:1000', 'max:9999', Rule::in([$nextSchoolYear])],
            'max_capacity' => ['required', 'integer', 'min:1', 'max:60'],
            'status' => ['nullable', 'in:active,inactive'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->whereIn('role', User::teachingRoles()),
            ],
        ]);

        $validated['school_year'] = $validated['school_year_start'].'-'.$validated['school_year_end'];
        unset($validated['school_year_start'], $validated['school_year_end']);

        return $validated;
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
