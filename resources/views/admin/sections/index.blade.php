<x-layout title="Manage Sections" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">All Sections</h2>
        <x-ui.button href="{{ route('admin.sections.create') }}">+ Add Section</x-ui.button>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, grade level, or school year"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Grade Level</th>
                    <th class="px-4 py-3 text-left">School Year</th>
                    <th class="px-4 py-3 text-left">Adviser</th>
                    <th class="px-4 py-3 text-left">Students</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($sections as $section)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $section->name }}</td>
                        <td class="px-4 py-3">{{ $section->grade_level }}</td>
                        <td class="px-4 py-3">{{ $section->school_year }}</td>
                        <td class="px-4 py-3">{{ $section->adviser->name ?? '— Unassigned' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span>{{ $section->students_count }}{{ $section->max_capacity ? ' / ' . $section->max_capacity : '' }}</span>
                                @if ($section->max_capacity)
                                    <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-600" style="width: {{ min(100, round($section->students_count / $section->max_capacity * 100)) }}%"></div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $section->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($section->status) }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 space-x-2 text-sm whitespace-nowrap">
                            <a href="{{ route('admin.sections.show', $section) }}" class="text-indigo-600 hover:underline">View</a>
                            <a href="{{ route('admin.sections.edit', $section) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.sections.toggle-status', $section) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button class="text-amber-600 hover:underline">{{ $section->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state message="No sections yet.">
                                <x-slot:action>
                                    <x-ui.button href="{{ route('admin.sections.create') }}" size="sm">+ Add your first section</x-ui.button>
                                </x-slot:action>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <div class="mt-4">{{ $sections->links() }}</div>

</x-layout>