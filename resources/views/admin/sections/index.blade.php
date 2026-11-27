<x-layout title="Manage Sections" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">All Sections</h2>
        <x-ui.button type="button" onclick="document.getElementById('add-section-modal').showModal()"><x-ui.icon name="plus" class="h-4 w-4" /> Add Section</x-ui.button>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <x-ui.form-field icon="search">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by name, grade level, or school year"
                           class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                </x-ui.form-field>
            </div>
            <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
            @if (request('search'))
                <a href="{{ route('admin.sections.index') }}" class="text-sm text-gray-500 hover:underline pb-2">Clear</a>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card>
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Grade Level</th>
                    <th class="px-4 py-3 text-left">School Year</th>
                    <th class="px-4 py-3 text-left">Teachers</th>
                    <th class="px-4 py-3 text-left">Students</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($sections as $section)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $sections->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-medium">{{ $section->name }}</td>
                        <td class="px-4 py-3">{{ $section->grade_level }}</td>
                        <td class="px-4 py-3">{{ $section->school_year }}</td>
                        <td class="px-4 py-3 max-w-md text-wrap text-slate-800">
                            <div class="flex flex-wrap items-center gap-y-1">
                                @forelse ($section->teachers as $teacher)
                                    <span>{{ $teacher->name }}</span>

                                    @if (! $loop->last)
                                        <span class="mx-2 px-1.5 py-0.5 text-xs font-bold text-indigo-700" aria-hidden="true">::</span>
                                    @endif
                                @empty
                                    <x-ui.optional-value fallback="Unassigned" />
                                @endforelse
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            {{ $section->students_count }}{{ $section->max_capacity ? ' / ' . $section->max_capacity : '' }}
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $section->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($section->status) }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 space-x-2 text-sm whitespace-nowrap">
                            <a href="{{ route('admin.sections.show', $section) }}" class="text-indigo-600 hover:underline">View/Edit</a>
                            <button type="button" onclick="document.getElementById('status-modal-{{ $section->id }}').showModal()" class="text-amber-600 hover:underline">{{ $section->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-ui.empty-state message="No sections yet." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <div class="mt-4">{{ $sections->links() }}</div>

    @foreach ($sections as $section)
        <x-ui.confirm-modal
            id="status-modal-{{ $section->id }}"
            title="{{ $section->status === 'active' ? 'Deactivate' : 'Activate' }} {{ $section->name }}?"
            form-action="{{ route('admin.sections.toggle-status', $section) }}"
            form-method="PATCH"
            confirm-label="{{ $section->status === 'active' ? 'Yes, deactivate' : 'Yes, activate' }}"
            warning="{{ $section->status === 'active' ? 'Inactive sections are hidden from active lists until reactivated.' : 'The section becomes active again immediately.' }}">
            This changes the section's status right away.
        </x-ui.confirm-modal>
    @endforeach

    <x-ui.form-modal id="add-section-modal" title="Add Section" action="{{ route('admin.sections.store') }}" submit-label="Save Section">
        @include('admin.sections._form')
    </x-ui.form-modal>

</x-layout>



