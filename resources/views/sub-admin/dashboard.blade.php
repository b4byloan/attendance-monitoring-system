<x-layout
    title="Teacher Dashboard"
    :unread-count="$unreadCount ?? 0"
    :recent-notifications="$recentNotifications ?? collect()"
>

    @if ($sections->isEmpty())
        <x-ui.card>
            <x-ui.empty-state message="You haven't been assigned as adviser to any section yet — contact your admin." />
        </x-ui.card>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <x-ui.card>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Students</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalStudents }}</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Present Today</p>
                <p class="text-3xl font-bold text-green-700 mt-1">{{ $presentTodayCount }}</p>
            </x-ui.card>
            <a href="{{ route('attendance-corrections.index') }}" class="block">
                <x-ui.card class="hover:border-emerald-300 transition">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">My Pending Correction Requests</p>
                    <p class="text-3xl font-bold {{ $pendingCorrections > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ $pendingCorrections }}</p>
                </x-ui.card>
            </a>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h2 class="text-base font-semibold text-gray-900">My Sections</h2>
            <a href="{{ route('sub-admin.roster.index') }}" class="inline-flex items-center gap-1 text-sm text-emerald-700 hover:underline">
                My Roster <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
            @foreach ($sections as $section)
                <x-ui.card class="flex flex-col">
                    <div class="flex flex-wrap justify-between items-start gap-2">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $section->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $section->grade_level }} · {{ $section->school_year }}</p>
                        </div>
                        <x-ui.badge color="{{ $section->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($section->status) }}</x-ui.badge>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Students</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $section->students_count }}</p>
                        </div>
                        <div class="rounded-lg bg-green-50 p-3">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Present Today</p>
                            <p class="mt-1 text-2xl font-bold text-green-700">{{ $section->present_today }}</p>
                        </div>
                    </div>

                    <a href="{{ route('sub-admin.sections.students', $section) }}"
                       class="mt-4 inline-flex items-center justify-center gap-1 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                        View <x-ui.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </x-ui.card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                <a href="{{ route('announcements.index') }}" class="mt-3 inline-flex items-center gap-1 text-sm text-emerald-700 hover:underline">View all <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" /></a>
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
    @endif

</x-layout>
