<x-layout title="Announcements" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Announcements</h2>
        <x-ui.button href="{{ route('announcements.create') }}"><x-ui.icon name="plus" class="h-4 w-4" /> New Announcement</x-ui.button>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Search by title</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search announcements by title"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
            @if (request('search'))
                <a href="{{ route('announcements.index') }}" class="text-sm text-gray-500 hover:underline pb-2">Clear</a>
            @endif
        </form>
    </x-ui.card>

    <div class="space-y-4">
        @forelse ($announcements as $a)
            @php $canManage = auth()->user()->isAdmin() || $a->author_id === auth()->id(); @endphp
            <x-ui.card class="{{ $a->is_pinned ? 'border-l-4 border-l-amber-400' : '' }}">
                <div class="flex items-center gap-2 flex-wrap mb-2">
                    @if ($a->is_pinned)
                        <span class="text-amber-500" title="Pinned"><x-ui.icon name="pin" class="h-4 w-4" /></span>
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
                <x-ui.empty-state message="{{ request('search') ? 'No announcements match your search.' : 'No announcements yet.' }}">
                    @if (! request('search'))
                        <x-slot:action>
                            <x-ui.button href="{{ route('announcements.create') }}" size="sm">+ Create your first announcement</x-ui.button>
                        </x-slot:action>
                    @endif
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
                This permanently removes the announcement.
            </x-ui.confirm-modal>
        @endif
    @endforeach

</x-layout>
