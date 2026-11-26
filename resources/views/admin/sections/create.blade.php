<x-layout title="Add Section" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-3xl mx-auto">
        <x-ui.back-button :href="route('admin.sections.index')" label="Back to sections" class="mb-4" />

        <x-ui.card title="Add Section">
            <form method="POST" action="{{ route('admin.sections.store') }}" class="space-y-4">
                @csrf
                @include('admin.sections._form')

                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Save</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('admin.sections.index') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layout>