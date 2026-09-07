<x-layout title="Edit Announcement" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-xl mx-auto">
        <x-ui.card title="Edit Announcement">
            <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title', $announcement->title) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                    @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="5"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror">{{ old('description', $announcement->description) }}</textarea>
                    @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                @if (auth()->user()->isAdmin())
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Audience</label>
                        <select name="section_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">School-wide (everyone)</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" @selected(old('section_id', $announcement->section_id) == $section->id)>{{ $section->name }} only</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center text-sm gap-2">
                        <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $announcement->is_pinned)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Pinned (shows at the top everywhere)
                    </label>
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Audience</label>
                        <p class="text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">{{ $announcement->section->name ?? 'School-wide' }} — audience can't be changed here.</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                        <input type="date" name="publish_date" value="{{ old('publish_date', $announcement->publish_date->toDateString()) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('publish_date') border-red-400 @enderror">
                        @error('publish_date')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date (optional)</label>
                        <input type="date" name="expiration_date" value="{{ old('expiration_date', $announcement->expiration_date?->toDateString()) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('expiration_date') border-red-400 @enderror">
                        @error('expiration_date')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="draft" @selected(old('status', $announcement->status) === 'draft')>Draft (not visible yet)</option>
                        <option value="published" @selected(old('status', $announcement->status) === 'published')>Published</option>
                    </select>
                </div>

                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Save Changes</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('announcements.index') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layout>