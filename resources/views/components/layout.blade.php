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
<div class="flex h-screen overflow-hidden">

    <input type="checkbox" id="sidebar-toggle" class="peer hidden">
    <div class="fixed inset-0 z-20 hidden bg-black/40 peer-checked:block lg:hidden"
         onclick="document.getElementById('sidebar-toggle').checked = false"></div>

    <aside class="fixed inset-y-0 left-0 z-30 flex w-64 shrink-0 -translate-x-full flex-col overflow-y-auto border-r border-emerald-950/40 bg-emerald-900 text-emerald-50 transition-transform duration-200 peer-checked:translate-x-0 lg:static lg:translate-x-0">
        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white ring-1 ring-white/20">
                <x-ui.icon name="school" class="h-5 w-5" />
            </span>
            <span class="text-base font-bold text-white">School System</span>
        </div>

        <nav class="flex-1 space-y-1.5 p-3">
            @php
                $user = auth()->user();
                $navGroups = match(true) {
                    $user->isAdmin() => [
                        'Overview' => [
                            'icon' => 'dashboard',
                            'items' => [
                                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
                            ],
                        ],
                        'Users' => [
                            'icon' => 'users',
                            'items' => [
                                ['label' => 'User Accounts', 'route' => 'admin.users.index', 'icon' => 'user'],
                                ['label' => 'Students', 'route' => 'admin.students.index', 'icon' => 'graduation-cap'],
                                ['label' => 'Guardians', 'route' => 'admin.guardians.index', 'icon' => 'users'],
                            ],
                        ],
                        'School Records' => [
                            'icon' => 'layers',
                            'items' => [
                                ['label' => 'Sections', 'route' => 'admin.sections.index', 'icon' => 'layers'],
                            ],
                        ],
                        'Attendance' => [
                            'icon' => 'scan-line',
                            'items' => [
                                ['label' => 'QR Scanner', 'route' => 'scanner.index', 'icon' => 'qr-code'],
                                ['label' => 'Corrections', 'route' => 'attendance-corrections.index', 'icon' => 'clipboard-check'],
                                ['label' => 'Reports', 'route' => 'admin.reports.index', 'icon' => 'bar-chart'],
                            ],
                        ],
                        'Communication' => [
                            'icon' => 'megaphone',
                            'items' => [
                                ['label' => 'Announcements', 'route' => 'announcements.index', 'icon' => 'megaphone'],
                            ],
                        ],
                        'System' => [
                            'icon' => 'settings',
                            'items' => [
                                ['label' => 'Settings', 'route' => 'admin.settings.edit', 'icon' => 'settings'],
                                ['label' => 'Audit Logs', 'route' => 'admin.audit-logs.index', 'icon' => 'scroll-text'],
                                ['label' => 'Backups', 'route' => 'admin.backups.index', 'icon' => 'archive'],
                            ],
                        ],
                    ],
                    $user->isSubAdmin() => [
                        'Overview' => [
                            'icon' => 'dashboard',
                            'items' => [
                                ['label' => 'Dashboard', 'route' => 'sub-admin.dashboard', 'icon' => 'dashboard'],
                            ],
                        ],
                        'Students' => [
                            'icon' => 'graduation-cap',
                            'items' => [
                                ['label' => 'Student Information', 'route' => 'sub-admin.students.index', 'icon' => 'graduation-cap'],
                                ['label' => 'My Roster', 'route' => 'sub-admin.roster.index', 'icon' => 'users'],
                            ],
                        ],
                        'Attendance' => [
                            'icon' => 'scan-line',
                            'items' => [
                                ['label' => 'QR Scanner', 'route' => 'scanner.index', 'icon' => 'qr-code'],
                                ['label' => 'Corrections', 'route' => 'attendance-corrections.index', 'icon' => 'clipboard-check'],
                            ],
                        ],
                        'Communication' => [
                            'icon' => 'megaphone',
                            'items' => [
                                ['label' => 'Announcements', 'route' => 'announcements.index', 'icon' => 'megaphone'],
                            ],
                        ],
                    ],
                    $user->isGuard() => [
                        'Overview' => [
                            'icon' => 'dashboard',
                            'items' => [
                                ['label' => 'Dashboard', 'route' => 'guard.dashboard', 'icon' => 'dashboard'],
                            ],
                        ],
                        'Attendance' => [
                            'icon' => 'scan-line',
                            'items' => [
                                ['label' => 'QR Scanner', 'route' => 'scanner.index', 'icon' => 'qr-code'],
                                ['label' => 'Corrections', 'route' => 'attendance-corrections.index', 'icon' => 'clipboard-check'],
                            ],
                        ],
                    ],
                    default => [
                        'Overview' => [
                            'icon' => 'dashboard',
                            'items' => [
                                ['label' => 'Dashboard', 'route' => 'parent.dashboard', 'icon' => 'dashboard'],
                            ],
                        ],
                        'Communication' => [
                            'icon' => 'megaphone',
                            'items' => [
                                ['label' => 'Announcements', 'route' => 'parent.announcements', 'icon' => 'megaphone'],
                            ],
                        ],
                    ],
                };

                $currentRoute = request()->route()?->getName() ?? '';
                $breadcrumbs = [];

                if ($currentRoute !== '' && ! str_ends_with($currentRoute, '.dashboard')) {
                    foreach ($navGroups as $groupName => $group) {
                        foreach ($group['items'] as $item) {
                            $resourcePrefix = str_ends_with($item['route'], '.index')
                                ? substr($item['route'], 0, -strlen('index'))
                                : null;

                            if ($currentRoute === $item['route'] || ($resourcePrefix !== null && str_starts_with($currentRoute, $resourcePrefix))) {
                                $breadcrumbs = [
                                    ['label' => $groupName, 'url' => null],
                                    ['label' => $item['label'], 'url' => $currentRoute === $item['route'] ? null : route($item['route'])],
                                ];
                                break 2;
                            }
                        }
                    }
                }
            @endphp

            @foreach ($navGroups as $groupName => $group)
                @php
                    $groupActive = collect($group['items'])->contains(function (array $item): bool {
                        return request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*');
                    });
                @endphp
                <details class="group" @if ($groupActive) open @endif>
                    <summary class="{{ $groupActive ? 'bg-white/15 text-white' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }} flex cursor-pointer list-none items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-white/60">
                        <x-ui.icon :name="$group['icon']" class="h-4 w-4 shrink-0" />
                        <span class="flex-1">{{ $groupName }}</span>
                        <x-ui.icon name="chevron-down" class="h-4 w-4 shrink-0 opacity-70 transition-transform group-open:rotate-180" />
                    </summary>
                    <div class="mt-1 ml-4 space-y-0.5 border-l border-white/15 py-1 pl-3">
                        @foreach ($group['items'] as $item)
                            @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*'); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="{{ $active ? 'bg-white text-emerald-900 shadow-sm' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }} flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition">
                                <x-ui.icon :name="$item['icon']" class="h-4 w-4 shrink-0" />
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </nav>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="z-10 flex h-16 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-4 lg:px-8">
            <div class="flex items-center gap-3">
                <label for="sidebar-toggle" class="-ml-1 cursor-pointer rounded-md p-1.5 text-gray-700 transition hover:bg-gray-100 focus-within:ring-2 focus-within:ring-emerald-500 lg:hidden">
                    <span class="sr-only">Open navigation menu</span>
                    <x-ui.icon name="menu" class="h-6 w-6" />
                </label>
                <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            </div>
            <div class="flex items-center gap-4">
                <x-ui.account-menu :unread-count="$unreadCount ?? 0" :recent-notifications="$recentNotifications ?? collect()" />
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <x-ui.breadcrumb :items="$breadcrumbs" />

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
@include('partials.copy-to-clipboard')
@stack('scripts')
</body>
</html>
