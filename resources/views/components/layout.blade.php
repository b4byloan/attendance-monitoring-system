@props(['title' => 'Dashboard', 'unreadCount' => 0, 'recentNotifications' => null])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - School Attendance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
<div class="min-h-screen flex">

    <input type="checkbox" id="sidebar-toggle" class="peer hidden">
    <div class="fixed inset-0 bg-black/40 z-20 hidden peer-checked:block lg:hidden"
         onclick="document.getElementById('sidebar-toggle').checked = false"></div>

    <aside class="fixed lg:static inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-200 -translate-x-full peer-checked:translate-x-0 lg:translate-x-0 transition-transform duration-200">
        <div class="h-16 flex items-center px-6 border-b border-gray-200">
            <span class="font-bold text-indigo-700 text-lg">🏫 School System</span>
        </div>
        <nav class="p-4 space-y-1">
            @php
                $user = auth()->user();
                $navItems = match(true) {
                    $user->isAdmin() => [
                        ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
                        ['label' => 'Students', 'route' => 'admin.students.index'],
                        ['label' => 'Guardians', 'route' => 'admin.guardians.index'],
                       
                        ['label' => 'Users', 'route' => 'admin.users.index'],
                        ['label' => 'Announcements', 'route' => 'announcements.index'],
                        ['label' => 'Reports', 'route' => 'admin.reports.index'],
                        ['label' => 'Corrections', 'route' => 'attendance-corrections.index'],
                        ['label' => 'Audit Logs', 'route' => 'admin.audit-logs.index'],
                        ['label' => 'Backups', 'route' => 'admin.backups.index'],
                        ['label' => 'Settings', 'route' => 'admin.settings.edit'],
                    ],
                    $user->isSubAdmin() => [
                        ['label' => 'Dashboard', 'route' => 'sub-admin.dashboard'],
                        ['label' => 'Scanner', 'route' => 'scanner.index'],
                        ['label' => 'Announcements', 'route' => 'announcements.index'],
                        ['label' => 'Corrections', 'route' => 'attendance-corrections.index'],
                    ],
                    $user->isGuard() => [
                        ['label' => 'Dashboard', 'route' => 'guard.dashboard'],
                        ['label' => 'Scanner', 'route' => 'scanner.index'],
                        ['label' => 'Corrections', 'route' => 'attendance-corrections.index'],
                    ],
                    default => [
                        ['label' => 'Dashboard', 'route' => 'parent.dashboard'],
                        ['label' => 'Announcements', 'route' => 'parent.announcements'],
                    ],
                };
            @endphp
            @foreach ($navItems as $item)
                @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*'); @endphp
                <a href="{{ route($item['route']) }}"
                   class="block px-3 py-2 rounded-lg text-sm font-medium {{ $active ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 -ml-1 pl-4' : 'text-gray-600 hover:bg-gray-50' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-8">
            <div class="flex items-center gap-3">
                <label for="sidebar-toggle" class="lg:hidden cursor-pointer text-xl">☰</label>
                <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            </div>
            <div class="flex items-center gap-4">
                @include('partials.notification-bell', ['unreadCount' => $unreadCount, 'recentNotifications' => $recentNotifications ?? collect()])
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $user->role) }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-gray-500 hover:text-gray-800">Log out</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-8">
            @if (session('success'))
                <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
            @endif
            @if ($errors->any())
                <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

<script>
document.addEventListener('submit', function (e) {
    const form = e.target;
    const button = form.querySelector('button[type="submit"], button:not([type])');
    if (button && !button.disabled) {
        button.dataset.originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = 'Please wait…';
    }
});
</script>
</body>
</html>