<x-layout title="Announcements" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="sticky top-0 z-20 -mx-1 mb-6 bg-gray-50/95 px-1 py-2 backdrop-blur">
        <x-ui.card>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-xs text-gray-500">Search by title</label>
                    <x-ui.form-field icon="search">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search announcements by title"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
                <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
                @if ($canCreate)
                    <x-ui.button type="button" onclick="document.getElementById('add-announcement-modal').showModal()"><x-ui.icon name="plus" class="h-4 w-4" /> Create Announcements</x-ui.button>
                @endif
                @if (request('search'))
                    <a href="{{ route('announcements.index') }}" class="pb-2 text-sm text-gray-500 hover:underline">Clear</a>
                @endif
            </form>
        </x-ui.card>
    </div>

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
                <x-ui.empty-state message="{{ request('search') ? 'No announcements match your search.' : 'No announcements yet.' }}" />
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

    @if ($canCreate)
        <x-ui.form-modal id="add-announcement-modal" title="New Announcement" action="{{ route('announcements.store') }}" submit-label="Publish / Save">
            @include('announcements._form')
        </x-ui.form-modal>
    @endif

</x-layout>
