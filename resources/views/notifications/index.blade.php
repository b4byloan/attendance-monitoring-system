<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 px-4 py-5 text-gray-900 sm:p-8">
    <div class="mx-auto w-full max-w-2xl">
        <x-ui.toast-container />
        @php
            $backRoute = match(true) {
                auth()->user()->isAdmin() => route('admin.dashboard'),
                auth()->user()->isTeachingStaff() => route('sub-admin.dashboard'),
                auth()->user()->isGuard() => route('guard.dashboard'),
                auth()->user()->isStudent() => route('student.dashboard'),
                default => route('parent.dashboard'),
            };
        @endphp
        <div class="mb-6 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-xl font-bold sm:text-2xl">Notifications</h1>
            <x-ui.back-button :href="$backRoute" label="Back to dashboard" />
        </div>

        <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="mb-4">
            @csrf
            <button class="text-base text-blue-600 hover:underline">Mark all as read</button>
        </form>

        <div class="space-y-3">
            @forelse ($notifications as $n)
                <a href="{{ route('notifications.read', $n->id) }}"
                   class="block min-w-0 break-words rounded-lg bg-white p-4 shadow sm:p-5 {{ $n->read_at ? '' : 'border-l-4 border-blue-500' }}">
                    <p class="text-base font-semibold leading-6 {{ $n->read_at ? 'text-gray-600' : 'text-gray-900' }}">{{ $n->data['title'] }}</p>
                    <p class="mt-1 text-base leading-6 text-gray-600">{{ $n->data['message'] }}</p>
                    <p class="mt-2 text-sm text-gray-500">{{ $n->created_at->diffForHumans() }}</p>
                </a>
            @empty
                <p class="text-gray-400 text-center py-8">No notifications yet.</p>
            @endforelse
        </div>
        <div class="mt-4 overflow-x-auto">{{ $notifications->links() }}</div>
    </div>
</body>
</html>