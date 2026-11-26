<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Section Name</label>
    <x-ui.form-field icon="layers" name="name">
        <input type="text" name="name" value="{{ old('name', $section->name ?? '') }}" placeholder="e.g. Grade 10 - Rizal"
               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Grade Level</label>
    <x-ui.form-field icon="graduation-cap" name="grade_level">
        <select name="grade_level" required
                class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            <option value="">— Select grade level —</option>
            @foreach (\App\Models\Section::GRADE_LEVELS as $gradeLevelOption)
                <option value="{{ $gradeLevelOption }}" @selected(old('grade_level', $section->grade_level ?? '') === $gradeLevelOption)>{{ $gradeLevelOption }}</option>
            @endforeach
        </select>
    </x-ui.form-field>
    @error('grade_level')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label for="school-year-start" class="block text-xs text-gray-500 mb-1">Start year</label>
            <x-ui.form-field icon="calendar" name="school_year_start">
                <input id="school-year-start" type="number" name="school_year_start" value="{{ old('school_year_start', $schoolYearStart) }}" min="1000" max="9999" step="1" required
                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            </x-ui.form-field>
            @error('school_year_start')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="school-year-end" class="block text-xs text-gray-500 mb-1">End year</label>
            <x-ui.form-field icon="calendar" name="school_year_end">
                <input id="school-year-end" type="number" name="school_year_end" value="{{ old('school_year_end', $schoolYearEnd) }}" min="1000" max="9999" step="1" required
                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            </x-ui.form-field>
            @error('school_year_end')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Capacity (1–60)</label>
    <x-ui.form-field icon="users" name="max_capacity">
        <input type="number" name="max_capacity" value="{{ old('max_capacity', $section->max_capacity ?? 50) }}" required min="1" max="60" step="1"
               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('max_capacity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Teachers</label>
    @php
        $selectedTeachers = old('teacher_ids', isset($section) ? $section->teachers->pluck('id')->all() : []);
    @endphp

    <x-ui.teacher-picker :teachers="$teachers" :selected="$selectedTeachers" />

    <p class="mt-1 text-xs text-gray-500">A teacher can be assigned to multiple sections.</p>
    @error('teacher_ids')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>

@isset($section)
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <x-ui.form-field icon="flag">
            <select name="status" class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                <option value="active" @selected(old('status', $section->status) === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $section->status) === 'inactive')>Inactive</option>
            </select>
        </x-ui.form-field>
    </div>
@endisset
