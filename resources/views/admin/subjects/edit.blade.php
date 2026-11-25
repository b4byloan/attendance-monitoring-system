<x-layout title="Edit Subject" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-xl mx-auto">
        <x-ui.back-button :href="route('admin.subjects.index')" label="Back to subjects" class="mb-4" />

        <x-ui.card title="Edit Subject">
            <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('admin.subjects._form')
            </form>
        </x-ui.card>
    </div>

</x-layout>
