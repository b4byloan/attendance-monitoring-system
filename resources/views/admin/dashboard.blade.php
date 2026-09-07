<x-layout
    title="Admin Dashboard"
    :unread-count="$unreadCount ?? 0"
    :recent-notifications="$recentNotifications ?? collect()"
>

    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Students</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalStudents }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Present Today</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ $presentToday }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Absent Today</p>
            <p class="text-3xl font-bold text-red-700 mt-1">{{ $absentToday }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Sections</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalSections }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Parents</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalParents }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Attendance Events Today</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalAttendanceToday }}</p>
        </x-ui.card>
    </div>

    <x-ui.alert type="warning">
        "Absent Today" is Total Active Students minus Present Today — it can't yet tell a real absence
        from a non-school day, since there's no school calendar in the system.
    </x-ui.alert>

    <x-ui.card class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-base font-semibold text-gray-900">Latest Announcements</h3>
            <a href="{{ route('announcements.index') }}" class="text-sm text-indigo-600 hover:underline">View all →</a>
        </div>
        @forelse (($latestAnnouncements ?? collect()) as $a)
            <div class="py-2 border-b border-gray-100 text-sm last:border-0">
                @if ($a->is_pinned)<span class="mr-1">📌</span>@endif
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