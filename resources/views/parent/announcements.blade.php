<x-simple-layout title="Announcements" :unread-count="$unreadCount ?? 0" :recent-notifications="$recentNotifications ?? collect()">

    <x-ui.back-button :href="route('parent.dashboard')" label="Back to dashboard" class="mb-4" />

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Announcements</h2>
        <p class="text-sm text-gray-500">Updates from the school and your student's section.</p>
    </div>

    <div class="space-y-3">
        @forelse ($announcements as $a)
            <x-ui.card>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            @if ($a->is_pinned)
                                <x-ui.icon name="pin" class="h-3.5 w-3.5 text-amber-500" />
                            @endif
                            <h3 class="text-base font-semibold text-gray-900">{{ $a->title }}</h3>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $a->isSchoolWide() ? 'School-wide' : ($a->section->name ?? 'Section') }}
                            · {{ $a->publish_date->format('M j, Y') }}
                        </p>
                    </div>
                    @if ($a->is_pinned)
                        <x-ui.badge color="amber">Pinned</x-ui.badge>
                    @endif
                </div>
                <p class="mt-3 whitespace-pre-line text-sm text-gray-700">{{ $a->description }}</p>
            </x-ui.card>
        @empty
            <x-ui.card>
                <x-ui.empty-state message="No announcements right now." />
            </x-ui.card>
        @endforelse
    </div>

    <div class="mt-4">{{ $announcements->links() }}</div>

</x-simple-layout>
