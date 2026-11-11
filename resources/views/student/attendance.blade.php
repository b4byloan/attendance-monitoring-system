<x-simple-layout title="My Attendance" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-semibold text-gray-900">My Attendance</h2>
        <x-ui.back-button :href="route('student.dashboard')" label="Back to dashboard" />
    </div>

    <x-ui.card class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Specific date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Or month</label>
                <input type="month" name="month" value="{{ request('month') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <x-ui.button type="submit" variant="secondary" size="sm">Filter</x-ui.button>
            @if (request('date') || request('month'))
                <a href="{{ route('student.attendance') }}" class="text-sm text-gray-500">Clear</a>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card>
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($records as $record)
                    <tr>
                        <td class="px-4 py-3">{{ $record->attendance_date->format('M j, Y') }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $record->type === 'check_in' ? 'green' : 'blue' }}">
                                {{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3">{{ $record->scanned_at->format('h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-ui.empty-state message="No records for this period." /></td></tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>
    <div class="mt-4">{{ $records->links() }}</div>

</x-simple-layout>