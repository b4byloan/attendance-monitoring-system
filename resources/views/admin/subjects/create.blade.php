<x-layout title="Add Subject" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-3xl mx-auto">
        <x-ui.back-button :href="route('admin.subjects.index')" label="Back to subjects" class="mb-4" />

        <x-ui.card title="Add Subject">
            <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-4">
                @csrf
                @include('admin.subjects._form')
            </form>
        </x-ui.card>
    </div>

</x-layout>
