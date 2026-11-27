@forelse ($notifications as $notification)
    @php
        $linkRequestId = $notification->data['link_request_id'] ?? null;
        $linkRequest = $linkRequestId ? \App\Models\GuardianLinkRequest::find($linkRequestId) : null;
        $canRespond = $linkRequest
            && $linkRequest->isPending()
            && auth()->user()?->student
            && $linkRequest->student_id === auth()->user()->student->id;
    @endphp

    @if ($canRespond)
        <div class="border-b border-gray-100 px-4 py-3 text-sm last:border-0 {{ $notification->read_at ? '' : 'bg-emerald-50/60' }}">
            <div class="flex items-start justify-between gap-2">
                <p class="text-xs font-semibold text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                @if (! $notification->read_at)
                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-600" aria-label="Unread"></span>
                @endif
            </div>
            <p class="mt-1 text-xs text-gray-500">{{ $notification->data['message'] ?? '' }}</p>
            <p class="mt-1 text-[11px] text-gray-400">{{ $notification->created_at->format('M j, g:i A') }}</p>

            <div class="mt-2 flex flex-wrap gap-2">
                <form method="POST" action="{{ route('student.link-requests.accept', $linkRequest) }}">
                    @csrf
                    <x-ui.button type="submit" size="sm">Accept</x-ui.button>
                </form>
                <form method="POST" action="{{ route('student.link-requests.deny', $linkRequest) }}">
                    @csrf
                    <x-ui.button type="submit" variant="secondary" size="sm">Deny</x-ui.button>
                </form>
            </div>
        </div>
    @else
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
    @endif
@empty
    <div class="px-4 py-6 text-center">
        <p class="text-sm text-gray-400">No notifications yet.</p>
    </div>
@endforelse
