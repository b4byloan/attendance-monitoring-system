<x-layout title="Edit Section" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-xl mx-auto">
        <x-ui.back-button :href="route('admin.sections.show', $section)" label="Back to section" class="mb-4" />

        <x-ui.card title="Edit Section">
            <form method="POST" action="{{ route('admin.sections.update', $section) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Section Name</label>
                    <input type="text" name="name" value="{{ old('name', $section->name) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grade Level</label>
                    <input type="text" name="grade_level" value="{{ old('grade_level', $section->grade_level) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('grade_level') border-red-400 @enderror">
                    @error('grade_level')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                    <input type="text" name="school_year" value="{{ old('school_year', $section->school_year) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('school_year') border-red-400 @enderror">
                    @error('school_year')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Capacity (optional)</label>
                    <input type="number" name="max_capacity" value="{{ old('max_capacity', $section->max_capacity) }}" min="1"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('max_capacity') border-red-400 @enderror">
                    @error('max_capacity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adviser (Teacher)</label>
                    <select name="adviser_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('adviser_id') border-red-400 @enderror">
                        <option value="">— None —</option>
                        @foreach ($advisers as $adviser)
                            <option value="{{ $adviser->id }}" @selected(old('adviser_id', $section->adviser_id) == $adviser->id)>{{ $adviser->name }}</option>
                        @endforeach
                    </select>
                    @error('adviser_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="active" @selected(old('status', $section->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $section->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Save Changes</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('admin.sections.show', $section) }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layout>