<x-layout title="Add Guardian" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-3xl mx-auto">
        <x-ui.back-button :href="route('admin.users.index', ['tab' => 'guardians'])" label="Back to users" class="mb-4" />

        <x-ui.card title="Add Guardian">
            <form method="POST" action="{{ route('admin.guardians.store') }}" class="space-y-4">
                @csrf
                @include('admin.guardians._form')

                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Save</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('admin.users.index', ['tab' => 'guardians']) }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layout>