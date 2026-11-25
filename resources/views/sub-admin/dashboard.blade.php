<x-layout
    title="Teacher Dashboard"
    :unread-count="$unreadCount ?? 0"
    :recent-notifications="$recentNotifications ?? collect()"
>

    @if ($sections->isEmpty())
        <x-ui.notice type="info">
            You haven't been assigned to any section yet — contact your admin to be linked to your advisory section.
        </x-ui.notice>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Students</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalStudents }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Present Today</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ $presentTodayCount }}</p>
        </x-ui.card>
        <a href="{{ route('sub-admin.sections.index') }}" class="block">
            <x-ui.card class="hover:border-emerald-300 transition">
                <p class="text-xs text-gray-500 uppercase tracking-wide">My Sections</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $sections->count() }}</p>
            </x-ui.card>
        </a>
    </div>

    @if ($sections->isNotEmpty())
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-base font-semibold text-gray-900">My Sections</h2>
            <a href="{{ route('sub-admin.roster.index', ['tab' => 'sections']) }}" class="inline-flex items-center gap-1 text-sm text-emerald-700 hover:underline">
                Student List <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 mb-6">
            @foreach ($sections as $section)
                <x-ui.section-card :section="$section" />
            @endforeach
        </div>
    @endif

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

</x-layout>
