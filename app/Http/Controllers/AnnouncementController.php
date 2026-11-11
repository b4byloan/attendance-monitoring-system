<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Models\Announcement;
use App\Models\Section;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    use LoadsNotificationBell;

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

        $announcements = $query->orderByDesc('is_pinned')->orderByDesc('publish_date')->paginate(20)->withQueryString();

        return view('announcements.index', compact('announcements'))->with($this->notificationBellData());
    }

    public function create(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return view('announcements.create', [
                'sections' => Section::orderBy('name')->get(),
                'lockedSection' => null,
            ])->with($this->notificationBellData());
        }

        $mySection = Section::where('adviser_id', $user->id)->first();

        if (! $mySection) {
            return redirect()->route('announcements.index')
                ->withErrors(['section' => 'You are not assigned as adviser to any section yet, so you cannot create announcements.']);
        }

        return view('announcements.create', ['sections' => collect([$mySection]), 'lockedSection' => $mySection])->with($this->notificationBellData());
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
            $mySection = Section::where('adviser_id', $user->id)->first();

            if (! $mySection) {
                return back()->withErrors(['section' => 'You are not assigned as adviser to any section.']);
            }

            $validated['section_id'] = $mySection->id;
        }

        $validated['author_id'] = $user->id;
        $validated['is_pinned'] = false;

        Announcement::create($validated);

        return redirect()->route('announcements.index')->with('success', 'Announcement created.');
    }

    public function edit(Request $request, Announcement $announcement)
    {
        $this->authorizeManage($announcement, $request->user());

        $sections = $request->user()->isAdmin() ? Section::orderBy('name')->get() : collect();

        return view('announcements.edit', compact('announcement', 'sections'))->with($this->notificationBellData());
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
        }

        $validated = $request->validate($rules);

        if ($user->isAdmin()) {
            $validated['section_id'] = $request->input('section_id') ?: null;
            $validated['is_pinned'] = $request->boolean('is_pinned');
        } else {
            unset($validated['section_id']);
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
