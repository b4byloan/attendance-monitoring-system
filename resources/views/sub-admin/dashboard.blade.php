<x-layout
    title="Teacher Dashboard"
    :unread-count="$unreadCount ?? 0"
    :recent-notifications="$recentNotifications ?? collect()"
>

    @if (! $section)
        <x-ui.card>
            <x-ui.empty-state message="You haven't been assigned as adviser to any section yet — contact your admin." />
        </x-ui.card>
    @else
        <x-ui.card class="mb-6">
            <div class="flex flex-wrap justify-between items-start gap-2">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $section->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $section->grade_level }} · {{ $section->school_year }}</p>
                </div>
                <x-ui.badge color="{{ $section->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($section->status) }}</x-ui.badge>
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <x-ui.card>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Students</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $students->count() }}{{ $section->max_capacity ? ' / ' . $section->max_capacity : '' }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Present Today</p>
                <p class="text-3xl font-bold text-green-700 mt-1">{{ $presentTodayCount }}</p>
            </x-ui.card>
            <a href="{{ route('attendance-corrections.index') }}" class="block">
                <x-ui.card class="hover:border-indigo-300 transition">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">My Pending Correction Requests</p>
                    <p class="text-3xl font-bold {{ $pendingCorrections > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ $pendingCorrections }}</p>
                </x-ui.card>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <x-ui.card title="Latest Announcements">
                @forelse ($latestAnnouncements as $a)
                    <div class="py-2 border-b border-gray-100 text-sm last:border-0">
                        @if ($a->is_pinned)<x-ui.icon name="pin" class="mr-1 inline h-3.5 w-3.5 align-text-bottom text-amber-500" />@endif
                        <strong>{{ $a->title }}</strong>
                        <span class="text-gray-400"> — {{ $a->publish_date->format('M j') }}</span>
                    </div>
                @empty
                    <x-ui.empty-state message="No announcements yet." />
                @endforelse
                <a href="{{ route('announcements.index') }}" class="mt-3 inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">View all <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" /></a>
            </x-ui.card>

            <x-ui.card title="Recent Attendance Activity">
                @forelse ($recentActivity as $record)
                    <div class="flex justify-between py-2 border-b border-gray-100 text-sm last:border-0">
                        <span>{{ $record->student->full_name }} — {{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}</span>
                        <span class="text-gray-400">{{ $record->scanned_at->format('M j, h:i A') }}</span>
                    </div>
                @empty
                    <x-ui.empty-state message="No attendance activity yet." />
                @endforelse
            </x-ui.card>
        </div>

        <x-ui.card title="My Section Roster">
            <x-ui.table>
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Today</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($students as $student)
                        <tr>
                            <td class="px-4 py-3">{{ $student->full_name }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3">
                                @if ($student->today_status === 'checked_out')
                                    <x-ui.badge color="green">Checked Out</x-ui.badge>
                                @elseif ($student->today_status === 'checked_in')
                                    <x-ui.badge color="blue">Checked In</x-ui.badge>
                                @else
                                    <x-ui.badge color="gray">Not Yet</x-ui.badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-ui.empty-state message="No students assigned to this section yet." /></td></tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.card>
    @endif

</x-layout>