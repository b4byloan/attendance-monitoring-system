<x-layout
    :title="$activeSection ? $activeSection->name.' Students' : 'Student Information'"
    :unread-count="$unreadCount ?? 0"
    :recent-notifications="$recentNotifications ?? collect()"
>

    <div class="mb-6">
        @if ($activeSection)
            <x-ui.back-button :href="route('sub-admin.roster.index')" label="Back to my roster" class="mb-3" />
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-bold text-gray-900">{{ $activeSection->name }}</h2>
                <x-ui.badge color="green">Read-only</x-ui.badge>
            </div>
            <p class="text-sm text-gray-500">
                {{ $activeSection->grade_level }} · {{ $activeSection->school_year }} · {{ $students->total() }} student(s)
            </p>
        @else
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-bold text-gray-900">Student Information</h2>
                <x-ui.badge color="green">Read-only</x-ui.badge>
            </div>
            <p class="text-sm text-gray-500">Students enrolled in your assigned sections.</p>
        @endif
    </div>

    <x-ui.card class="mb-6">
        <form method="GET"
              action="{{ $activeSection ? route('sub-admin.sections.students', $activeSection) : route('sub-admin.students.index') }}"
              class="flex flex-wrap items-end gap-3">
            <div class="min-w-[200px] flex-1">
                <label for="student-search" class="mb-1 block text-xs text-gray-500">Search</label>
                <input id="student-search" type="text" name="search" value="{{ $search }}"
                       placeholder="Search by LRN, name, or ID"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
            @if ($search !== '')
                <a href="{{ $activeSection ? route('sub-admin.sections.students', $activeSection) : route('sub-admin.students.index') }}"
                   class="pb-2 text-sm text-gray-500 hover:underline">Clear</a>
            @endif
        </form>
    </x-ui.card>

    <div class="overflow-x-auto">
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">LRN</th>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Section</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Today</th>
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
                            <x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.attendance-status :status="$todayStatuses->get($student->id, 'not_yet')" />
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('sub-admin.students.show', $student) }}" class="text-sm text-emerald-700 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state message="No students found in your assigned sections." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </div>

    <div class="mt-4">{{ $students->links() }}</div>

</x-layout>
