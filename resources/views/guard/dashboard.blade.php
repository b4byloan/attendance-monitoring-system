<x-simple-layout
    title="Guard Dashboard"
    :unread-count="$unreadCount ?? 0"
    :recent-notifications="$recentNotifications ?? collect()"
>
    <div class="text-center mb-6">
        <p class="text-sm text-gray-500">Logged in as</p>
        <p class="text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</p>
    </div>

    <a href="{{ route('scanner.index') }}"
       class="block w-full text-center bg-indigo-600 text-white text-2xl font-bold py-8 rounded-xl shadow-sm hover:bg-indigo-700 transition mb-6">
        📷 Open Scanner
    </a>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <x-ui.card class="text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Check-Ins Today</p>
            <p class="text-4xl font-bold text-green-700 mt-1">{{ $checkInsToday }}</p>
        </x-ui.card>
        <x-ui.card class="text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Check-Outs Today</p>
            <p class="text-4xl font-bold text-blue-700 mt-1">{{ $checkOutsToday }}</p>
        </x-ui.card>
    </div>

    <x-ui.card class="mb-6 text-center">
        <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Connection</p>
        <span id="connection-badge" class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700">Checking…</span>
        <p class="text-xs text-gray-400 mt-2">Full offline/sync detail (pending count, last sync) is on the Scanner page.</p>
    </x-ui.card>

    <x-ui.card title="Today's Scan History">
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Student</th>
                    <th class="px-4 py-3 text-left">Section</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($todaysScans as $scan)
                    <tr>
                        <td class="px-4 py-3">{{ $scan->student->full_name }}</td>
                        <td class="px-4 py-3">{{ $scan->student->section->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge color="{{ $scan->type === 'check_in' ? 'green' : 'blue' }}">
                                {{ $scan->type === 'check_in' ? 'Check-In' : 'Check-Out' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3">{{ $scan->scanned_at->format('h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-ui.empty-state message="No scans recorded yet today." /></td></tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <script>
        function updateConnectionBadge() {
            const badge = document.getElementById('connection-badge');
            if (navigator.onLine) {
                badge.textContent = 'Online';
                badge.className = 'inline-block px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
            } else {
                badge.textContent = 'Offline';
                badge.className = 'inline-block px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
            }
        }
        window.addEventListener('online', updateConnectionBadge);
        window.addEventListener('offline', updateConnectionBadge);
        updateConnectionBadge();
    </script>

</x-simple-layout>