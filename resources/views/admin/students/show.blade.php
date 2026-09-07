<x-layout title="Student Details" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-2xl mx-auto">
        <x-ui.card class="mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if ($student->photo_path)
                        <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-16 h-16 rounded-full object-cover">
                    @else
                        <div class="w-16 h-16 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold">
                            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $student->full_name }}</h2>
                        <p class="text-sm text-gray-500">LRN: {{ $student->lrn }}</p>
                    </div>
                </div>
                <x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge>
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <x-ui.card title="Student Information">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Section</dt><dd class="font-medium">{{ $student->section->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Grade Level</dt><dd class="font-medium">{{ $student->section->grade_level ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Attendance Records</dt><dd class="font-medium">{{ $student->attendanceRecords->count() }}</dd></div>
                </dl>
            </x-ui.card>

            <x-ui.card title="QR Code">
                <p class="text-sm text-gray-500 mb-3">Used to check this student in and out at the gate.</p>
                <x-ui.button href="{{ route('admin.students.qr-code.show', $student) }}" variant="secondary">View / Print QR Code</x-ui.button>
            </x-ui.card>
        </div>

        <x-ui.card title="Guardians" class="mb-6">
            @forelse ($student->guardians as $guardian)
                <a href="{{ route('admin.guardians.show', $guardian) }}" class="block py-2 border-b border-gray-100 text-sm last:border-0 hover:text-indigo-600">
                    {{ $guardian->first_name }} {{ $guardian->last_name }}
                    <span class="text-gray-400">— {{ $guardian->relationship_to_student ?? 'Guardian' }}</span>
                </a>
            @empty
                <x-ui.empty-state message="No guardians linked yet." />
            @endforelse
        </x-ui.card>

        <div class="flex gap-2">
            <x-ui.button href="{{ route('admin.students.edit', $student) }}">Edit Student</x-ui.button>
            <x-ui.button variant="secondary" href="{{ route('admin.students.index') }}">Back to List</x-ui.button>
        </div>
    </div>

</x-layout>