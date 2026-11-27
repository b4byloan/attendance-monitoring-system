<x-simple-layout title="Attendance History" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Attendance History</h2>
            <p class="text-sm text-gray-500">{{ $student->full_name }} · {{ $student->section->name ?? 'No section' }}</p>
        </div>
        <x-ui.back-button :href="route('parent.students.dashboard', $student)" label="Back to dashboard" />
    </div>

    @if ($students->count() > 1)
        <div class="mb-6 flex gap-2 overflow-x-auto pb-1">
            @foreach ($students as $s)
                <a href="{{ route('parent.students.attendance', $s) }}"
                   class="flex shrink-0 items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium {{ $s->id === $student->id ? 'bg-indigo-600 text-white' : 'border border-gray-300 bg-white text-gray-700' }}">
                    <x-ui.avatar :name="$s->full_name" :photo="$s->photo_path" size="xs" />
                    {{ $s->full_name }}
                </a>
            @endforeach
        </div>
    @endif

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
                <a href="{{ route('parent.students.attendance', $student) }}" class="text-sm text-gray-500 hover:underline">Clear</a>
            @endif
        </form>
    </x-ui.card>

    <x-ui.card title="Subject Attendance" class="mb-6">
        <p class="-mt-2 mb-4 text-xs text-gray-500">
            Every class session held for this section — <span class="font-medium text-green-700">Present</span> when the student was scanned,
            <span class="font-medium text-red-600">Away</span> when they were not.
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
                            <x-ui.badge color="{{ $record->type === 'check_in' ? 'green' : 'blue' }}">{{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}</x-ui.badge>
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
