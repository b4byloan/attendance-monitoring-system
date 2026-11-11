<x-layout
    title="My Roster"
    :unread-count="$unreadCount ?? 0"
    :recent-notifications="$recentNotifications ?? collect()"
>

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">My Roster</h2>
        <p class="text-sm text-gray-500">Sections assigned to you and the students enrolled in each.</p>
    </div>

    @forelse ($sections as $section)
        <x-ui.card class="mb-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $section->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $section->grade_level }} · {{ $section->school_year }}</p>
                </div>
                <x-ui.badge color="{{ $section->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($section->status) }}</x-ui.badge>
            </div>

            <div class="mt-4 grid max-w-md grid-cols-2 gap-3">
                <div class="rounded-lg bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Total Students</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $section->students_count }}</p>
                </div>
                <div class="rounded-lg bg-green-50 p-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Present Today</p>
                    <p class="mt-1 text-2xl font-bold text-green-700">{{ $section->present_today }}</p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <x-ui.table>
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">LRN</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Today</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($section->students as $student)
                            <tr>
                                <td class="px-4 py-3">{{ $student->full_name }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $student->lrn }}</td>
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
                                <td colspan="5"><x-ui.empty-state message="No students assigned to this section yet." /></td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            <a href="{{ route('sub-admin.sections.students', $section) }}"
               class="mt-4 inline-flex items-center justify-center gap-1 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                View student information <x-ui.icon name="arrow-right" class="h-4 w-4" />
            </a>
        </x-ui.card>
    @empty
        <x-ui.card>
            <x-ui.empty-state message="You haven't been assigned as adviser to any section yet — contact your admin." />
        </x-ui.card>
    @endforelse

</x-layout>
