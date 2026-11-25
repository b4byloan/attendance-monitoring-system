<x-layout title="Section Details" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-2xl mx-auto">
        <x-ui.back-button :href="route('admin.sections.index')" label="Back to sections" class="mb-4" />

        <x-ui.card class="mb-6">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $section->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $section->grade_level }} · {{ $section->school_year }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-ui.badge color="{{ $section->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($section->status) }}</x-ui.badge>
                    <x-ui.button href="{{ route('admin.sections.edit', $section) }}" variant="secondary" size="sm">Edit</x-ui.button>
                </div>
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <x-ui.card title="Assigned Teachers">
                @forelse ($section->teachers as $teacher)
                    <div class="border-b border-gray-100 py-2 text-sm last:border-0">
                        <p class="font-medium text-gray-900">{{ $teacher->name }}</p>
                        <p class="text-xs text-gray-500">{{ $teacher->email }} · {{ $teacher->roleLabel() }}</p>
                    </div>
                @empty
                    <x-ui.empty-state message="No teacher assigned yet." />
                @endforelse
            </x-ui.card>

            <x-ui.card title="Enrollment">
                <p class="text-2xl font-bold text-gray-900">
                    {{ $students->total() }}{{ $section->max_capacity ? ' / ' . $section->max_capacity : '' }}
                </p>
            </x-ui.card>
        </div>

        <x-ui.card title="Students in this Section ({{ $students->total() }})" class="mb-6">
            @forelse ($students as $student)
                <div class="flex justify-between items-center py-2.5 border-b border-gray-100 text-sm last:border-0">
                    <span>{{ $student->full_name }}</span>
                    <div class="flex items-center gap-2">
                        <x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge>
                        <a href="{{ route('admin.students.show', $student) }}" class="text-indigo-600 hover:underline">View</a>
                    </div>
                </div>
            @empty
                <x-ui.empty-state message="No students assigned to this section yet." />
            @endforelse
        </x-ui.card>

        <div class="mb-6">{{ $students->links() }}</div>

        <button type="button" onclick="document.getElementById('delete-section-modal').showModal()" class="text-red-600 text-sm hover:underline">
            Delete Section
        </button>
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