<x-simple-layout title="My Profile" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-semibold text-gray-900">My Profile</h2>
        <x-ui.back-button :href="route('student.dashboard')" label="Back to dashboard" />
    </div>

    <x-ui.card title="Student Information" class="mb-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500 text-xs uppercase">Full Name</dt><dd class="font-medium mt-0.5">{{ $student->full_name }}</dd></div>
            <div>
                <dt class="text-gray-500 text-xs uppercase">LRN</dt>
                <dd class="mt-0.5 flex flex-wrap items-center gap-2">
                    <span class="font-mono font-medium">{{ $student->lrn }}</span>
                    <x-ui.copy-button :value="$student->lrn" />
                </dd>
            </div>
            <div><dt class="text-gray-500 text-xs uppercase">Status</dt><dd class="mt-0.5"><x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge></dd></div>
        </dl>
    </x-ui.card>

    <x-ui.card title="Section" class="mb-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500 text-xs uppercase">Grade Level</dt><dd class="font-medium mt-0.5">{{ $student->section->grade_level ?? '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs uppercase">Section</dt><dd class="font-medium mt-0.5">{{ $student->section->name ?? '—' }}</dd></div>
            <div><dt class="text-gray-500 text-xs uppercase">Adviser</dt><dd class="font-medium mt-0.5">{{ $student->section->adviser->name ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>

    <x-ui.card title="Account">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500 text-xs uppercase">Login Email</dt><dd class="font-medium mt-0.5">{{ auth()->user()->email }}</dd></div>
        </dl>
        <a href="{{ route('student.account-settings') }}" class="mt-4 inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">Edit account settings <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" /></a>
    </x-ui.card>

</x-simple-layout>