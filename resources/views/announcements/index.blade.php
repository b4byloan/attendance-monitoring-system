<x-layout title="Announcements" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Announcements</h2>
        <x-ui.button href="{{ route('announcements.create') }}">+ New Announcement</x-ui.button>
    </div>

    <div class="space-y-4">
        @forelse ($announcements as $a)
            @php $canManage = auth()->user()->isAdmin() || $a->author_id === auth()->id(); @endphp
            <x-ui.card class="{{ $a->is_pinned ? 'border-l-4 border-l-amber-400' : '' }}">
                <div class="flex items-center gap-2 flex-wrap mb-2">
                    @if ($a->is_pinned)
                        <span class="text-amber-500 text-lg" title="Pinned">📌</span>
                    @endif
                    <h3 class="text-base font-bold text-gray-900">{{ $a->title }}</h3>
                    <x-ui.badge color="{{ $a->isSchoolWide() ? 'indigo' : 'blue' }}">
                        {{ $a->isSchoolWide() ? 'School-wide' : ($a->section->name ?? 'Section') }}
                    </x-ui.badge>
                    @if ($a->status === 'draft')
                        <x-ui.badge color="gray">Draft</x-ui.badge>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mb-3">
                    By {{ $a->author->name ?? 'Unknown' }} · Published {{ $a->publish_date->format('M j, Y') }}
                    @if ($a->expiration_date) · Expires {{ $a->expiration_date->format('M j, Y') }} @endif
                </p>
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $a->description }}</p>

                @if ($canManage)
                    <div class="flex gap-4 mt-4 pt-4 border-t border-gray-100 text-sm">
                        <a href="{{ route('announcements.edit', $a) }}" class="text-indigo-600 hover:underline">Edit</a>
                        <button type="button" onclick="document.getElementById('delete-modal-{{ $a->id }}').showModal()" class="text-red-600 hover:underline">Delete</button>
                        @if (auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('announcements.toggle-pin', $a) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button class="text-amber-600 hover:underline">{{ $a->is_pinned ? 'Unpin' : 'Pin' }}</button>
                            </form>
                        @endif
                    </div>
                @endif
            </x-ui.card>
        @empty
            <x-ui.card>
                <x-ui.empty-state message="No announcements yet.">
                    <x-slot:action>
                        <x-ui.button href="{{ route('announcements.create') }}" size="sm">+ Create your first announcement</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </x-ui.card>
        @endforelse
    </div>

    <div class="mt-4">{{ $announcements->links() }}</div>

    @foreach ($announcements as $a)
        @php $canManage = auth()->user()->isAdmin() || $a->author_id === auth()->id(); @endphp
        @if ($canManage)
            <x-ui.confirm-modal
                id="delete-modal-{{ $a->id }}"
                title="Delete '{{ $a->title }}'?"
                form-action="{{ route('announcements.destroy', $a) }}"
                form-method="DELETE"
                confirm-label="Yes, delete">
                This cannot be undone.
            </x-ui.confirm-modal>
        @endif
    @endforeach

</x-layout>