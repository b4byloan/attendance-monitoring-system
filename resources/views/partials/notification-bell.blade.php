<details class="relative inline-block">
    <summary class="relative inline-block cursor-pointer list-none align-middle rounded-md p-1.5 text-gray-600 transition hover:bg-gray-100 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <span class="sr-only">Notifications</span>
        <x-ui.icon name="bell" class="h-6 w-6" />
        @if ($unreadCount > 0)
            <span class="absolute -right-1 -top-1 rounded-full bg-red-600 px-1.5 py-0.5 text-xs font-semibold text-white">{{ $unreadCount }}</span>
        @endif
    </summary>
    <div class="absolute right-0 z-20 mt-2 w-80 rounded-lg border bg-white shadow-lg">
        <div class="flex items-center justify-between border-b p-3">
            <span class="text-sm font-semibold">Notifications</span>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button class="text-xs text-emerald-700 hover:underline">Mark all read</button>
                </form>
            @endif
        </div>
        <div class="max-h-80 overflow-y-auto">
            @forelse ($recentNotifications as $n)
                <a href="{{ route('notifications.read', $n->id) }}"
                   class="block border-b p-3 text-sm hover:bg-gray-50 {{ $n->read_at ? 'text-gray-500' : 'bg-emerald-50 font-medium' }}">
                    <p>{{ $n->data['title'] }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $n->data['message'] }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $n->created_at->diffForHumans() }}</p>
                </a>
            @empty
                <p class="p-4 text-center text-sm text-gray-400">No notifications yet.</p>
            @endforelse
        </div>
        <a href="{{ route('notifications.index') }}" class="block border-t p-2 text-center text-sm text-emerald-700 hover:underline">View all</a>
    </div>
</details>
