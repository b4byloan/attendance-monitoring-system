<x-simple-layout title="Parent Dashboard" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="mb-6">
        <p class="text-sm text-gray-500">Hello,</p>
        <h2 class="text-xl font-bold text-gray-900">{{ auth()->user()->name }}</h2>
    </div>

    @if ($students->count() > 1)
        <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
            @foreach ($students as $s)
                <a href="{{ route('parent.students.dashboard', $s) }}"
                   class="shrink-0 px-4 py-2.5 rounded-lg text-sm font-medium whitespace-nowrap {{ $s->id === $student->id ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700' }}">
                    {{ $s->full_name }}
                </a>
            @endforeach
        </div>
    @endif

    <x-ui.card class="mb-6">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">{{ $student->full_name }}</h3>
                <p class="text-sm text-gray-500">{{ $student->section->grade_level ?? '—' }} · {{ $student->section->name ?? 'No section' }}</p>
            </div>
            @if ($todayCheckIn && $todayCheckOut)
                <x-ui.badge color="green">Checked Out</x-ui.badge>
            @elseif ($todayCheckIn)
                <x-ui.badge color="blue">Checked In</x-ui.badge>
            @else
                <x-ui.badge color="gray">Not Checked In Yet</x-ui.badge>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Check-In Time</p>
                <p class="text-lg font-semibold text-gray-900 mt-1">
                    {{ $todayCheckIn ? $todayCheckIn->scanned_at->format('h:i A') : '—' }}
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Check-Out Time</p>
                <p class="text-lg font-semibold text-gray-900 mt-1">
                    {{ $todayCheckOut ? $todayCheckOut->scanned_at->format('h:i A') : '—' }}
                </p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-base font-semibold text-gray-900">Recent Attendance</h3>
            <a href="{{ route('parent.students.attendance', $student) }}" class="text-sm text-indigo-600 hover:underline">Full history →</a>
        </div>
        @forelse ($recent as $record)
            <div class="flex justify-between py-2.5 border-b border-gray-100 text-sm last:border-0">
                <span>{{ $record->attendance_date->format('D, M j') }} — {{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}</span>
                <span class="text-gray-500">{{ $record->scanned_at->format('h:i A') }}</span>
            </div>
        @empty
            <x-ui.empty-state message="No attendance recorded yet." />
        @endforelse
    </x-ui.card>

    <x-ui.card title="Announcements">
        @forelse ($announcements as $a)
            <div class="py-2.5 border-b border-gray-100 text-sm last:border-0">
                @if ($a->is_pinned)<span class="mr-1">📌</span>@endif
                <strong>{{ $a->title }}</strong>
                <p class="text-gray-500 text-xs mt-0.5">{{ $a->publish_date->format('M j, Y') }}</p>
            </div>
        @empty
            <x-ui.empty-state message="No announcements right now." />
        @endforelse
        <a href="{{ route('parent.announcements') }}" class="block mt-3 text-sm text-indigo-600 hover:underline">View all →</a>
    </x-ui.card>

</x-simple-layout>