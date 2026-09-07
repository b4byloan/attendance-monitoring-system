@props(['title' => 'Guard', 'unreadCount' => 0, 'recentNotifications' => null])

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
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6">
        <span class="font-bold text-indigo-700">🏫 School System</span>
        <div class="flex items-center gap-4">
            @include('partials.notification-bell', ['unreadCount' => $unreadCount, 'recentNotifications' => $recentNotifications ?? collect()])
            <span class="text-sm text-gray-600 hidden sm:inline">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm text-gray-500 hover:text-gray-800">Log out</button>
            </form>
        </div>
    </header>

    <main class="max-w-3xl mx-auto p-4 sm:p-6">
        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if ($errors->any())
            <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        {{ $slot }}
    </main>
</body>
</html>