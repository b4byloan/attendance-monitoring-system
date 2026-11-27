@props(['title' => 'Dashboard', 'unreadCount' => 0, 'recentNotifications' => null])

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - School Attendance System</title>
    @vite('resources/css/app.css')
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full overflow-hidden bg-gray-50 text-gray-900 antialiased">
<x-ui.toast-container />
<div class="flex h-screen flex-col">
    <header class="z-10 flex h-16 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6">
        <a href="{{ route('home') }}" aria-label="Go to homepage" class="school-brand group flex items-center gap-2.5 font-bold text-emerald-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-700 text-white">
                <x-ui.icon name="school" class="school-brand-icon h-5 w-5" />
            </span>
            <span class="inline-grid overflow-hidden">
                <span class="col-start-1 row-start-1 transition-transform duration-300 ease-in-out group-hover:-translate-y-full group-focus-visible:-translate-y-full motion-reduce:transition-none">School System</span>
                <span aria-hidden="true" class="col-start-1 row-start-1 translate-y-full transition-transform duration-300 ease-in-out group-hover:translate-y-0 group-focus-visible:translate-y-0 motion-reduce:transition-none">Go to homepage</span>
            </span>
        </a>
        <div class="flex items-center gap-4">
            <x-ui.account-menu :unread-count="$unreadCount ?? 0" :recent-notifications="$recentNotifications ?? collect()" />
        </div>
    </header>

    <main class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6" style="contain: layout paint">
        <div class="mx-auto max-w-6xl">
            {{ $slot }}
        </div>
    </main>
</div>
@include('partials.copy-to-clipboard')
@include('partials.input-masks')
@stack('scripts')
</body>
</html>
