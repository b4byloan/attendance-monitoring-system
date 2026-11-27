<x-layout title="Audit Logs" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <x-ui.card class="mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <x-ui.form-field icon="search">
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                </x-ui.form-field>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">User</label>
                <x-ui.form-field icon="user">
                    <input type="text" name="user" value="{{ request('user') }}" placeholder="Search by name"
                           class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                </x-ui.form-field>
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
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Date/Time</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Action</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-left">IP</th>
                    <th class="px-4 py-3 text-left">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($logs as $log)
                    @php [$color, $icon] = $log->actionStyle(); $url = $log->subjectUrl(); @endphp
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $logs->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $log->created_at->format('M j, Y h:i A') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $log->user->name ?? 'System' }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge :color="$color" class="gap-1"><x-ui.icon :name="$icon" class="h-3 w-3" /> {{ $log->action }}</x-ui.badge>
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
                        <td class="px-4 py-3 text-sm text-gray-400"><x-ui.optional-value :value="$log->ip_address" /></td>
                        <td class="px-4 py-3 text-sm">
                            <button type="button" onclick="document.getElementById('audit-log-modal-{{ $log->id }}').showModal()"
                                    class="text-indigo-600 hover:underline">View</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-ui.empty-state message="No logs match these filters." /></td></tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <div class="mt-4">{{ $logs->links() }}</div>

    @foreach ($logs as $log)
        @php [$color, $icon] = $log->actionStyle(); $url = $log->subjectUrl(); @endphp

        <dialog id="audit-log-modal-{{ $log->id }}" class="w-full max-w-lg rounded-lg p-0 shadow-xl backdrop:bg-black/40">
            <div class="relative p-6">
                <button type="button" onclick="document.getElementById('audit-log-modal-{{ $log->id }}').close()" aria-label="Close dialog"
                        class="absolute left-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <x-ui.icon name="x" class="h-4 w-4" />
                </button>

                <h3 class="mb-4 pl-8 text-lg font-semibold text-gray-900">Log details</h3>

                <div class="mb-4 pl-8">
                    <x-ui.badge :color="$color" class="gap-1"><x-ui.icon :name="$icon" class="h-3 w-3" /> {{ $log->action }}</x-ui.badge>
                </div>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Date/Time</dt>
                        <dd class="mt-0.5 font-medium">{{ $log->created_at->format('F j, Y h:i:s A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">User</dt>
                        <dd class="mt-0.5 flex flex-wrap items-center gap-2 font-medium">
                            <span>{{ $log->user->name ?? 'System (no user)' }}</span>
                            @if ($log->user)
                                <span class="font-normal text-gray-400">{{ $log->user->email }}</span>
                                <x-ui.copy-button :value="$log->user->email" what="email" :icon-only="true" />
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Description</dt>
                        <dd class="mt-0.5 font-medium">{{ $log->description }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Subject</dt>
                        <dd class="mt-0.5 font-medium">
                            @if ($log->subject_type)
                                @if ($url)
                                    <a href="{{ $url }}" class="text-indigo-600 hover:underline">{{ $log->subject_type }} #{{ $log->subject_id }}</a>
                                @else
                                    {{ $log->subject_type }} #{{ $log->subject_id }}
                                @endif
                            @else
                                Not tied to a specific record
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">IP Address</dt>
                        <dd class="mt-0.5 font-medium">{{ $log->ip_address ?? 'Not recorded (e.g. run from console)' }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex justify-end">
                    <x-ui.button type="button" variant="secondary" onclick="document.getElementById('audit-log-modal-{{ $log->id }}').close()">Close</x-ui.button>
                </div>
            </div>
        </dialog>
    @endforeach

</x-layout>
