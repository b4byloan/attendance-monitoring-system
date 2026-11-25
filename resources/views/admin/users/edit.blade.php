<x-layout title="Edit User" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    @php
        $roleColors = [
            'admin' => 'indigo',
            'sub_admin' => 'blue',
            'teacher' => 'green',
            'guard' => 'amber',
            'parent' => 'purple',
            'student' => 'gray',
        ];
    @endphp

    <div class="max-w-xl mx-auto space-y-6">

        <x-ui.back-button :href="route('admin.users.index')" label="Back to users" />

        <x-ui.card>
            <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
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
            <form id="user-update-form" method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
                    @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select id="role" name="role" data-original-role="{{ $user->role }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('role') border-red-400 @enderror">
                        @foreach (\App\Models\User::roleOptions() as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Sections (Teachers / Sub-Admins)</label>
                    @php $selectedSections = old('section_ids', $user->sections->pluck('id')->all()); @endphp
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
                        <p class="mt-1 text-xs text-gray-500">A teacher can be assigned to multiple sections. Ignored for other roles.</p>
                    @endif
                </div>
                <div class="rounded-lg border border-gray-200 p-3">
                    <p class="text-sm font-medium text-gray-700">Change Password (optional)</p>
                    <div class="mt-3 space-y-3">
                        <x-ui.password-input name="password" placeholder="Leave blank to keep the current password" class="@error('password') border-red-400 @enderror" />
                        @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        <x-ui.password-input name="password_confirmation" placeholder="Confirm new password" />
                    </div>
                </div>
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

        <x-ui.card title="Reset Password">
            <p class="text-sm text-gray-500 mb-3">
                Sends a password reset link to <strong>{{ $user->email }}</strong>.
                The user must open the email and choose a new password — no password is generated or shown here.
            </p>
            <x-ui.button type="button" variant="secondary" onclick="document.getElementById('reset-password-modal').showModal()">
                Send Reset Email
            </x-ui.button>
        </x-ui.card>

    </div>

    <x-ui.confirm-modal
        id="reset-password-modal"
        title="Send a password reset email to {{ $user->name }}?"
        form-action="{{ route('admin.users.reset-password', $user) }}"
        form-method="PATCH"
        confirm-label="Yes, send reset email"
        warning="The current password stays active until the user opens the email and sets a new one.">
        A password reset link will be sent to <strong>{{ $user->email }}</strong>.
    </x-ui.confirm-modal>

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
    </script>
    @endpush

</x-layout>
