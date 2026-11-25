<x-layout title="My Sections" :unread-count="$unreadCount ?? 0" :recent-notifications="$recentNotifications ?? collect()">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h2 class="text-xl font-bold text-gray-900">My Sections</h2>
            <p class="text-sm text-gray-500">The sections assigned to you, with today's attendance totals. Select one to view its students.</p>
        </div>
        <x-ui.button href="{{ route('sub-admin.roster.index', ['tab' => 'sections']) }}" variant="secondary" size="sm">Student List</x-ui.button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($sections as $section)
            <x-ui.section-card :section="$section" />
        @empty
            <x-ui.card class="sm:col-span-2 xl:col-span-3">
                <x-ui.empty-state message="No sections are assigned to you yet — contact your admin." />
            </x-ui.card>
        @endforelse
    </div>

</x-layout>
