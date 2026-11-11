<x-layout title="Manage Students" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">All Students</h2>
        <x-ui.button href="{{ route('admin.students.create') }}"><x-ui.icon name="plus" class="h-4 w-4" /> Add Student</x-ui.button>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by LRN, name, or ID"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
            @if (request('search'))
                <a href="{{ route('admin.students.index') }}" class="text-sm text-gray-500 hover:underline pb-2">Clear</a>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card>
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">LRN</th>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Section</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($students as $student)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="flex items-center gap-2">
                                <span class="font-mono text-xs">{{ $student->lrn }}</span>
                                <x-ui.copy-button :value="$student->lrn" />
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $student->full_name }}</td>
                        <td class="px-4 py-3">{{ $student->section->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">
                                {{ ucfirst($student->status) }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3 space-x-2 text-sm whitespace-nowrap">
                            <a href="{{ route('admin.students.show', $student) }}" class="text-indigo-600 hover:underline">View</a>
                            <a href="{{ route('admin.students.edit', $student) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <a href="{{ route('admin.students.qr-code.show', $student) }}" class="text-purple-600 hover:underline">QR</a>
                            <button type="button" onclick="document.getElementById('status-modal-{{ $student->id }}').showModal()" class="text-amber-600 hover:underline">
                                {{ $student->status === 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button type="button" onclick="document.getElementById('delete-modal-{{ $student->id }}').showModal()" class="text-red-600 hover:underline">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-ui.empty-state message="No students yet.">
                                <x-slot:action>
                                    <x-ui.button href="{{ route('admin.students.create') }}" size="sm">+ Add your first student</x-ui.button>
                                </x-slot:action>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <div class="mt-4">{{ $students->links() }}</div>

    @foreach ($students as $student)
        <x-ui.confirm-modal
            id="status-modal-{{ $student->id }}"
            title="{{ $student->status === 'active' ? 'Deactivate' : 'Activate' }} {{ $student->full_name }}?"
            form-action="{{ route('admin.students.toggle-status', $student) }}"
            form-method="PATCH"
            confirm-label="{{ $student->status === 'active' ? 'Yes, deactivate' : 'Yes, activate' }}"
            warning="{{ $student->status === 'active' ? 'The student is skipped by the scanner until reactivated.' : 'The student becomes scannable again immediately.' }}">
            This changes the student's status right away.
        </x-ui.confirm-modal>

        <x-ui.confirm-modal
            id="delete-modal-{{ $student->id }}"
            title="Delete {{ $student->full_name }}?"
            form-action="{{ route('admin.students.destroy', $student) }}"
            form-method="DELETE"
            confirm-label="Yes, delete">
            This only works if this student has no attendance history. Otherwise, deactivating them is the correct option instead.
        </x-ui.confirm-modal>
    @endforeach

</x-layout>