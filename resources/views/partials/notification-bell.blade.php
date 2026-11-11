@php
    $recentNotifications = $recentNotifications ?? collect();
    $unreadCount = $unreadCount ?? 0;
@endphp

<details class="relative" data-notification-dropdown>
    <summary class="relative cursor-pointer list-none rounded-md p-2 text-gray-600 transition hover:bg-gray-100 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <span class="sr-only">Open notifications</span>
        <x-ui.icon name="bell" class="h-6 w-6" />
        @if ($unreadCount > 0)
            <span class="absolute right-0 top-0 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold text-white">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </summary>

    <div class="absolute right-0 z-30 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <span class="text-sm font-semibold text-gray-900">Notifications</span>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-emerald-700 hover:underline">Mark all read</button>
                </form>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @include('partials.notification-items', ['notifications' => $recentNotifications])
        </div>

        <a href="{{ route('notifications.index') }}" class="block border-t border-gray-100 px-4 py-2.5 text-center text-sm font-medium text-emerald-700 transition hover:bg-emerald-50">
            View all notifications
        </a>
    </div>
</details>

<script>
    document.addEventListener('click', function (event) {
        document.querySelectorAll('details[data-notification-dropdown][open]').forEach(function (dropdown) {
            if (! dropdown.contains(event.target)) {
                dropdown.removeAttribute('open');
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('details[data-notification-dropdown][open]').forEach(function (dropdown) {
                dropdown.removeAttribute('open');
            });
        }
    });
</script>
