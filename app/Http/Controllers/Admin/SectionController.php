<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    use LoadsNotificationBell;

    public function index(Request $request)
    {
        $query = Section::withCount('students')->with('adviser');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('grade_level', 'like', "%{$search}%")
                    ->orWhere('school_year', 'like', "%{$search}%");
            });
        }

        $sections = $query->orderByDesc('school_year')->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.sections.index', compact('sections'))->with($this->notificationBellData());
    }

    public function create()
    {
        $advisers = User::where('role', 'sub_admin')->orderBy('name')->get();

        return view('admin.sections.create', compact('advisers'))->with($this->notificationBellData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateSection($request);
        $validated['status'] = 'active';

        Section::create($validated);

        return redirect()->route('admin.sections.index')->with('success', 'Section created.');
    }

    public function show(Section $section)
    {
        $section->load('adviser', 'students');

        return view('admin.sections.show', compact('section'))->with($this->notificationBellData());
    }

    public function edit(Section $section)
    {
        $advisers = User::where('role', 'sub_admin')->orderBy('name')->get();

        return view('admin.sections.edit', compact('section', 'advisers'))->with($this->notificationBellData());
    }

    public function update(Request $request, Section $section)
    {
        $validated = $this->validateSection($request, $section->id);
        $validated['status'] = $request->input('status', 'active');

        $section->update($validated);

        return redirect()->route('admin.sections.show', $section)->with('success', 'Section updated.');
    }

    public function toggleStatus(Section $section)
    {
        $section->update([
            'status' => $section->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Section status updated.');
    }

    public function destroy(Section $section)
    {
        if ($section->students()->exists()) {
            return back()->withErrors(['delete' => 'This section still has students assigned. Reassign or deactivate them first, or deactivate the section instead of deleting it.']);
        }

        $section->delete();

        return redirect()->route('admin.sections.index')->with('success', 'Section deleted.');
    }

    private function validateSection(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('sections', 'name')
                    ->where(fn ($query) => $query->where('school_year', $request->school_year))
                    ->ignore($ignoreId),
            ],
            'grade_level' => ['required', 'string', 'max:255'],
            'school_year' => ['required', 'string', 'max:20'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'adviser_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($ignoreId) {
                    if (! $value) {
                        return;
                    }

                    $user = User::find($value);
                    if (! $user || $user->role !== 'sub_admin') {
                        $fail('The adviser must be a Teacher account.');

                        return;
                    }

                    $alreadyAdvising = Section::where('adviser_id', $value)
                        ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                        ->exists();

                    if ($alreadyAdvising) {
                        $fail('This Teacher is already assigned as adviser to another section.');
                    }
                },
            ],
        ]);
    }
}
