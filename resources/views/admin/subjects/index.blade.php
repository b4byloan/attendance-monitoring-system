<x-layout title="Subject Management" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Subjects</h2>
            <p class="text-sm text-gray-500">Create subjects and assign the teachers who handle them.</p>
        </div>
        <x-ui.button type="button" onclick="document.getElementById('add-subject-modal').showModal()"><x-ui.icon name="plus" class="h-4 w-4" /> Add Subject</x-ui.button>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <x-ui.form-field icon="search">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by name or code"
                           class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                </x-ui.form-field>
            </div>
            <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
            @if (request('search'))
                <a href="{{ route('admin.subjects.index') }}" class="text-sm text-gray-500 hover:underline pb-2">Clear</a>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card>
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-left">Code</th>
                    <th class="px-4 py-3 text-left">Teachers</th>
                    <th class="px-4 py-3 text-left">Records</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($subjects as $subject)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $subjects->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-medium">{{ $subject->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $subject->code }}</td>
                        <td class="px-4 py-3">{{ $subject->teachers_count }}</td>
                        <td class="px-4 py-3">{{ $subject->attendance_records_count }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $subject->isActive() ? 'green' : 'gray' }}">{{ ucfirst($subject->status) }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 space-x-2 text-sm whitespace-nowrap">
                            <a href="{{ route('admin.subjects.show', $subject) }}" class="text-indigo-600 hover:underline">View/Edit</a>
                            <button type="button" onclick="document.getElementById('subject-status-modal-{{ $subject->id }}').showModal()" class="text-amber-600 hover:underline">
                                {{ $subject->isActive() ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button type="button" onclick="document.getElementById('subject-delete-modal-{{ $subject->id }}').showModal()" class="text-red-600 hover:underline">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state message="No subjects yet." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <div class="mt-4">{{ $subjects->links() }}</div>

    @foreach ($subjects as $subject)
        <x-ui.confirm-modal
            id="subject-status-modal-{{ $subject->id }}"
            title="{{ $subject->isActive() ? 'Deactivate' : 'Activate' }} {{ $subject->name }}?"
            form-action="{{ route('admin.subjects.toggle-status', $subject) }}"
            form-method="PATCH"
            confirm-label="{{ $subject->isActive() ? 'Yes, deactivate' : 'Yes, activate' }}"
            warning="{{ $subject->isActive() ? 'Inactive subjects disappear from the teacher scanner until reactivated.' : 'The subject becomes available in the teacher scanner immediately.' }}">
            This changes the subject status right away.
        </x-ui.confirm-modal>

        <x-ui.confirm-modal
            id="subject-delete-modal-{{ $subject->id }}"
            title="Delete {{ $subject->name }}?"
            form-action="{{ route('admin.subjects.destroy', $subject) }}"
            form-method="DELETE"
            confirm-label="Yes, delete">
            Subjects with existing attendance records cannot be deleted; deactivate them instead.
        </x-ui.confirm-modal>
    @endforeach

    <x-ui.form-modal id="add-subject-modal" title="Add Subject" action="{{ route('admin.subjects.store') }}" submit-label="Create Subject">
        @include('admin.subjects._form', ['showActions' => false])
    </x-ui.form-modal>

</x-layout>
