<details class="relative inline-block">
    <summary class="list-none cursor-pointer relative inline-block align-middle">
        <span class="text-2xl">🔔</span>
        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-2 bg-red-600 text-white text-xs rounded-full px-1.5 py-0.5">{{ $unreadCount }}</span>
        @endif
    </summary>
    <div class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-10">
        <div class="p-3 border-b flex justify-between items-center">
            <span class="font-semibold text-sm">Notifications</span>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button class="text-xs text-blue-600 hover:underline">Mark all read</button>
                </form>
            @endif
        </div>
        <div class="max-h-80 overflow-y-auto">
            @forelse ($recentNotifications as $n)
                <a href="{{ route('notifications.read', $n->id) }}"
                   class="block p-3 border-b text-sm hover:bg-gray-50 {{ $n->read_at ? 'text-gray-500' : 'font-medium bg-blue-50' }}">
                    <p>{{ $n->data['title'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $n->data['message'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                </a>
            @empty
                <p class="p-4 text-sm text-gray-400 text-center">No notifications yet.</p>
            @endforelse
        </div>
        <a href="{{ route('notifications.index') }}" class="block p-2 text-center text-sm text-blue-600 hover:underline border-t">View all</a>
    </div>
</details>