<x-layout title="Guardian Details" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-2xl mx-auto">
        <x-ui.back-button :href="route('admin.guardians.index')" label="Back to guardians" class="mb-4" />

        <x-ui.card class="mb-6">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $guardian->first_name }} {{ $guardian->last_name }}</h2>
                    <p class="text-sm text-gray-500">{{ $guardian->relationship_to_student ?? 'Guardian' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-ui.badge color="{{ $guardian->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($guardian->status) }}</x-ui.badge>
                    <x-ui.button href="{{ route('admin.guardians.edit', $guardian) }}" variant="secondary" size="sm">Edit</x-ui.button>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Contact Information" class="mb-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500 text-xs uppercase">Mobile</dt>
                    <dd class="font-medium mt-0.5">{{ $guardian->phone_number }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 text-xs uppercase">Email</dt>
                    <dd class="font-medium mt-0.5">{{ $guardian->email ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-gray-500 text-xs uppercase">Address</dt>
                    <dd class="font-medium mt-0.5">{{ $guardian->address ?? '—' }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Parent Portal Account" class="mb-6">
            @if ($guardian->user)
                <p class="text-sm">Linked to <strong>{{ $guardian->user->email }}</strong>
                    — <x-ui.badge color="{{ $guardian->user->is_active ? 'green' : 'gray' }}">{{ $guardian->user->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                </p>
            @else
                <p class="text-sm text-gray-500 mb-3">No Parent account yet — this guardian can still receive SMS, but can't log into the Parent Portal.</p>
                <form method="POST" action="{{ route('admin.guardians.create-account', $guardian) }}" class="flex flex-wrap gap-2 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Login email</label>
                        <input type="email" name="account_email" value="{{ old('account_email', $guardian->email) }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Initial password</label>
                        <input type="password" name="account_password" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <x-ui.button type="submit" size="sm">Create Account</x-ui.button>
                </form>
            @endif
        </x-ui.card>

        <x-ui.card title="Linked Students" class="mb-6">
            <div class="space-y-2 mb-4">
                @forelse ($guardian->students as $student)
                    <div class="flex justify-between items-center border border-gray-200 rounded-lg px-4 py-2.5 text-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <div>
                                <span class="font-medium">{{ $student->full_name }}</span>
                                <span class="text-gray-400"> — {{ $student->section->name ?? 'No section' }}</span>
                            </div>
                            <span class="font-mono text-xs text-gray-500">{{ $student->lrn }}</span>
                            <x-ui.copy-button :value="$student->lrn" />
                        </div>
                        <button type="button" onclick="document.getElementById('unlink-modal-{{ $student->id }}').showModal()" class="text-red-600 hover:underline text-xs">
                            Unlink
                        </button>
                    </div>
                @empty
                    <x-ui.empty-state message="No students linked yet." />
                @endforelse
            </div>

            <form method="POST" action="{{ route('admin.guardians.students.link', $guardian) }}" class="flex gap-2">
                @csrf
                <input type="text" name="lrn" placeholder="Student LRN" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <x-ui.button type="submit" size="sm">Link Student</x-ui.button>
            </form>
        </x-ui.card>

        <button type="button" onclick="document.getElementById('delete-guardian-modal').showModal()" class="text-red-600 text-sm hover:underline">
            Delete Guardian
        </button>
    </div>

    @foreach ($guardian->students as $student)
        <x-ui.confirm-modal
            id="unlink-modal-{{ $student->id }}"
            title="Unlink {{ $student->full_name }}?"
            form-action="{{ route('admin.guardians.students.unlink', [$guardian, $student]) }}"
            form-method="DELETE"
            confirm-label="Yes, unlink"
            warning="This removes the link only — both records stay in the system and can be linked again.">
            This only removes the connection between this guardian and this student — neither record itself is deleted.
        </x-ui.confirm-modal>
    @endforeach

    <x-ui.confirm-modal
        id="delete-guardian-modal"
        title="Delete {{ $guardian->first_name }} {{ $guardian->last_name }}?"
        form-action="{{ route('admin.guardians.destroy', $guardian) }}"
        form-method="DELETE"
        confirm-label="Yes, delete">
        @if ($guardian->students->count() > 0)
            This guardian is linked to {{ $guardian->students->count() }} student(s). Deleting will remove those links too.
        @else
            This guardian has no linked students, so this is safe to delete.
        @endif
    </x-ui.confirm-modal>

</x-layout>