<x-layout title="New Correction Request" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-2xl mx-auto">
        <x-ui.back-button :href="route('attendance-corrections.index')" label="Back to corrections" class="mb-4" />

        <h2 class="text-lg font-semibold text-gray-900 mb-6">New Correction Request</h2>

        <x-ui.card class="mb-6">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search student by name or LRN"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
            </form>
        </x-ui.card>

        @if (request('search'))
            @forelse ($students as $student)
                <x-ui.card class="mb-4">
                    <p class="font-semibold text-gray-900 mb-3">{{ $student->full_name }} <span class="text-xs text-gray-400 font-normal">LRN: {{ $student->lrn }}</span></p>
                    <div class="space-y-2">
                        @forelse ($student->attendanceRecords()->orderByDesc('attendance_date')->limit(10)->get() as $record)
                            <div class="flex justify-between items-center text-sm bg-gray-50 rounded-lg px-3 py-2.5">
                                <span>{{ $record->attendance_date->format('M j, Y') }} — {{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }} at {{ $record->scanned_at->format('h:i A') }}</span>
                                <a href="{{ route('attendance-corrections.create-for-record', $record) }}" class="text-indigo-600 hover:underline whitespace-nowrap ml-2">Request Correction</a>
                            </div>
                        @empty
                            <x-ui.empty-state message="No attendance records for this student yet." />
                        @endforelse
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card><x-ui.empty-state message="No students match that search." /></x-ui.card>
            @endforelse
        @endif
    </div>

</x-layout>