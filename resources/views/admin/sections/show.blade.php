<x-layout title="Section View/Edit" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="mx-auto max-w-7xl">
        <x-ui.back-button :href="route('admin.sections.index')" label="Back to sections" class="mb-4" />

        <x-ui.card class="mb-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $section->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $section->grade_level }} · {{ $section->school_year }}</p>
                </div>
                <x-ui.badge color="{{ $section->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($section->status) }}</x-ui.badge>
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="space-y-6 lg:col-span-3">
                <x-ui.card title="Section Information">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Adviser</dt><dd class="font-medium">{{ $section->adviser->name ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Maximum Capacity</dt><dd class="font-medium">{{ $section->max_capacity }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Enrolled Students</dt><dd class="font-medium">{{ $students->total() }} / {{ $section->max_capacity }}</dd></div>
                    </dl>
                </x-ui.card>

                <x-ui.card title="Assigned Teachers">
                    @forelse ($section->teachers as $teacher)
                        <div class="border-b border-gray-100 py-2 text-sm last:border-0">
                            <p class="font-medium text-gray-900">{{ $teacher->name }}</p>
                            <p class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                {{ $teacher->email }}
                                <x-ui.copy-button :value="$teacher->email" what="email" :icon-only="true" />
                                <span>· {{ $teacher->roleLabel() }}</span>
                            </p>
                        </div>
                    @empty
                        <x-ui.empty-state message="No teacher assigned yet." />
                    @endforelse
                </x-ui.card>

                <x-ui.card title="Students in this Section ({{ $students->total() }})">
                    @forelse ($students as $student)
                        <div class="flex items-center justify-between border-b border-gray-100 py-2.5 text-sm last:border-0">
                            <span>{{ $student->full_name }}</span>
                            <div class="flex items-center gap-2">
                                <x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge>
                                <a href="{{ route('admin.students.show', $student) }}" class="text-indigo-600 hover:underline">View/Edit</a>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state message="No students assigned to this section yet." />
                    @endforelse

                    <div class="mt-4">{{ $students->links() }}</div>
                </x-ui.card>

                <button type="button" onclick="document.getElementById('delete-section-modal').showModal()" class="text-sm text-red-600 hover:underline">
                    Delete Section
                </button>
            </div>

            <div class="lg:col-span-2">
                <x-ui.card title="Edit Section">
                    <form method="POST" action="{{ route('admin.sections.update', $section) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        @include('admin.sections._form', ['section' => $section])

                        <div class="flex gap-2 pt-2">
                            <x-ui.button type="submit">Save Changes</x-ui.button>
                            <x-ui.button variant="secondary" href="{{ route('admin.sections.show', $section) }}">Cancel</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>
    </div>

    <x-ui.confirm-modal
        id="delete-section-modal"
        title="Delete {{ $section->name }}?"
        form-action="{{ route('admin.sections.destroy', $section) }}"
        form-method="DELETE"
        confirm-label="Yes, delete">
        @if ($students->total() > 0)
            This section still has {{ $students->total() }} student(s) assigned. This delete will be blocked — reassign or deactivate them first, or deactivate this section instead.
        @else
            This section has no students assigned, so this is safe to delete.
        @endif
    </x-ui.confirm-modal>

</x-layout>
