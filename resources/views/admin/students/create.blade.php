<x-layout title="Add Student" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-3xl mx-auto">
        <x-ui.back-button :href="route('admin.users.index', ['tab' => 'students'])" label="Back to users" class="mb-4" />

        <x-ui.card title="Add Student">
            <form method="POST" action="{{ route('admin.students.store') }}" class="space-y-4">
                @csrf
                @include('admin.students._form')

                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Save</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('admin.users.index', ['tab' => 'students']) }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layout>