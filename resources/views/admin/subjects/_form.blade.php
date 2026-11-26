@php
    $selectedTeachers = old('teacher_ids', isset($subject) ? $subject->teachers->pluck('id')->all() : []);
@endphp

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Subject Name</label>
    <x-ui.form-field icon="scroll-text" name="name">
        <input type="text" name="name" value="{{ old('name', $subject->name ?? '') }}" maxlength="255" required
               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Subject Code</label>
    <x-ui.form-field icon="hash" name="code">
        <input type="text" name="code" value="{{ old('code', $subject->code ?? '') }}" maxlength="30" required
               placeholder="e.g. MATH" pattern="[A-Za-z0-9_-]+" title="Letters, numbers, dashes and underscores only"
               class="w-full border-0 bg-transparent px-3 py-2 text-sm uppercase text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('code')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Description (max 50 characters/optional)</label>
    <x-ui.form-field icon="pencil" name="description">
        <textarea name="description" rows="3" maxlength="50"
                  class="max-h-32 w-full resize-y overflow-y-auto border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">{{ old('description', $subject->description ?? '') }}</textarea>
    </x-ui.form-field>
    @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Teachers</label>

    <x-ui.teacher-picker :teachers="$teachers" :selected="$selectedTeachers"
                         empty-message="No active Teacher accounts yet — create one under Users first." />

    <p class="mt-1 text-xs text-gray-500">A teacher can handle more than one subject.</p>
    @error('teacher_ids')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
    <x-ui.form-field icon="flag">
        <select name="status" class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            <option value="active" @selected(old('status', $subject->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $subject->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
    </x-ui.form-field>
</div>

{{-- @if ($showActions ?? true)
    <div class="flex gap-2 pt-2">
        <x-ui.button type="submit">{{ isset($subject) ? 'Save Changes' : 'Create Subject' }}</x-ui.button>
        <x-ui.button variant="secondary" href="{{ route('admin.subjects.index') }}">Cancel</x-ui.button>
    </div>
@endif --}}

