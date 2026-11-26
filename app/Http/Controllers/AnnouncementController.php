<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Concerns\RedirectsAfterForm;
use App\Models\Announcement;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    use LoadsNotificationBell;
    use RedirectsAfterForm;

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Announcement::with('section', 'author');

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('status', 'published')->orWhere('author_id', $user->id);
            });
        }

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $announcements = $query->orderByDesc('is_pinned')->orderByDesc('publish_date')->paginate(10)->withQueryString();

        return view('announcements.index', array_merge(
            compact('announcements'),
            $this->formContext($user),
        ))->with($this->notificationBellData());
    }

    public function create(Request $request)
    {
        $context = $this->formContext($request->user());

        if (! $context['canCreate']) {
            return redirect()->route('announcements.index')
                ->withErrors(['section' => 'You are not assigned to any section yet, so you cannot create announcements.']);
        }

        return view('announcements.create', $context)->with($this->notificationBellData());
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'publish_date' => ['required', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
            'status' => ['required', 'in:draft,published'],
        ];

        if ($user->isAdmin()) {
            $rules['section_id'] = ['nullable', 'exists:sections,id'];
        }

        $validated = $request->validate($rules);

        if ($user->isAdmin()) {
            $validated['section_id'] = $request->input('section_id') ?: null;
        } else {
            $sectionId = $this->assignedSectionId($user, $request->input('section_id'));

            if (! $sectionId) {
                return back()->withErrors(['section_id' => 'Choose one of your assigned sections as the audience.']);
            }

            $validated['section_id'] = $sectionId;
        }

        $validated['author_id'] = $user->id;
        $validated['is_pinned'] = false;

        Announcement::create($validated);

        return $this->redirectAfterForm($request, route('announcements.index'))
            ->with('success', 'Announcement created.');
    }

    public function edit(Request $request, Announcement $announcement)
    {
        $this->authorizeManage($announcement, $request->user());

        $user = $request->user();
        $sections = $user->isAdmin() ? Section::orderBy('name')->get() : $user->sections()->orderBy('name')->get();

        return view('announcements.edit', [
            'announcement' => $announcement,
            'sections' => $sections,
            'allowSchoolWide' => $user->isAdmin(),
        ])->with($this->notificationBellData());
    }

    /** @return array<string, mixed> */
    private function formContext(User $user): array
    {
        if ($user->isAdmin()) {
            return [
                'sections' => Section::orderBy('name')->get(),
                'lockedSection' => null,
                'allowSchoolWide' => true,
                'canCreate' => true,
            ];
        }

        $mySections = $user->sections()->orderBy('name')->get();

        return [
            'sections' => $mySections,
            'lockedSection' => $mySections->count() === 1 ? $mySections->first() : null,
            'allowSchoolWide' => false,
            'canCreate' => $mySections->isNotEmpty(),
        ];
    }

    private function assignedSectionId($user, mixed $sectionId): ?int
    {
        $assigned = $user->sections()->orderBy('name')->get();

        if ($sectionId) {
            return $assigned->firstWhere('id', (int) $sectionId)?->id;
        }

        return $assigned->count() === 1 ? $assigned->first()->id : null;
    }

    public function update(Request $request, Announcement $announcement)
    {
        $user = $request->user();
        $this->authorizeManage($announcement, $user);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'publish_date' => ['required', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
            'status' => ['required', 'in:draft,published'],
        ];

        if ($user->isAdmin()) {
            $rules['section_id'] = ['nullable', 'exists:sections,id'];
        } else {
            $rules['section_id'] = ['nullable', 'integer'];
        }

        $validated = $request->validate($rules);

        if ($user->isAdmin()) {
            $validated['section_id'] = $request->input('section_id') ?: null;
            $validated['is_pinned'] = $request->boolean('is_pinned');
        } else {
            $sectionId = $this->assignedSectionId($user, $request->input('section_id'));

            if (! $sectionId) {
                return back()->withErrors(['section_id' => 'Choose one of your assigned sections as the audience.']);
            }

            $validated['section_id'] = $sectionId;
        }

        $announcement->update($validated);

        return redirect()->route('announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        $this->authorizeManage($announcement, $request->user());

        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted.');
    }

    public function togglePin(Request $request, Announcement $announcement)
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only Admin can pin announcements.');
        }

        $announcement->update(['is_pinned' => ! $announcement->is_pinned]);

        return back()->with('success', 'Pin status updated.');
    }

    private function authorizeManage(Announcement $announcement, $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($announcement->author_id !== $user->id) {
            abort(403, 'You can only manage announcements you created.');
        }
    }
}
