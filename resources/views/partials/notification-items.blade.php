@forelse ($notifications as $notification)
    <a href="{{ route('notifications.read', $notification->id) }}"
       class="block border-b border-gray-100 px-4 py-3 text-sm transition last:border-0 hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-emerald-50/60' }}">
        <div class="flex items-start justify-between gap-2">
            <p class="text-xs font-semibold text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
            @if (! $notification->read_at)
                <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-600" aria-label="Unread"></span>
            @endif
        </div>
        <p class="mt-1 line-clamp-2 text-xs text-gray-500">{{ $notification->data['message'] ?? '' }}</p>
        <p class="mt-1 text-[11px] text-gray-400">{{ $notification->created_at->format('M j, g:i A') }}</p>
    </a>
@empty
    <div class="px-4 py-6 text-center">
        <p class="text-sm text-gray-400">No notifications yet.</p>
    </div>
@endforelse
