<x-layout title="Edit Announcement" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-3xl mx-auto">
        <x-ui.back-button :href="route('announcements.index')" label="Back to announcements" class="mb-4" />

        <x-ui.card title="Edit Announcement">
            <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="space-y-4">
                @csrf
                @method('PUT')

                @include('announcements._form', ['announcement' => $announcement])

                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Save Changes</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('announcements.index') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layout>