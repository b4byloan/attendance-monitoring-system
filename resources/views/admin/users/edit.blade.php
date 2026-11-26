<x-layout title="Edit User" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    @php
        $roleColors = [
            'admin' => 'indigo',
            'sub_admin' => 'green',
            'teacher' => 'green',
            'guard' => 'amber',
            'parent' => 'purple',
            'student' => 'gray',
        ];
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">

        <x-ui.back-button :href="route('admin.users.index')" label="Back to users" />

        <x-ui.card>
            <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
                <div class="flex items-center gap-4">
                    <x-ui.avatar :name="$user->name" size="lg" />
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                            {{ $user->email }}
                            <x-ui.copy-button :value="$user->email" what="email" :icon-only="true" />
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <x-ui.badge color="{{ $roleColors[$user->role] ?? 'gray' }}">{{ $user->roleLabel() }}</x-ui.badge>
                    <x-ui.badge color="{{ $user->is_active ? 'green' : 'gray' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                </div>
            </div>
            <dl class="grid grid-cols-2 gap-4 text-sm border-t border-gray-100 pt-4">
                <div>
                    <dt class="text-gray-500 text-xs uppercase">Last Login</dt>
                    <dd class="font-medium mt-0.5">{{ $user->last_login_at?->format('M j, Y h:i A') ?? 'Never' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs uppercase">Account Created</dt>
                    <dd class="font-medium mt-0.5">{{ $user->created_at->format('M j, Y') }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Edit Details">
            <form id="user-update-form" method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4" data-role-form>
                @csrf
                @method('PUT')

                @php
                    $currentRole = old('role', $user->role);
                    $linkedStudent = $user->student;
                    $linkedGuardian = $user->guardian;
                    $selectedSections = old('section_ids', $user->sections->pluck('id')->all());
                @endphp

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <x-ui.form-field icon="user" name="name">
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                   class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                        </x-ui.form-field>
                        @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <x-ui.form-field icon="mail" name="email">
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                   class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                        </x-ui.form-field>
                        @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <x-ui.form-field icon="shield" name="role">
                        <select id="role" name="role" data-original-role="{{ $user->role }}" data-role-select
                                class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                            @foreach (\App\Models\User::roleOptions() as $value => $label)
                                <option value="{{ $value }}" @selected($currentRole === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-ui.form-field>
                    @error('role')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-gray-500">The rest of this form changes to match the selected role.</p>
                </div>
                <div data-role-panel="teacher" class="rounded-lg border border-gray-200 p-4">
                    <p class="mb-2 text-sm font-semibold text-gray-700">Assigned Sections (Teachers)</p>
                    @if ($sections->isEmpty())
                        <p class="text-xs text-gray-500">No sections have been created yet.</p>
                    @else
                        <div class="max-h-48 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3">
                            @foreach ($sections as $section)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="section_ids[]" value="{{ $section->id }}"
                                           @checked(in_array($section->id, $selectedSections)) class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    {{ $section->name }}
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-gray-500">A teacher can be assigned to multiple sections.</p>
                    @endif
                    @error('section_ids')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div data-role-panel="student" class="rounded-lg border border-gray-200 p-4">
                    <p class="mb-2 text-sm font-semibold text-gray-700">Student record</p>
                    <label class="block text-sm font-medium text-gray-700 mb-1">LRN (12 digits)</label>
                    <x-ui.form-field icon="hash" name="lrn">
                        <input type="text" name="lrn" value="{{ old('lrn', $linkedStudent->lrn ?? '') }}"
                               inputmode="numeric" pattern="\d{12}" maxlength="12" minlength="12"
                               title="Enter the 12-digit LRN" data-digits-only data-max-digits="12"
                               data-required-for="student"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                    @error('lrn')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-gray-500">The account is linked to the existing student record with this LRN.</p>
                </div>

                <div data-role-panel="parent" class="space-y-4 rounded-lg border border-gray-200 p-4">
                    <p class="text-sm font-semibold text-gray-700">Guardian details</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                            <x-ui.form-field icon="phone" name="phone_number">
                                <input type="tel" name="phone_number" value="{{ old('phone_number', $linkedGuardian->phone_number ?? '') }}"
                                       placeholder="09XXXXXXXXX" inputmode="numeric" pattern="09[0-9]{9}" maxlength="11"
                                       title="Enter an 11-digit mobile number starting with 09"
                                       data-digits-only data-phone-number data-max-digits="11" data-required-for="parent"
                                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                            </x-ui.form-field>
                            @error('phone_number')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Relationship to Student</label>
                            <x-ui.form-field icon="users">
                                @php $currentRelationship = old('relationship_to_student', $linkedGuardian->relationship_to_student ?? ''); @endphp
                                <select name="relationship_to_student"
                                        class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                                    <option value="">— Select relationship —</option>
                                    @if ($currentRelationship !== '' && ! in_array($currentRelationship, \App\Models\Guardian::RELATIONSHIPS, true))
                                        <option value="{{ $currentRelationship }}" selected>{{ $currentRelationship }} (current)</option>
                                    @endif
                                    @foreach (\App\Models\Guardian::RELATIONSHIPS as $relationshipOption)
                                        <option value="{{ $relationshipOption }}" @selected($currentRelationship === $relationshipOption)>{{ $relationshipOption }}</option>
                                    @endforeach
                                </select>
                            </x-ui.form-field>
                            @error('relationship_to_student')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
                {{-- <div class="rounded-lg border border-gray-200 p-3">
                    <p class="text-sm font-medium text-gray-700">Change Password (optional)</p>
                    <div class="mt-3 space-y-3">
                        <x-ui.password-input name="password" placeholder="Leave blank to keep the current password" :grouped="true" class="@error('password') border-red-400 @enderror" />
                        @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        <x-ui.password-input name="password_confirmation" placeholder="Confirm new password" :grouped="true" />
                    </div>
                </div> --}}
                @if ($user->subjects->isNotEmpty())
                    <div class="rounded-lg bg-gray-50 p-3 text-sm text-gray-600">
                        <p class="font-medium text-gray-700">Assigned subjects</p>
                        <p class="mt-1">{{ $user->subjects->pluck('name')->join(', ') }}</p>
                        <p class="mt-1 text-xs text-gray-500">Manage subject assignments from Subject Management.</p>
                    </div>
                @endif
                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Save Changes</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('admin.users.index') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>

    </div>

    <x-ui.confirm-modal
        id="role-change-modal"
        title="Change this user's role?"
        form-id="user-update-form"
        confirm-label="Yes, save changes"
        warning="Changing a role changes what this account can access, effective immediately.">
        You are about to change {{ $user->name }}'s role from
        <strong>{{ $user->roleLabel() }}</strong> to the role selected in the form.
    </x-ui.confirm-modal>

    @push('scripts')
    <script>
        const userUpdateForm = document.getElementById('user-update-form');
        const roleSelect = document.getElementById('role');

        userUpdateForm.addEventListener('submit', function (event) {
            if (userUpdateForm.dataset.confirmed === 'true' || roleSelect.value === roleSelect.dataset.originalRole) {
                return;
            }

            event.preventDefault();
            document.getElementById('role-change-modal').showModal();
        });

        document.getElementById('role-change-modal-confirm').addEventListener('click', function () {
            userUpdateForm.dataset.confirmed = 'true';
            userUpdateForm.requestSubmit();
        });

        // Show only the fields that belong to the selected role.
        (function () {
            const form = userUpdateForm;
            const select = roleSelect;

            function applyRole(role) {
                form.querySelectorAll('[data-role-panel]').forEach(function (panel) {
                    const visible = panel.dataset.rolePanel === role;

                    panel.classList.toggle('hidden', ! visible);

                    panel.querySelectorAll('input, select, textarea').forEach(function (field) {
                        field.disabled = ! visible;

                        if (field.dataset.requiredFor) {
                            field.required = field.dataset.requiredFor.split(' ').includes(role);
                        }
                    });
                });
            }

            select.addEventListener('change', function () {
                applyRole(select.value);
            });

            applyRole(select.value);
        })();
    </script>
    @endpush

</x-layout>
