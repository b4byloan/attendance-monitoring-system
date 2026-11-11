<x-simple-layout title="Student Dashboard" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex items-center gap-4 mb-6">
        @if ($student->photo_path)
            <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-16 h-16 rounded-full object-cover">
        @else
            <div class="w-16 h-16 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold">
                {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
            </div>
        @endif
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $student->full_name }}</h2>
            <div class="mt-1 flex flex-wrap items-center gap-2">
                <p class="text-sm text-gray-500">LRN: <span class="font-mono text-gray-700">{{ $student->lrn }}</span></p>
                <x-ui.copy-button :value="$student->lrn" />
            </div>
        </div>
    </div>

    <x-ui.card class="mb-6">
        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div><dt class="text-gray-500 text-xs uppercase">Grade Level</dt><dd class="font-medium mt-0.5">{{ $student->section->grade_level ?? '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs uppercase">Section</dt><dd class="font-medium mt-0.5">{{ $student->section->name ?? '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs uppercase">Adviser</dt><dd class="font-medium mt-0.5">{{ $student->section->adviser->name ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>

    <x-ui.card class="mb-6">
        <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Today</p>
        @if ($todayCheckIn && $todayCheckOut)
            <x-ui.badge color="green">Checked Out</x-ui.badge>
        @elseif ($todayCheckIn)
            <x-ui.badge color="blue">Checked In</x-ui.badge>
        @else
            <x-ui.badge color="gray">Not Checked In Yet</x-ui.badge>
        @endif
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">Check-In</p>
                <p class="text-lg font-semibold mt-1">{{ $todayCheckIn ? $todayCheckIn->scanned_at->format('h:i A') : '—' }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">Check-Out</p>
                <p class="text-lg font-semibold mt-1">{{ $todayCheckOut ? $todayCheckOut->scanned_at->format('h:i A') : '—' }}</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-base font-semibold text-gray-900">Recent Attendance</h3>
            <a href="{{ route('student.attendance') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">Full history <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" /></a>
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

    <div class="flex gap-4 text-sm">
        <a href="{{ route('student.profile') }}" class="text-indigo-600 hover:underline">My Profile</a>
        <a href="{{ route('account-settings.edit') }}" class="text-indigo-600 hover:underline">Account Settings</a>
    </div>

</x-simple-layout>