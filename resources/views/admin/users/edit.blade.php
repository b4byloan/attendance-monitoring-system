<x-layout title="Edit User" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    @php $roleColors = ['admin' => 'indigo', 'sub_admin' => 'blue', 'guard' => 'amber', 'parent' => 'purple']; @endphp

    <div class="max-w-xl mx-auto space-y-6">

        <x-ui.card>
            <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-ui.badge color="{{ $roleColors[$user->role] ?? 'gray' }}">{{ ucwords(str_replace('_', ' ', $user->role)) }}</x-ui.badge>
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
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
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
                    <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('role') border-red-400 @enderror">
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                        <option value="sub_admin" @selected(old('role', $user->role) === 'sub_admin')>Sub-Admin</option>
                        <option value="guard" @selected(old('role', $user->role) === 'guard')>Guard</option>
                        <option value="parent" @selected(old('role', $user->role) === 'parent')>Parent</option>
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
            <p class="text-sm text-gray-500 mb-3">Sets a new password immediately. The current one is never shown — there's no way to view it, only replace it.</p>
            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="flex flex-wrap gap-2 items-start">
                @csrf
                @method('PATCH')
                <div class="flex-1 min-w-[200px]">
                    <input type="password" name="password" autocomplete="new-password" placeholder="New password (min. 8 characters)"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-400 @enderror">
                    @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <x-ui.button type="submit" variant="secondary">Reset Password</x-ui.button>
            </form>
        </x-ui.card>

    </div>

</x-layout>