@php
    $accountTypes = [
        'student' => 'Student',
        'guardian' => 'Guardian',
        'teacher' => 'Teacher',
        'admin' => 'Admin',
        'guard' => 'Guard',
    ];

    $defaultType = old('account_type', $defaultType ?? 'student');
@endphp

<div data-account-form>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Account type</label>
        <x-ui.form-field icon="users" name="account_type">
            <select name="account_type" data-account-type
                    class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                @foreach ($accountTypes as $value => $label)
                    <option value="{{ $value }}" @selected($defaultType === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-ui.form-field>
        <p class="mt-1 text-xs text-gray-500">The form below changes automatically to match the selected type.</p>
    </div>

    <div class="rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-sm text-emerald-800" data-account-preview aria-live="polite">
        New Student
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div data-account-panel="all">
            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
            <x-ui.form-field icon="user" name="first_name">
                <input type="text" name="first_name" value="{{ old('first_name') }}" required maxlength="255"
                       data-account-name="first"
                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            </x-ui.form-field>
            @error('first_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div data-account-panel="all">
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
            <x-ui.form-field icon="user" name="last_name">
                <input type="text" name="last_name" value="{{ old('last_name') }}" required maxlength="255"
                       data-account-name="last"
                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            </x-ui.form-field>
            @error('last_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div data-account-panel="all">
            <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name <span class="text-xs text-gray-500">(optional)</span></label>
            <x-ui.form-field icon="user">
                <input type="text" name="middle_name" value="{{ old('middle_name') }}" maxlength="255"
                       data-account-name="middle"
                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            </x-ui.form-field>
        </div>

        <div data-account-panel="student">
            <label class="block text-sm font-medium text-gray-700 mb-1">Suffix <span class="text-xs text-gray-500">(optional)</span></label>
            <x-ui.form-field icon="pencil">
                <select name="suffix" data-account-suffix
                        class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    <option value="">None</option>
                    @foreach (\App\Models\Student::SUFFIXES as $suffixOption)
                        <option value="{{ $suffixOption }}" @selected(old('suffix') === $suffixOption)>{{ $suffixOption }}</option>
                    @endforeach
                </select>
            </x-ui.form-field>
            @error('suffix')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div data-account-panel="student" class="mt-4 space-y-4 rounded-lg border border-gray-200 p-4">
        <p class="text-sm font-semibold text-gray-700">Student details</p>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">LRN (12 digits)</label>
            <x-ui.form-field icon="hash" name="lrn">
                <input type="text" name="lrn" value="{{ old('lrn') }}" inputmode="numeric" pattern="\d{12}" maxlength="12" minlength="12"
                       title="Enter the 12-digit LRN" data-digits-only data-max-digits="12" data-required-for="student"
                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            </x-ui.form-field>
            @error('lrn')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
            <x-ui.form-field icon="layers" name="section_id">
                <select name="section_id" data-required-for="student"
                        class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    <option value="">— Select a section —</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected(old('section_id') == $section->id)>{{ $section->name }}</option>
                    @endforeach
                </select>
            </x-ui.form-field>
            @error('section_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
    <div data-account-panel="guardian" class="mt-4 space-y-4 rounded-lg border border-gray-200 p-4">
        <p class="text-sm font-semibold text-gray-700">Guardian details</p>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-xs text-gray-500">(optional)</span></label>
                <x-ui.form-field icon="mail" name="email">
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="255"
                           class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                </x-ui.form-field>
                @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                <x-ui.form-field icon="phone" name="phone_number">
                    <input type="tel" name="phone_number" value="{{ old('phone_number') }}" placeholder="09XXXXXXXXX" inputmode="numeric"
                           pattern="09[0-9]{9}" maxlength="11" title="Enter an 11-digit mobile number starting with 09"
                           data-digits-only data-phone-number data-max-digits="11" data-required-for="guardian"
                           class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                </x-ui.form-field>
                @error('phone_number')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-gray-500">11 digits, starting with 09.</p>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Relationship to Student</label>
            <x-ui.form-field icon="users">
                @php $currentRelationship = old('relationship_to_student'); @endphp
                <select name="relationship_to_student"
                        class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    <option value="">— Select relationship —</option>
                    @if ($currentRelationship !== null && $currentRelationship !== '' && ! in_array($currentRelationship, \App\Models\Guardian::RELATIONSHIPS, true))
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
                          class="max-h-32 w-full resize-y overflow-y-auto border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">{{ old('address') }}</textarea>
            </x-ui.form-field>
        </div>
    </div>


    <div data-account-panel="teacher admin guard" class="mt-4 space-y-4 rounded-lg border border-gray-200 p-4">
        <p class="text-sm font-semibold text-gray-700">Login Credentials</p>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <x-ui.form-field icon="mail" name="email">
                <input type="email" name="email" value="{{ old('email') }}" maxlength="255"
                       data-required-for="teacher admin guard"
                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            </x-ui.form-field>
            @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <x-ui.form-field icon="lock" name="password" data-password-group>
                    <x-ui.password-input name="password" :grouped="true" data-required-for="teacher admin guard" />
                </x-ui.form-field>
                @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-gray-500">At least 8 characters.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <x-ui.form-field icon="lock" data-password-group>
                    <x-ui.password-input name="password_confirmation" :grouped="true" data-required-for="teacher admin guard" />
                </x-ui.form-field>
            </div>
        </div>
    </div>

    <div data-account-panel="teacher" class="mt-4 rounded-lg border border-gray-200 p-4">
        <p class="mb-2 text-sm font-semibold text-gray-700">Assigned Sections (Teachers)</p>
        @if ($sections->isEmpty())
            <p class="text-xs text-gray-500">No sections have been created yet.</p>
        @else
            <div class="max-h-48 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3">
                @foreach ($sections as $section)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="section_ids[]" value="{{ $section->id }}"
                               @checked(in_array($section->id, old('section_ids', []))) class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        {{ $section->name }}
                    </label>
                @endforeach
            </div>
            <p class="mt-1 text-xs text-gray-500">A teacher can be assigned to multiple sections.</p>
        @endif
        @error('section_ids')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    @once
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-account-form]').forEach(function (form) {
                    const typeSelect = form.querySelector('[data-account-type]');
                    const preview = form.querySelector('[data-account-preview]');
                    const labels = { student: 'Student', guardian: 'Guardian', teacher: 'Teacher', admin: 'Admin', guard: 'Guard' };

                    if (! typeSelect) {
                        return;
                    }

                    function updatePreview() {
                        const type = typeSelect.value;
                        const parts = ['first', 'middle', 'last'].map(function (key) {
                            const field = form.querySelector('[data-account-name="' + key + '"]');

                            return field ? field.value.trim() : '';
                        }).filter(Boolean);

                        const suffix = form.querySelector('[data-account-suffix]');

                        if (type === 'student' && suffix && suffix.value) {
                            parts.push(suffix.value);
                        }

                        const name = parts.join(' ');

                        if (preview) {
                            preview.textContent = 'New ' + (labels[type] || 'User') + (name ? ': ' + name : '');
                        }
                    }

                    function applyType(type) {
                        form.querySelectorAll('[data-account-panel]').forEach(function (panel) {
                            const types = panel.dataset.accountPanel.split(' ');
                            const visible = types.includes('all') || types.includes(type);

                            panel.classList.toggle('hidden', ! visible);

                            panel.querySelectorAll('input, select, textarea').forEach(function (field) {
                                field.disabled = ! visible;

                                if (field.dataset.requiredFor) {
                                    field.required = field.dataset.requiredFor.split(' ').includes(type);
                                }
                            });
                        });

                        updatePreview();
                    }

                    typeSelect.addEventListener('change', function () {
                        applyType(typeSelect.value);
                    });

                    form.addEventListener('input', updatePreview);
                    form.addEventListener('change', updatePreview);

                    applyType(typeSelect.value);
                });
            });
        </script>
    @endonce
</div>

