<x-layout title="Log Details" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    @php [$color, $icon] = $log->actionStyle(); $url = $log->subjectUrl(); @endphp

    <div class="max-w-xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Log Details</h2>
            <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-gray-500 hover:underline">← Back to logs</a>
        </div>

        <x-ui.card>
            <div class="mb-4">
                <x-ui.badge :color="$color">{{ $icon }} {{ $log->action }}</x-ui.badge>
            </div>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500 text-xs uppercase">Date/Time</dt>
                    <dd class="font-medium mt-0.5">{{ $log->created_at->format('F j, Y h:i:s A') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs uppercase">User</dt>
                    <dd class="font-medium mt-0.5">
                        {{ $log->user->name ?? 'System (no user)' }}
                        @if ($log->user)<span class="text-gray-400 font-normal"> ({{ $log->user->email }})</span>@endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs uppercase">Description</dt>
                    <dd class="font-medium mt-0.5">{{ $log->description }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs uppercase">Subject</dt>
                    <dd class="font-medium mt-0.5">
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
                    <dt class="text-gray-500 text-xs uppercase">IP Address</dt>
                    <dd class="font-medium mt-0.5">{{ $log->ip_address ?? 'Not recorded (e.g. run from console)' }}</dd>
                </div>
            </dl>
        </x-ui.card>
    </div>

</x-layout>