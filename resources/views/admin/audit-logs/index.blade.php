<x-layout title="Audit Logs" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <h2 class="text-lg font-semibold text-gray-900 mb-6">Audit Logs</h2>

    <x-ui.card class="mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">User</label>
                <select name="user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Action</label>
                <select name="action" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All</option>
                    @foreach ($actions as $a)
                        <option value="{{ $a }}" @selected(request('action') === $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Date</label>
                <input type="date" name="date" value="{{ request('date') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="sm:col-span-4 flex gap-2">
                <x-ui.button type="submit">Apply Filters</x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('admin.audit-logs.index') }}">Clear</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card>
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Date/Time</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Action</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-left">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($logs as $log)
                    @php [$color, $icon] = $log->actionStyle(); $url = $log->subjectUrl(); @endphp
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $log->created_at->format('M j, Y h:i A') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $log->user->name ?? 'System' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.audit-logs.show', $log) }}">
                                <x-ui.badge :color="$color">{{ $icon }} {{ $log->action }}</x-ui.badge>
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm max-w-xs truncate">{{ $log->description }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if ($log->subject_type)
                                @if ($url)
                                    <a href="{{ $url }}" class="text-indigo-600 hover:underline">{{ $log->subject_type }} #{{ $log->subject_id }}</a>
                                @else
                                    {{ $log->subject_type }} #{{ $log->subject_id }}
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-400">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state message="No logs match these filters." /></td></tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <div class="mt-4">{{ $logs->links() }}</div>

</x-layout>