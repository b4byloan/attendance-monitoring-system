<x-layout title="Manage Guardians" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">All Guardians</h2>
        <x-ui.button href="{{ route('admin.guardians.create') }}">+ Add Guardian</x-ui.button>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, email, or phone"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
            @if (request('search'))
                <a href="{{ route('admin.guardians.index') }}" class="text-sm text-gray-500 hover:underline pb-2">Clear</a>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card>
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Contact</th>
                    <th class="px-4 py-3 text-left">Relationship</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Students</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($guardians as $guardian)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $guardian->first_name }} {{ $guardian->last_name }}</td>
                        <td class="px-4 py-3">
                            <div>{{ $guardian->phone_number }}</div>
                            @if ($guardian->email)<div class="text-xs text-gray-400">{{ $guardian->email }}</div>@endif
                        </td>
                        <td class="px-4 py-3">{{ $guardian->relationship_to_student ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $guardian->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($guardian->status) }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3">{{ $guardian->students_count }}</td>
                        <td class="px-4 py-3 space-x-2 text-sm whitespace-nowrap">
                            <a href="{{ route('admin.guardians.show', $guardian) }}" class="text-indigo-600 hover:underline">View</a>
                            <a href="{{ route('admin.guardians.edit', $guardian) }}" class="text-indigo-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state message="No guardians yet.">
                                <x-slot:action>
                                    <x-ui.button href="{{ route('admin.guardians.create') }}" size="sm">+ Add your first guardian</x-ui.button>
                                </x-slot:action>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <div class="mt-4">{{ $guardians->links() }}</div>

</x-layout>