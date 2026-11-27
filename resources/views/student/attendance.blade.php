<x-simple-layout title="My Attendance" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">My Attendance</h2>
            <p class="text-sm text-gray-500">Class (subject) attendance and gate check-in history.</p>
        </div>
        <x-ui.back-button :href="route('student.dashboard')" label="Back to dashboard" />
    </div>

    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[160px] flex-1 sm:flex-none">
                <label class="mb-1 block text-xs text-gray-500">Specific date</label>
                <x-ui.form-field icon="calendar">
                    <input type="date" name="date" value="{{ request('date') }}"
                           class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                </x-ui.form-field>
            </div>
            <div class="min-w-[160px] flex-1 sm:flex-none">
                <label class="mb-1 block text-xs text-gray-500">Or month</label>
                <x-ui.form-field icon="calendar">
                    <input type="month" name="month" value="{{ request('month') }}"
                           class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                </x-ui.form-field>
            </div>
            <x-ui.button type="submit" variant="secondary" size="sm">Filter</x-ui.button>
            @if (request('date') || request('month'))
                <a href="{{ route('student.attendance') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card title="Subject Attendance" class="mb-6">
        <p class="-mt-2 mb-4 text-xs text-gray-500">
            Every class session held for your section — <span class="font-medium text-green-700">Present</span> when you were scanned,
            <span class="font-medium text-red-600">Away</span> when you were not.
        </p>

        <x-ui.table :fit="true">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($subjectRows as $row)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $row['date']->format('M j, Y') }}</td>
                        <td class="px-4 py-3">{{ $row['subject']->name ?? 'Subject removed' }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $row['present'] ? 'green' : 'red' }}">{{ $row['present'] ? 'Present' : 'Away' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-500">{{ $row['time']?->format('h:i A') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-ui.empty-state message="No class sessions recorded for this period." /></td></tr>
                @endforelse
            </tbody>
        </x-ui.table>

        <div class="mt-4">{{ $subjectSessions->links() }}</div>
    </x-ui.card>

    <x-ui.card title="Gate Attendance">
        <x-ui.table :fit="true">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($records as $record)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $records->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3">{{ $record->attendance_date->format('M j, Y') }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $record->type === 'check_in' ? 'green' : 'blue' }}">
                                {{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3">{{ $record->scanned_at->format('h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-ui.empty-state message="No records for this period." /></td></tr>
                @endforelse
            </tbody>
        </x-ui.table>

        <div class="mt-4">{{ $records->links() }}</div>
    </x-ui.card>

</x-simple-layout>
