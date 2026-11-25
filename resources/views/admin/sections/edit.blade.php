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
                    <input type="number" name="grade_level" value="{{ old('grade_level', $section->grade_level) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('grade_level') border-red-400 @enderror" max="10" min="1" step="1">
                    @error('grade_level')<p class="text-red-60  0 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="school-year-start" class="block text-xs text-gray-500 mb-1">Start year</label>
                            <input id="school-year-start" type="number" name="school_year_start" value="{{ old('school_year_start', $schoolYearStart) }}" min="1000" max="9999" step="1" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('school_year_start') border-red-400 @enderror">
                            @error('school_year_start')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="school-year-end" class="block text-xs text-gray-500 mb-1">End year</label>
                            <input id="school-year-end" type="number" name="school_year_end" value="{{ old('school_year_end', $schoolYearEnd) }}" min="1000" max="9999" step="1" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('school_year_end') border-red-400 @enderror">
                            @error('school_year_end')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Capacity</label>
                    <input type="number" name="max_capacity" value="{{ old('max_capacity', $section->max_capacity) }}" required min="1" max="60" step="1"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('max_capacity') border-red-400 @enderror">
                    @error('max_capacity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Teachers</label>
                    @php $selectedTeachers = old('teacher_ids', $section->teachers->pluck('id')->all()); @endphp
                    @if ($teachers->isEmpty())
                        <p class="text-xs text-gray-500">No Teacher or Sub-Admin accounts exist yet — create one in Users first.</p>
                    @else
                        <div class="max-h-56 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3">
                            @foreach ($teachers as $teacher)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="teacher_ids[]" value="{{ $teacher->id }}"
                                           @checked(in_array($teacher->id, $selectedTeachers)) class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>{{ $teacher->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $teacher->roleLabel() }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Uncheck a teacher to remove their access to this section.</p>
                    @endif
                    @error('teacher_ids')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
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