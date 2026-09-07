<x-layout title="Backup & Restore" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex gap-4 mb-6 text-sm">
        <a href="{{ route('admin.settings.edit') }}" class="text-gray-500 hover:text-indigo-600">School Settings</a>
        <span class="text-gray-300">|</span>
        <span class="font-medium text-indigo-700">Backup &amp; Restore</span>
    </div>

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Backups</h2>
        <form method="POST" action="{{ route('admin.backups.store') }}">
            @csrf
            <x-ui.button type="submit">+ Create Backup Now</x-ui.button>
        </form>
    </div>

    @if (session('success'))
        <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.card>
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Filename</th>
                    <th class="px-4 py-3 text-left">Created</th>
                    <th class="px-4 py-3 text-left">Size</th>
                    <th class="px-4 py-3 text-left">Created By</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($backups as $backup)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs">{{ $backup['filename'] }}</td>
                        <td class="px-4 py-3 text-sm">{{ $backup['created_at']->format('M j, Y h:i A') }}</td>
                        <td class="px-4 py-3 text-sm">{{ number_format($backup['size'] / 1024 / 1024, 2) }} MB</td>
                        <td class="px-4 py-3 text-sm">{{ $backup['created_by'] }}</td>
                        <td class="px-4 py-3"><x-ui.badge color="green">Ready</x-ui.badge></td>
                        <td class="px-4 py-3 space-x-2 text-sm whitespace-nowrap">
                            <a href="{{ route('admin.backups.download', $backup['filename']) }}" class="text-indigo-600 hover:underline">Download</a>
                            <button type="button" onclick="document.getElementById('restore-modal-{{ $loop->index }}').showModal()" class="text-red-700 font-semibold hover:underline">
                                Restore
                            </button>
                            <button type="button" onclick="document.getElementById('delete-modal-{{ $loop->index }}').showModal()" class="text-red-600 hover:underline">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state message="No backups yet - create the first one above." /></td></tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>

    @foreach ($backups as $backup)
        <x-ui.confirm-modal
            id="delete-modal-{{ $loop->index }}"
            title="Delete this backup?"
            form-action="{{ route('admin.backups.destroy', $backup['filename']) }}"
            form-method="DELETE"
            confirm-label="Yes, delete">
            This permanently removes {{ $backup['filename'] }}. This cannot be undone.
        </x-ui.confirm-modal>

        <dialog id="restore-modal-{{ $loop->index }}" class="rounded-lg shadow-xl p-0 w-full max-w-md backdrop:bg-black/40">
            <div class="p-6">
                <h3 class="text-lg font-bold text-red-700 mb-2">⚠ Restore this backup?</h3>
                <p class="text-sm text-gray-600 mb-2">
                    This replaces <strong>all current data</strong> with what's in this backup, from
                    {{ $backup['created_at']->format('M j, Y h:i A') }}. Anything added or changed since then
                    will no longer be in the live system.
                </p>
                <p class="text-sm text-gray-600 mb-4">
                    A fresh safety backup of your <em>current</em> data is taken automatically first, so this isn't permanent even if something goes wrong.
                </p>
                <form method="POST" action="{{ route('admin.backups.restore', $backup['filename']) }}"
                      onsubmit="return this.querySelector('[name=confirmation]').value === 'RESTORE';">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type <strong>RESTORE</strong> to confirm</label>
                    <input type="text" name="confirmation" placeholder="RESTORE"
                           class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('restore-modal-{{ $loop->index }}').close()"
                                class="px-4 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-red-700 text-white hover:bg-red-800">Restore Now</button>
                    </div>
                </form>
            </div>
        </dialog>
    @endforeach

</x-layout>