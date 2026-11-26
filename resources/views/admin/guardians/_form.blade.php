@php
    $guardian = $guardian ?? null;
@endphp

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
    <x-ui.form-field icon="user" name="first_name">
        <input type="text" name="first_name" value="{{ old('first_name', $guardian->first_name ?? '') }}" required maxlength="255"
               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('first_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
    <x-ui.form-field icon="user" name="last_name">
        <input type="text" name="last_name" value="{{ old('last_name', $guardian->last_name ?? '') }}" required maxlength="255"
               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('last_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Email (optional)</label>
    <x-ui.form-field icon="mail" name="email">
        <input type="email" name="email" value="{{ old('email', $guardian->email ?? '') }}" maxlength="255"
               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
    <x-ui.form-field icon="phone" name="phone_number">
        <input type="tel" name="phone_number" value="{{ old('phone_number', $guardian->phone_number ?? '') }}" placeholder="09XXXXXXXXX" required inputmode="numeric" pattern="09[0-9]{9}" maxlength="11" title="Enter an 11-digit mobile number starting with 09"
               data-digits-only data-phone-number data-max-digits="11"
               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('phone_number')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    <p class="mt-1 text-xs text-gray-500">11 digits, starting with 09.</p>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Relationship to Student</label>
    <x-ui.form-field icon="users">
        <select name="relationship_to_student"
                class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            <option value="">— Select relationship —</option>
            @php $currentRelationship = old('relationship_to_student', $guardian->relationship_to_student ?? ''); @endphp
            @if ($currentRelationship !== '' && ! in_array($currentRelationship, \App\Models\Guardian::RELATIONSHIPS, true))
                <option value="{{ $currentRelationship }}" selected>{{ $currentRelationship }} (current)</option>
            @endif
            @foreach (\App\Models\Guardian::RELATIONSHIPS as $relationshipOption)
                <option value="{{ $relationshipOption }}" @selected($currentRelationship === $relationshipOption)>{{ $relationshipOption }}</option>
            @endforeach
        </select>
    </x-ui.form-field>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
    <x-ui.form-field icon="pin">
        <textarea name="address" rows="2" maxlength="500"
                  class="max-h-32 w-full resize-y overflow-y-auto border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">{{ old('address', $guardian->address ?? '') }}</textarea>
    </x-ui.form-field>
</div>

