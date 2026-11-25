@csrf

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Subject Name</label>
    <input type="text" name="name" value="{{ old('name', $subject->name ?? '') }}" maxlength="255"
           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
    @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Subject Code</label>
        <input type="text" name="code" value="{{ old('code', $subject->code ?? '') }}" maxlength="30"
               placeholder="e.g. MATH"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('code') border-red-400 @enderror">
        @error('code')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Grade Level (optional)</label>
        <input type="text" name="grade_level" value="{{ old('grade_level', $subject->grade_level ?? '') }}" maxlength="255"
               placeholder="e.g. Grade 7"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('grade_level') border-red-400 @enderror">
        @error('grade_level')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Description (max 50 characters)</label>
    <textarea name="description" rows="3"  maxlength="50" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror">{{ old('description', $subject->description ?? '') }}</textarea>
    @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Teachers</label>
    @php
        $selectedTeachers = old('teacher_ids', isset($subject) ? $subject->teachers->pluck('id')->all() : []);
    @endphp
    @if ($teachers->isEmpty())
        <p class="text-xs text-gray-500">No active Teacher or Sub-Admin accounts yet — create one under Users first.</p>
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
        <p class="mt-1 text-xs text-gray-500">A teacher can handle more than one subject.</p>
    @endif
    @error('teacher_ids')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="active" @selected(old('status', $subject->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $subject->status ?? 'active') === 'inactive')>Inactive</option>
    </select>
</div>

<div class="flex gap-2 pt-2">
    <x-ui.button type="submit">{{ isset($subject) ? 'Save Changes' : 'Create Subject' }}</x-ui.button>
    <x-ui.button variant="secondary" href="{{ route('admin.subjects.index') }}">Cancel</x-ui.button>
</div>
