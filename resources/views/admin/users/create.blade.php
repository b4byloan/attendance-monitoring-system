<x-layout title="Add User" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-3xl mx-auto">
        <x-ui.back-button :href="route('admin.users.index')" label="Back to users" class="mb-4" />

        <x-ui.card title="Add User">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                @include('admin.users._form')

                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Create Account</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('admin.users.index') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layout>
