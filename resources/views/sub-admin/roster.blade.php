<x-layout
    title="Student List"
    :unread-count="$unreadCount ?? 0"
    :recent-notifications="$recentNotifications ?? collect()"
>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Student List</h2>
            <p class="text-sm text-gray-500">
                @if ($tab === 'sections')
                    The sections you handle, their students, and today's attendance.
                @else
                    Every class attendance scan in your sections — across all subjects and sections — newest first.
                @endif
            </p>
        </div>
        @if ($tab === 'attendance')
            <div class="flex flex-wrap gap-2">
                <x-ui.button href="{{ route('sub-admin.roster.export-pdf', request()->query()) }}" variant="secondary" size="sm">Export PDF</x-ui.button>
                <x-ui.button href="{{ route('sub-admin.roster.export-excel', request()->query()) }}" variant="secondary" size="sm">Export Excel</x-ui.button>
                <x-ui.button href="{{ route('sub-admin.roster.export-csv', request()->query()) }}" variant="secondary" size="sm">Export CSV</x-ui.button>
            </div>
        @endif
    </div>

    <div class="mb-6 overflow-x-auto border-b border-gray-200">
        <nav class="flex min-w-max gap-1" aria-label="Student list sections">
            <a href="{{ route('sub-admin.roster.index') }}"
               @class([
                   'inline-flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition',
                   'border-emerald-600 text-emerald-700' => $tab === 'attendance',
                   'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => $tab !== 'attendance',
               ])>
                Attendance Log
            </a>
            <a href="{{ route('sub-admin.roster.index', ['tab' => 'sections']) }}"
               @class([
                   'inline-flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition',
                   'border-emerald-600 text-emerald-700' => $tab === 'sections',
                   'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => $tab !== 'sections',
               ])>
                Sections
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ $sectionTabCount }}</span>
            </a>
        </nav>
    </div>

    @if ($tab === 'sections')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-3">
            @forelse ($sections as $section)
                <x-ui.section-card :section="$section" />
            @empty
                <x-ui.card class="sm:col-span-2 2xl:col-span-3">
                    <x-ui.empty-state message="No sections are assigned to you yet — contact your admin." />
                </x-ui.card>
            @endforelse
        </div>
    @else
        <x-ui.card class="mb-6">
            <form method="GET" action="{{ route('sub-admin.roster.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="min-w-[220px] flex-1">
                    <label for="scan-search" class="mb-1 block text-xs text-gray-500">Search</label>
                    <input id="scan-search" type="text" name="search" value="{{ $search }}"
                           placeholder="Search by student name or LRN"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="min-w-[180px]">
                    <label for="scan-subject" class="mb-1 block text-xs text-gray-500">Subject</label>
                    <select id="scan-subject" name="subject"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">All subjects</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($selectedSubjectId === $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[180px]">
                    <label for="scan-date" class="mb-1 block text-xs text-gray-500">Timestamp</label>
                    <input id="scan-date" type="date" name="date" value="{{ $selectedDate }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <x-ui.button type="submit" variant="secondary">Apply Filters</x-ui.button>

                @if ($search !== '' || $selectedSubjectId || $selectedDate !== '')
                    <a href="{{ route('sub-admin.roster.index') }}" class="pb-2 text-sm text-gray-500 hover:underline">Clear</a>
                @endif
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm text-gray-500">
                    <span class="font-semibold text-gray-900">{{ $records->total() }}</span>
                    {{ Str::plural('scan', $records->total()) }} found
                </p>
            </div>

            <div class="overflow-x-auto">
                <x-ui.table>
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Timestamp</th>
                            <th class="px-4 py-3 text-left">Student Name</th>
                            <th class="px-4 py-3 text-left">LRN</th>
                            <th class="px-4 py-3 text-left">Section</th>
                            <th class="px-4 py-3 text-left">Subject</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($records as $record)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $records->firstItem() + $loop->index }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="font-medium text-gray-900">{{ $record->scanned_at->format('M j, Y') }}</span>
                                    <span class="block text-xs text-gray-400">{{ $record->scanned_at->format('h:i A') }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $record->student->full_name }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $record->student->lrn }}</td>
                                <td class="px-4 py-3">{{ $record->student->section->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $record->subject->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-ui.badge color="{{ $record->status === 'present' ? 'green' : 'gray' }}">{{ ucfirst($record->status) }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3">{{ $record->recordedBy->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-ui.empty-state message="No scanned students match these filters." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            <div class="mt-4">{{ $records->links() }}</div>
        </x-ui.card>
    @endif

</x-layout>

