@php
    $roleColors = [
        'admin' => 'indigo',
        'sub_admin' => 'green',
        'teacher' => 'green',
        'guard' => 'amber',
        'parent' => 'purple',
        'student' => 'gray',
    ];
    $showSections = $showSections ?? false;
@endphp

<x-ui.table :fit="true">
    <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-700">
        <tr>
            <th scope="col" class="w-12 px-4 py-3 text-left font-semibold">#</th>
            <x-ui.sortable-th column="name" label="Name" class="font-semibold" />
            <x-ui.sortable-th column="email" label="Email" class="font-semibold" />
            <x-ui.sortable-th column="role" label="Role" class="font-semibold" />
            @if ($showSections)
                <th scope="col" class="px-4 py-3 text-left font-semibold">Assigned Sections</th>
            @endif
            <x-ui.sortable-th column="status" label="Status" class="font-semibold" />
            <x-ui.sortable-th column="last_login" label="Last Login" class="font-semibold" />
            <th scope="col" class="px-4 py-3 text-left font-semibold">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200 bg-white">
        @forelse ($accounts as $account)
            <tr class="bg-white transition-colors hover:bg-gray-50">
                <td class="px-4 py-4 text-sm font-medium text-gray-500">{{ $accounts->firstItem() + $loop->index }}</td>
                <td class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :name="$account->name" size="xs" />
                        <span class="whitespace-nowrap font-semibold text-gray-900">{{ $account->name }}</span>
                    </div>
                </td>
                <td class="px-4 py-4">
                    <span class="flex flex-wrap items-center gap-2 break-all text-sm text-gray-500">
                        {{ $account->email }}
                        <x-ui.copy-button :value="$account->email" what="email" :icon-only="true" />
                    </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <x-ui.badge color="{{ $roleColors[$account->role] ?? 'gray' }}">{{ $account->roleLabel() }}</x-ui.badge>
                </td>
                @if ($showSections)
                    <td class="px-4 py-4">
                        <div class="flex max-w-[240px] flex-wrap gap-1.5">
                            @forelse ($account->sections as $section)
                                <span class="inline-flex items-center rounded-sm bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                                    {{ $section->name }}
                                </span>
                            @empty
                                <x-ui.optional-value />
                            @endforelse
                        </div>
                    </td>
                @endif
                <td class="px-4 py-4 whitespace-nowrap">
                    <x-ui.badge color="{{ $account->is_active ? 'green' : 'gray' }}">{{ $account->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $account->last_login_at?->format('M j, Y h:i A') ?? 'Never' }}</td>
                <td class="px-4 py-4 text-sm">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <a href="{{ route('admin.users.edit', $account) }}" class="whitespace-nowrap font-medium text-blue-600 hover:underline">
                            Edit
                        </a>
                        <button type="button" onclick="document.getElementById('account-reset-modal-{{ $account->id }}').showModal()" class="whitespace-nowrap font-medium text-emerald-600 hover:underline">
                            Reset password
                        </button>
                        @if ($account->id !== auth()->id())
                            <button type="button" onclick="document.getElementById('account-status-modal-{{ $account->id }}').showModal()" class="whitespace-nowrap font-medium text-amber-600 hover:underline">
                                {{ $account->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button type="button" onclick="document.getElementById('account-delete-modal-{{ $account->id }}').showModal()" class="whitespace-nowrap font-medium text-red-600 hover:underline">Delete</button>
                        @else
                            <span class="whitespace-nowrap text-xs text-gray-400">(you)</span>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr class="bg-white">
                <td colspan="{{ $showSections ? 8 : 7 }}" class="px-4 py-6 text-center text-sm text-gray-500">
                    <x-ui.empty-state message="No accounts found." />
                </td>
            </tr>
        @endforelse
    </tbody>
</x-ui.table>

@foreach ($accounts as $account)
    @if ($account->role === 'admin')
        <x-ui.confirm-modal
            id="account-reset-modal-{{ $account->id }}"
            title="Set a new password for {{ $account->name }}?"
            form-action="{{ route('admin.users.set-password', $account) }}"
            form-method="PATCH"
            confirm-label="Set password"
            warning="The new password takes effect immediately. Share it with the admin through a secure channel.">
            <x-slot:fields>
                <div>
                    <label for="new-password-{{ $account->id }}" class="mb-1 block text-sm font-medium text-gray-700">New Password</label>
                    <x-ui.password-input name="password" id="new-password-{{ $account->id }}" />
                    <p class="mt-1 text-xs text-gray-500">At least 8 characters.</p>
                </div>
                <div>
                    <label for="new-password-confirmation-{{ $account->id }}" class="mb-1 block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <x-ui.password-input name="password_confirmation" id="new-password-confirmation-{{ $account->id }}" />
                </div>
            </x-slot:fields>
            Enter the new password for this admin account.
        </x-ui.confirm-modal>
    @else
        <x-ui.confirm-modal
            id="account-reset-modal-{{ $account->id }}"
            title="Send a password reset email to {{ $account->name }}?"
            form-action="{{ route('admin.users.reset-password', $account) }}"
            form-method="PATCH"
            confirm-label="Yes, send reset email"
            warning="The current password stays active until the user opens the email and sets a new one.">
            A password reset link will be sent to <strong>{{ $account->email }}</strong>.
        </x-ui.confirm-modal>
    @endif

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
