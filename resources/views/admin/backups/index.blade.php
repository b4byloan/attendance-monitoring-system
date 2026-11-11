<x-layout title="Backup & Restore" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

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

        <x-ui.confirm-modal
            id="restore-modal-{{ $loop->index }}"
            title="Restore this backup?"
            form-action="{{ route('admin.backups.restore', $backup['filename']) }}"
            confirm-label="Restore Now"
            confirm-phrase="RESTORE"
            warning="All current data is replaced by the backup from {{ $backup['created_at']->format('M j, Y h:i A') }}.">
            Anything added or changed since then will no longer be in the live system. A fresh safety backup of the current data is taken automatically first.
        </x-ui.confirm-modal>
    @endforeach

</x-layout>