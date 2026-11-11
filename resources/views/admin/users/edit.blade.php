<x-layout title="Edit User" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    @php $roleColors = ['admin' => 'indigo', 'sub_admin' => 'blue', 'guard' => 'amber', 'parent' => 'purple']; @endphp

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
                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Save Changes</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('admin.users.index') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card title="Reset Password">
            <p class="text-sm text-gray-500 mb-3">
                Generates a strong random password on the server and replaces the current one immediately.
                Nothing is emailed to the user — you will see the new password once and must share it securely yourself.
            </p>
            <x-ui.button type="button" variant="secondary" onclick="document.getElementById('reset-password-modal').showModal()">
                Reset Password
            </x-ui.button>
        </x-ui.card>

    </div>

    <x-ui.confirm-modal
        id="reset-password-modal"
        title="Reset password for {{ $user->name }}?"
        form-action="{{ route('admin.users.reset-password', $user) }}"
        form-method="PATCH"
        confirm-label="Yes, reset password">
        The current password stops working immediately and a new random password is generated on the server.
        This cannot be undone.
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

    @if (session('generated_password'))
        <dialog id="generated-password-modal" class="w-full max-w-md rounded-lg p-0 shadow-xl backdrop:bg-black/40">
            <div class="p-6">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">New password for {{ $user->name }}</h3>
                <p class="mb-4 text-sm text-gray-600">A new password was generated on the server.</p>

                <div class="mb-4 flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                    <code id="generated-password" class="flex-1 break-all font-mono text-sm text-gray-900">{{ session('generated_password') }}</code>
                    <x-ui.button type="button" size="sm" variant="secondary" id="copy-generated-password">Copy</x-ui.button>
                </div>

                <p class="mb-4 flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800">
                    <x-ui.icon name="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>This password will not be shown again. Copy it now and share it securely with the user.</span>
                </p>

                <div class="flex justify-end">
                    <x-ui.button type="button" variant="secondary" onclick="document.getElementById('generated-password-modal').close()">
                        Done
                    </x-ui.button>
                </div>
            </div>
        </dialog>
    @endif

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

        @if (session('generated_password'))
            const generatedPasswordModal = document.getElementById('generated-password-modal');
            generatedPasswordModal.showModal();

            document.getElementById('copy-generated-password').addEventListener('click', async function () {
                const password = document.getElementById('generated-password').textContent.trim();

                try {
                    await navigator.clipboard.writeText(password);
                } catch (error) {
                    const clipboardFallback = document.createElement('textarea');
                    clipboardFallback.value = password;
                    document.body.appendChild(clipboardFallback);
                    clipboardFallback.select();
                    document.execCommand('copy');
                    clipboardFallback.remove();
                }

                this.textContent = 'Copied';
            });
        @endif
    </script>
    @endpush

</x-layout>
