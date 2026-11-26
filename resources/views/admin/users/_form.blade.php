@php
    $selectedRole = old('role', $selectedRole ?? 'admin');
    $selectedSections = old('section_ids', []);
@endphp

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
    <x-ui.form-field icon="user" name="name">
        <input type="text" name="name" value="{{ old('name') }}" required maxlength="255"
               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
    <x-ui.form-field icon="mail" name="email">
        <input type="email" name="email" value="{{ old('email') }}" required maxlength="255"
               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
    <x-ui.password-input name="password" :required="true" :grouped="true" class="@error('password') border-red-400 @enderror" />
    @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    <p class="text-xs text-gray-500 mt-1">At least 8 characters.</p>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
    <x-ui.password-input name="password_confirmation" :required="true" :grouped="true" />
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
    <x-ui.form-field icon="shield" name="role">
        <select name="role" class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            @foreach (\App\Models\User::roleOptions() as $value => $label)
                <option value="{{ $value }}" @selected($selectedRole === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </x-ui.form-field>
    @error('role')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Sections (Teachers)</label>
    <div class="max-h-48 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3">
        @foreach ($sections as $section)
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="section_ids[]" value="{{ $section->id }}"
                       @checked(in_array($section->id, $selectedSections)) class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                {{ $section->name }}
            </label>
        @endforeach
    </div>
    <p class="mt-1 text-xs text-gray-500">Only applies to Teacher accounts. A teacher can be assigned to multiple sections.</p>
</div>
