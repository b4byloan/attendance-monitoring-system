<x-layout
    title="Admin Dashboard"
    :unread-count="$unreadCount ?? 0"
    :recent-notifications="$recentNotifications ?? collect()"
>

    <div class="mb-6 flex items-center gap-4">
        <x-ui.avatar :name="auth()->user()->name" size="lg" />
        <div>
            <p class="text-sm text-gray-500">Welcome back,</p>
            <h2 class="text-xl font-bold text-gray-900">{{ auth()->user()->name }}</h2>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6 lg:grid-cols-3 xl:grid-cols-6">
        <x-ui.card>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Total Students</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $totalStudents }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $activeStudents }} active</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                    <x-ui.icon name="graduation-cap" class="h-5 w-5" />
                </span>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Present Today</p>
                    <p class="mt-1 text-3xl font-bold text-green-700">{{ $presentToday }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $presentRate }}% of active students</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-50 text-green-600">
                    <x-ui.icon name="circle-check" class="h-5 w-5" />
                </span>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Absent Today</p>
                    <p class="mt-1 text-3xl font-bold text-red-700">{{ $absentToday }}</p>
                    <p class="mt-1 text-xs text-gray-400">No check-in yet today</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <x-ui.icon name="alert-triangle" class="h-5 w-5" />
                </span>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Total Sections</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $totalSections }}</p>
                    <p class="mt-1 text-xs text-gray-400">Across all grade levels</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                    <x-ui.icon name="layers" class="h-5 w-5" />
                </span>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Total Parents</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $totalParents }}</p>
                    <p class="mt-1 text-xs text-gray-400">Guardian records</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-purple-50 text-purple-600">
                    <x-ui.icon name="users" class="h-5 w-5" />
                </span>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Attendance Events Today</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $totalAttendanceToday }}</p>
                    <p class="mt-1 text-xs text-gray-400">Check-ins and check-outs</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                    <x-ui.icon name="clipboard-check" class="h-5 w-5" />
                </span>
            </div>
        </x-ui.card>
    </div>

    <x-ui.notice type="warning">
        "Absent Today" counts active students without a gate check-in today — it can't yet tell a real absence
        from a non-school day, since there's no school calendar in the system.
    </x-ui.notice>

    <x-ui.card class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-base font-semibold text-gray-900">Latest Announcements</h3>
            <a href="{{ route('announcements.index') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">View all <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" /></a>
        </div>
        @forelse (($latestAnnouncements ?? collect()) as $a)
            <div class="py-2 border-b border-gray-100 text-sm last:border-0">
                @if ($a->is_pinned)<x-ui.icon name="pin" class="mr-1 inline h-3.5 w-3.5 align-text-bottom text-amber-500" />@endif
                <strong>{{ $a->title }}</strong>
                <span class="text-gray-400"> — {{ $a->publish_date->format('M j') }}</span>
            </div>
        @empty
            <x-ui.empty-state message="No announcements yet." />
        @endforelse
    </x-ui.card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-ui.card title="Daily Attendance (Last 7 Days)">
            <canvas id="dailyChart"></canvas>
        </x-ui.card>
        <x-ui.card title="Monthly Attendance (Last 6 Months)">
            <canvas id="monthlyChart"></canvas>
        </x-ui.card>
        <x-ui.card title="Today's Attendance by Grade Level">
            <canvas id="gradeLevelChart"></canvas>
        </x-ui.card>
        <x-ui.card title="Today's Attendance by Section">
            <canvas id="sectionChart"></canvas>
        </x-ui.card>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('dailyChart'), {
            type: 'line',
            data: {
                labels: @json($dailyLabels),
                datasets: [{
                    label: 'Check-ins',
                    data: @json($dailyData),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });

        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: @json($monthlyLabels),
                datasets: [{
                    label: 'Check-ins',
                    data: @json($monthlyData),
                    backgroundColor: '#4f46e5',
                }],
            },
            options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });

        new Chart(document.getElementById('gradeLevelChart'), {
            type: 'pie',
            data: {
                labels: @json($byGradeLevel->keys()),
                datasets: [{
                    data: @json($byGradeLevel->values()),
                    backgroundColor: ['#4f46e5', '#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#4338ca', '#3730a3'],
                }],
            },
        });

        new Chart(document.getElementById('sectionChart'), {
            type: 'doughnut',
            data: {
                labels: @json($bySection->keys()),
                datasets: [{
                    data: @json($bySection->values()),
                    backgroundColor: ['#4f46e5', '#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#4338ca', '#3730a3', '#312e81', '#1e1b4b', '#e0e7ff'],
                }],
            },
        });
    </script>

</x-layout>