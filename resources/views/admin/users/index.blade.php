<x-layout title="Manage Users" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    @php $roleColors = ['admin' => 'indigo', 'sub_admin' => 'blue', 'guard' => 'amber', 'parent' => 'purple']; @endphp

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">All Users</h2>
        <x-ui.button href="{{ route('admin.users.create') }}">+ Add User</x-ui.button>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, email, or role"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Role</label>
                <select name="role" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Roles</option>
                    @foreach (\App\Models\User::roleOptions() as $value => $label)
                        <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
            @if (request('search') || request('role'))
                <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:underline pb-2">Clear</a>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card>
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Last Login</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $roleColors[$user->role] ?? 'gray' }}">{{ $user->roleLabel() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $user->is_active ? 'green' : 'gray' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $user->last_login_at?->format('M j, Y h:i A') ?? 'Never' }}</td>
                        <td class="px-4 py-3 space-x-2 text-sm whitespace-nowrap">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:underline">Edit</a>
                            @if ($user->id !== auth()->id())
                                <button type="button" onclick="document.getElementById('status-modal-{{ $user->id }}').showModal()" class="text-amber-600 hover:underline">
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button type="button" onclick="document.getElementById('delete-modal-{{ $user->id }}').showModal()" class="text-red-600 hover:underline">
                                    Delete
                                </button>
                            @else
                                <span class="text-gray-400 text-xs">(you)</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state message="No users found." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <div class="mt-4">{{ $users->links() }}</div>

    @foreach ($users as $user)
        @if ($user->id !== auth()->id())
            <x-ui.confirm-modal
                id="status-modal-{{ $user->id }}"
                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }} {{ $user->name }}?"
                form-action="{{ route('admin.users.toggle-status', $user) }}"
                form-method="PATCH"
                confirm-label="{{ $user->is_active ? 'Yes, deactivate' : 'Yes, activate' }}"
                warning="{{ $user->is_active ? 'The account is blocked from signing in immediately. You can activate it again later.' : 'The account will be able to sign in again immediately.' }}">
                This changes the account status right away.
            </x-ui.confirm-modal>

            <x-ui.confirm-modal
                id="delete-modal-{{ $user->id }}"
                title="Delete {{ $user->name }}?"
                form-action="{{ route('admin.users.destroy', $user) }}"
                form-method="DELETE"
                confirm-label="Yes, delete">
                This permanently removes the account. If they're the last Admin account, this will be blocked automatically.
            </x-ui.confirm-modal>
        @endif
    @endforeach

</x-layout>
