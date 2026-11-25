@php
    $roleColors = [
        'admin' => 'indigo',
        'sub_admin' => 'blue',
        'teacher' => 'green',
        'guard' => 'amber',
        'parent' => 'purple',
        'student' => 'gray',
    ];
    $showSections = $showSections ?? false;
@endphp

<x-ui.table>
    <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
        <tr>
            <th class="px-4 py-3 text-left">#</th>
            <x-ui.sortable-th column="name" label="Name" />
            <x-ui.sortable-th column="email" label="Email" />
            <x-ui.sortable-th column="role" label="Role" />
            @if ($showSections)
                <th class="px-4 py-3 text-left">Assigned Sections</th>
            @endif
            <x-ui.sortable-th column="status" label="Status" />
            <x-ui.sortable-th column="last_login" label="Last Login" />
            <th class="px-4 py-3 text-left">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y">
        @forelse ($accounts as $account)
            <tr>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $accounts->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3 font-medium">{{ $account->name }}</td>
                <td class="px-4 py-3">{{ $account->email }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge color="{{ $roleColors[$account->role] ?? 'gray' }}">{{ $account->roleLabel() }}</x-ui.badge>
                </td>
                @if ($showSections)
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $account->sections->pluck('name')->join(', ') ?: '—' }}
                    </td>
                @endif
                <td class="px-4 py-3">
                    <x-ui.badge color="{{ $account->is_active ? 'green' : 'gray' }}">{{ $account->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $account->last_login_at?->format('M j, Y h:i A') ?? 'Never' }}</td>
                <td class="px-4 py-3 space-x-2 text-sm whitespace-nowrap">
                    <a href="{{ route('admin.users.edit', $account) }}" class="text-indigo-600 hover:underline">Edit</a>
                    @if ($account->id !== auth()->id())
                        <button type="button" onclick="document.getElementById('account-status-modal-{{ $account->id }}').showModal()" class="text-amber-600 hover:underline">
                            {{ $account->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        <button type="button" onclick="document.getElementById('account-delete-modal-{{ $account->id }}').showModal()" class="text-red-600 hover:underline">Delete</button>
                    @else
                        <span class="text-gray-400 text-xs">(you)</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $showSections ? 8 : 7 }}">
                    <x-ui.empty-state message="No accounts found." />
                </td>
            </tr>
        @endforelse
    </tbody>
</x-ui.table>

@foreach ($accounts as $account)
    @if ($account->id !== auth()->id())
        <x-ui.confirm-modal
            id="account-status-modal-{{ $account->id }}"
            title="{{ $account->is_active ? 'Deactivate' : 'Activate' }} {{ $account->name }}?"
            form-action="{{ route('admin.users.toggle-status', $account) }}"
            form-method="PATCH"
            confirm-label="{{ $account->is_active ? 'Yes, deactivate' : 'Yes, activate' }}"
            warning="{{ $account->is_active ? 'The account is blocked from signing in immediately.' : 'The account can sign in again immediately.' }}">
            This changes the account status right away.
        </x-ui.confirm-modal>

        <x-ui.confirm-modal
            id="account-delete-modal-{{ $account->id }}"
            title="Delete {{ $account->name }}?"
            form-action="{{ route('admin.users.destroy', $account) }}"
            form-method="DELETE"
            confirm-label="Yes, delete">
            This permanently removes the account. The last remaining Admin cannot be deleted.
        </x-ui.confirm-modal>
    @endif
@endforeach
