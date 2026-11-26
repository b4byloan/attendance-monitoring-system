<x-layout title="Subject View/Edit" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="mx-auto max-w-7xl">
        <x-ui.back-button :href="route('admin.subjects.index')" label="Back to subjects" class="mb-4" />

        <x-ui.card class="mb-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $subject->name }}</h2>
                    <p class="font-mono text-xs text-gray-500">{{ $subject->code }}</p>
                </div>
                <x-ui.badge color="{{ $subject->isActive() ? 'green' : 'gray' }}">{{ ucfirst($subject->status) }}</x-ui.badge>
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="space-y-6 lg:col-span-3">
                <x-ui.card title="Subject Information">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Subject Name</dt>
                            <dd class="font-medium">{{ $subject->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Subject Code</dt>
                            <dd class="font-mono font-medium">{{ $subject->code }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Status</dt>
                            <dd class="font-medium">{{ ucfirst($subject->status) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Attendance Records</dt>
                            <dd class="font-medium">{{ $subject->attendance_records_count }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Created</dt>
                            <dd class="font-medium">{{ $subject->created_at?->format('M j, Y') }}</dd>
                        </div>
                        <div class="border-t border-gray-100 pt-2">
                            <dt class="text-gray-500">Description</dt>
                            <dd class="mt-1 font-medium">{{ $subject->description ?? '—' }}</dd>
                        </div>
                    </dl>
                </x-ui.card>

                <x-ui.card title="Teachers Handling this Subject ({{ $subject->teachers->count() }})">
                    @forelse ($subject->teachers as $teacher)
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 py-2 text-sm last:border-0">
                            <div>
                                <p class="font-medium text-gray-900">{{ $teacher->name }}</p>
                                <p class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                    {{ $teacher->email }}
                                    <x-ui.copy-button :value="$teacher->email" what="email" :icon-only="true" />
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-ui.badge color="{{ $teacher->is_active ? 'green' : 'gray' }}">{{ $teacher->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                                <span class="text-xs text-gray-400">{{ $teacher->roleLabel() }}</span>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state message="No teacher is handling this subject yet." />
                    @endforelse
                </x-ui.card>

                <button type="button" onclick="document.getElementById('delete-subject-modal').showModal()" class="text-sm text-red-600 hover:underline">
                    Delete Subject
                </button>
            </div>

            <div class="lg:col-span-2">
                <x-ui.card title="Edit Subject">
                    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        @include('admin.subjects._form', ['subject' => $subject])

                        <div class="flex gap-2 pt-2">
                            <x-ui.button type="submit">Save Changes</x-ui.button>
                            <x-ui.button variant="secondary" href="{{ route('admin.subjects.show', $subject) }}">Cancel</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>
    </div>

    <x-ui.confirm-modal
        id="delete-subject-modal"
        title="Delete {{ $subject->name }}?"
        form-action="{{ route('admin.subjects.destroy', $subject) }}"
        form-method="DELETE"
        confirm-label="Yes, delete">
        Subjects with existing attendance records cannot be deleted; deactivate them instead.
    </x-ui.confirm-modal>

</x-layout>
