@props(['unreadCount' => 0, 'recentNotifications' => null])

@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
        ->implode('');

    $recentNotifications = $recentNotifications ?? collect();
@endphp

<details class="relative" data-account-menu>
    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-lg p-1.5 text-gray-700 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-700 text-sm font-semibold text-white">
            {{ $initials }}
            @if ($unreadCount > 0)
                <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full bg-red-600 ring-2 ring-white"></span>
            @endif
        </span>
        <span class="hidden text-left sm:block">
            <span class="block text-sm font-medium text-gray-900">{{ $user->name }}</span>
            <span class="block text-xs text-gray-500">{{ $user->roleLabel() }}</span>
        </span>
        <x-ui.icon name="chevron-down" class="hidden h-4 w-4 shrink-0 text-gray-400 sm:block" />
        <span class="sr-only">Open account menu</span>
    </summary>

    <div class="absolute right-0 z-20 mt-2 w-56 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
        <div class="border-b border-gray-100 px-4 py-3 sm:hidden">
            <p class="truncate text-sm font-medium text-gray-900">{{ $user->name }}</p>
            <p class="text-xs text-gray-500">{{ $user->roleLabel() }}</p>
        </div>

        <details class="group/notification">
            <summary class="flex cursor-pointer list-none items-center gap-2 px-4 py-2.5 text-sm text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500">
                <x-ui.icon name="bell" class="h-4 w-4" />
                <span class="flex-1">Notifications</span>
                @if ($unreadCount > 0)
                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @endif
                <x-ui.icon name="chevron-down" class="h-3.5 w-3.5 shrink-0 text-gray-400 transition-transform group-open/notification:rotate-180" />
            </summary>

            <div class="border-t border-gray-100">
                <div class="max-h-72 overflow-y-auto">
                    @include('partials.notification-items', ['notifications' => $recentNotifications])
                </div>
                <a href="{{ route('notifications.index') }}" class="block border-t border-gray-100 px-4 py-2.5 text-center text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                    View all notifications
                </a>
            </div>
        </details>

        <a href="{{ route('account-settings.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 transition hover:bg-gray-50">
            <x-ui.icon name="user" class="h-4 w-4" />
            Account Settings
        </a>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100">
            @csrf
            <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 transition hover:bg-red-50">
                <x-ui.icon name="log-out" class="h-4 w-4" />
                Logout
            </button>
        </form>
    </div>
</details>

<script>
    document.addEventListener('click', function (event) {
        document.querySelectorAll('details[data-account-menu][open]').forEach(function (menu) {
            if (! menu.contains(event.target)) {
                menu.removeAttribute('open');
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('details[data-account-menu][open]').forEach(function (menu) {
                menu.removeAttribute('open');
            });
        }
    });
</script>
