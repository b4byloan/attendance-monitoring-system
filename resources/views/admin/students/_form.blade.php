<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">LRN (12 digits)</label>
    <x-ui.form-field icon="hash" name="lrn">
        <input type="text" name="lrn" required inputmode="numeric" pattern="\d{12}" maxlength="12" minlength="12" title="Enter the 12-digit LRN"
               value="{{ old('lrn', $student->lrn ?? '') }}" data-digits-only data-max-digits="12"
               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('lrn')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
        <x-ui.form-field icon="user" name="first_name">
            <input type="text" name="first_name" required maxlength="255" value="{{ old('first_name', $student->first_name ?? '') }}"
                   class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
        </x-ui.form-field>
        @error('first_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
        <x-ui.form-field icon="user" name="last_name">
            <input type="text" name="last_name" required maxlength="255" value="{{ old('last_name', $student->last_name ?? '') }}"
                   class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
        </x-ui.form-field>
        @error('last_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
        <x-ui.form-field icon="user">
            <input type="text" name="middle_name" maxlength="255" value="{{ old('middle_name', $student->middle_name ?? '') }}"
                   class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
        </x-ui.form-field>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Suffix <span class="text-xs text-gray-500">(optional)</span></label>
        <x-ui.form-field icon="pencil">
            <select name="suffix"
                    class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                <option value="">None</option>
                @foreach (\App\Models\Student::SUFFIXES as $suffixOption)
                    <option value="{{ $suffixOption }}" @selected(old('suffix', $student->suffix ?? '') === $suffixOption)>{{ $suffixOption }}</option>
                @endforeach
            </select>
        </x-ui.form-field>
    </div>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
    <x-ui.form-field icon="layers" name="section_id">
        <select name="section_id"
                class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            <option value="">— Select a section —</option>
            @foreach ($sections as $section)
                <option value="{{ $section->id }}" @selected(old('section_id', $student->section_id ?? null) == $section->id)>{{ $section->name }}</option>
            @endforeach
        </select>
    </x-ui.form-field>
    @error('section_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>

@isset($student)
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <x-ui.form-field icon="flag">
            <select name="status"
                    class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                @foreach (['active', 'inactive', 'transferred', 'graduated'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $student->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </x-ui.form-field>
    </div>
@endisset
