<x-layout title="Attendance Reports" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Attendance Reports</h2>
        <div class="flex flex-wrap gap-2">
            <x-ui.button href="{{ route('admin.reports.export-pdf', request()->query()) }}" variant="secondary" size="sm">Export PDF</x-ui.button>
            <x-ui.button href="{{ route('admin.reports.export-excel', request()->query()) }}" variant="secondary" size="sm">Export Excel</x-ui.button>
            <x-ui.button href="{{ route('admin.reports.export-csv', request()->query()) }}" variant="secondary" size="sm">Export CSV</x-ui.button>
        </div>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" class="space-y-4">
            <div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Specific Date</label>
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Or Week</label>
                        <input type="week" name="week" value="{{ request('week') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Or Month</label>
                        <input type="month" name="month" value="{{ request('month') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Pick only one — if more than one is filled in, Date takes priority, then Week, then Month.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">School Year</label>
                    <select name="school_year" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All</option>
                        @foreach ($schoolYears as $sy)
                            <option value="{{ $sy }}" @selected(request('school_year') === $sy)>{{ $sy }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Grade Level</label>
                    <select name="grade_level" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All</option>
                        @foreach ($gradeLevels as $gl)
                            <option value="{{ $gl }}" @selected(request('grade_level') === $gl)>{{ $gl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Section</label>
                    <select name="section_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}" @selected((string) request('section_id') === (string) $section->id)>{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Search Name / LRN</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex gap-2">
                <x-ui.button type="submit">Apply Filters</x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('admin.reports.index') }}">Clear</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Students (matching filters)</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalStudents }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Present</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ $present }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Check-Ins</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $checkIns }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Check-Outs</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $checkOuts }}</p>
        </x-ui.card>
    </div>

    @if (! is_null($didNotCheckIn))
        <x-ui.alert type="warning">
            <strong>{{ $didNotCheckIn }}</strong> enrolled student(s) matching these filters have no check-in on this date.
            This is a raw gap, not a verified absence — it can't tell an absence from a non-school day.
        </x-ui.alert>
    @endif

    <x-ui.alert type="warning">
        "Absent" and "Late" columns aren't shown — both need a school calendar/schedule that doesn't exist in the system yet.
    </x-ui.alert>

    <x-ui.card>
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Student</th>
                    <th class="px-4 py-3 text-left">Section</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($records as $record)
                    <tr>
                        <td class="px-4 py-3">{{ $record->attendance_date->format('M j, Y') }}</td>
                        <td class="px-4 py-3">{{ $record->student->full_name }}</td>
                        <td class="px-4 py-3">{{ $record->student->section->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $record->type === 'check_in' ? 'green' : 'blue' }}">
                                {{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3">{{ $record->scanned_at->format('h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-ui.empty-state message="No records match these filters." /></td></tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <div class="mt-4">{{ $records->links() }}</div>

</x-layout>