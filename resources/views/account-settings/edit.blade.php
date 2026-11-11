<x-dynamic-component :component="$layout" title="Account Settings" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Account Settings</h2>
            <p class="mt-1 text-sm text-gray-500">Manage the details you use to sign in. Changes only affect your own account.</p>
        </div>
        <x-ui.back-button :href="route($user->dashboardRoute())" label="Back to dashboard" />
    </div>

    <x-ui.card title="Account Information" class="mb-6">
        <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs uppercase text-gray-500">Role</dt>
                <dd class="mt-0.5 font-medium">{{ $user->roleLabel() }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase text-gray-500">Login Email</dt>
                <dd class="mt-0.5 font-medium">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase text-gray-500">Account Created</dt>
                <dd class="mt-0.5 font-medium">{{ $user->created_at?->format('M j, Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs uppercase text-gray-500">Last Login</dt>
                <dd class="mt-0.5 font-medium">{{ $user->last_login_at?->format('M j, Y g:i A') ?? '—' }}</dd>
            </div>
        </dl>
    </x-ui.card>

    <x-ui.card title="Personal Information">
        <form method="POST" action="{{ route('account-settings.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <hr class="border-gray-100">
            <p class="text-sm text-gray-500">Leave the password fields blank to keep your current password.</p>

            <div>
                <label for="current_password" class="mb-1 block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-500">Required when changing your email or setting a new password.</p>
            </div>

            <div>
                <label for="new_password" class="mb-1 block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" id="new_password" name="new_password" autocomplete="new-password"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-500">At least 8 characters.</p>
            </div>

            <div>
                <label for="new_password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <x-ui.button type="submit">Save Changes</x-ui.button>
        </form>
    </x-ui.card>

</x-dynamic-component>
