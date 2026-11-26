<x-layout title="New Announcement" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-3xl mx-auto">
        <x-ui.back-button :href="route('announcements.index')" label="Back to announcements" class="mb-4" />

        <x-ui.card title="New Announcement">
            <form method="POST" action="{{ route('announcements.store') }}" class="space-y-4">
                @csrf
                @include('announcements._form')

                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Publish / Save</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('announcements.index') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layout>