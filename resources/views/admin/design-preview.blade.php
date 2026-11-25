<x-layout title="Design Preview" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <x-ui.notice type="info">This page exists purely to preview the new shared design system before it's applied anywhere real. Safe to delete later, or keep as a living style guide.</x-ui.notice>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-ui.card title="Buttons">
            <div class="flex flex-wrap gap-2">
                <x-ui.button variant="primary">Primary</x-ui.button>
                <x-ui.button variant="secondary">Secondary</x-ui.button>
                <x-ui.button variant="danger">Danger</x-ui.button>
                <x-ui.button variant="ghost">Ghost</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card title="Badges">
            <div class="flex flex-wrap gap-2">
                <x-ui.badge color="green">Active</x-ui.badge>
                <x-ui.badge color="red">Inactive</x-ui.badge>
                <x-ui.badge color="amber">Pending</x-ui.badge>
                <x-ui.badge color="blue">Info</x-ui.badge>
                <x-ui.badge color="gray">Draft</x-ui.badge>
            </div>
        </x-ui.card>

        <x-ui.card title="Stat Card Example">
            <p class="text-sm text-gray-500">Total Students</p>
            <p class="text-3xl font-bold text-gray-900">248</p>
        </x-ui.card>
    </div>

    <x-ui.card title="Toasts" class="mb-8">
        <p class="mb-3 text-sm text-gray-500">Transient feedback appears in the top-right corner and dismisses itself. Flash messages and validation errors are delivered this way automatically.</p>
        <div class="flex flex-wrap gap-2">
            <x-ui.button type="button" variant="secondary" onclick="window.showToast('success', 'Saved successfully.')">Success toast</x-ui.button>
            <x-ui.button type="button" variant="secondary" onclick="window.showToast('error', 'Something went wrong.')">Error toast</x-ui.button>
            <x-ui.button type="button" variant="secondary" onclick="window.showToast('warning', 'This action needs your attention.')">Warning toast</x-ui.button>
            <x-ui.button type="button" variant="secondary" onclick="window.showToast('info', 'Just a heads up.')">Info toast</x-ui.button>
        </div>
    </x-ui.card>

    <x-ui.card title="Notices" class="mb-8">
        <p class="mb-3 text-sm text-gray-500">Persistent, in-page callouts for context that must stay visible while the page is open.</p>
        <div class="space-y-2">
            <x-ui.notice type="success">Saved successfully.</x-ui.notice>
            <x-ui.notice type="error">Something went wrong.</x-ui.notice>
            <x-ui.notice type="warning">This action needs your attention.</x-ui.notice>
            <x-ui.notice type="info">Just a heads up.</x-ui.notice>
        </div>
    </x-ui.card>

    <x-ui.card title="Table" class="mb-8">
        <x-ui.table>
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Status</th></tr>
            </thead>
            <tbody class="divide-y">
                <tr><td class="px-4 py-3">Juan Dela Cruz</td><td class="px-4 py-3"><x-ui.badge color="green">Active</x-ui.badge></td></tr>
                <tr><td class="px-4 py-3">Maria Santos</td><td class="px-4 py-3"><x-ui.badge color="gray">Inactive</x-ui.badge></td></tr>
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <x-ui.card title="Empty State" class="mb-8">
        <x-ui.empty-state message="No records found." />
    </x-ui.card>

    <x-ui.card title="Confirmation Modal">
        <button type="button" onclick="document.getElementById('demo-modal').showModal()"
                class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700">
            Try a delete action
        </button>
    </x-ui.card>

    <x-ui.confirm-modal id="demo-modal" title="Delete this item?" form-action="#" confirm-label="Yes, delete">
        This is just a preview — nothing will actually be deleted.
    </x-ui.confirm-modal>

</x-layout>