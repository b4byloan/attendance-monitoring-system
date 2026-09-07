<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto">
        @php
            $backRoute = match(true) {
                auth()->user()->isAdmin() => route('admin.dashboard'),
                auth()->user()->isSubAdmin() => route('sub-admin.dashboard'),
                auth()->user()->isGuard() => route('guard.dashboard'),
                default => route('parent.dashboard'),
            };
        @endphp
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Notifications</h1>
            <a href="{{ $backRoute }}" class="text-sm text-gray-500">← Back to dashboard</a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="mb-4">
            @csrf
            <button class="text-sm text-blue-600 hover:underline">Mark all as read</button>
        </form>

        <div class="space-y-2">
            @forelse ($notifications as $n)
                <a href="{{ route('notifications.read', $n->id) }}"
                   class="block bg-white rounded-lg shadow p-4 {{ $n->read_at ? '' : 'border-l-4 border-blue-500' }}">
                    <p class="font-semibold {{ $n->read_at ? 'text-gray-600' : 'text-gray-900' }}">{{ $n->data['title'] }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $n->data['message'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                </a>
            @empty
                <p class="text-gray-400 text-center py-8">No notifications yet.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</body>
</html>