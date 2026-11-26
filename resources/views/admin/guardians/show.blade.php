<x-layout title="Guardian View/Edit" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="mx-auto max-w-7xl">
        <x-ui.back-button :href="route('admin.users.index', ['tab' => 'guardians'])" label="Back to users" class="mb-4" />

        <x-ui.card class="mb-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex items-center gap-4">
                    <x-ui.avatar :name="$guardian->first_name.' '.$guardian->last_name" size="lg" />
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $guardian->first_name }} {{ $guardian->last_name }}</h2>
                        <p class="text-sm text-gray-500">{{ $guardian->relationship_to_student ?? 'Guardian' }}</p>
                    </div>
                </div>
                <x-ui.badge color="{{ $guardian->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($guardian->status) }}</x-ui.badge>
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="space-y-6 lg:col-span-3">
                <x-ui.card title="Contact Information">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Mobile</dt>
                            <dd class="flex items-center gap-2 font-medium">
                                {{ $guardian->phone_number }}
                                <x-ui.copy-button :value="$guardian->phone_number" what="phone number" :icon-only="true" />
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Email</dt>
                            <dd class="flex items-center gap-2 font-medium">
                                {{ $guardian->email ?? '—' }}
                                @if ($guardian->email)
                                    <x-ui.copy-button :value="$guardian->email" what="email" :icon-only="true" />
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Relationship</dt>
                            <dd class="font-medium">{{ $guardian->relationship_to_student ?? '—' }}</dd>
                        </div>
                        <div class="border-t border-gray-100 pt-2">
                            <dt class="text-gray-500">Address</dt>
                            <dd class="mt-1 font-medium">{{ $guardian->address ?? '—' }}</dd>
                        </div>
                    </dl>
                </x-ui.card>

                <x-ui.card title="Linked Students">
                    <div class="mb-4 space-y-2">
                        @forelse ($guardian->students as $student)
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-2.5 text-sm">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div>
                                        <span class="font-medium">{{ $student->full_name }}</span>
                                        <span class="text-gray-400"> — {{ $student->section->name ?? 'No section' }}</span>
                                    </div>
                                    <span class="font-mono text-xs text-gray-500">{{ $student->lrn }}</span>
                                    <x-ui.copy-button :value="$student->lrn" />
                                </div>
                                <button type="button" onclick="document.getElementById('unlink-modal-{{ $student->id }}').showModal()" class="text-xs text-red-600 hover:underline">
                                    Unlink
                                </button>
                            </div>
                        @empty
                            <x-ui.empty-state message="No students linked yet." />
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('admin.guardians.students.link', $guardian) }}" class="flex flex-wrap items-end gap-2">
                        @csrf
                        <div class="min-w-0 flex-1">
                            <label for="student-lrn" class="mb-1 block text-xs font-medium text-gray-700">Student LRN</label>
                            <x-ui.form-field icon="hash">
                                <input id="student-lrn" type="text" name="lrn" placeholder="Student LRN" inputmode="numeric" pattern="\d{12}" maxlength="12" required
                                       data-digits-only data-max-digits="12"
                                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                            </x-ui.form-field>
                        </div>
                        <x-ui.button type="submit" size="sm">Link Student</x-ui.button>
                    </form>
                </x-ui.card>

                <button type="button" onclick="document.getElementById('delete-guardian-modal').showModal()" class="text-sm text-red-600 hover:underline">
                    Delete Guardian
                </button>
            </div>

            <div class="lg:col-span-2">
                <x-ui.card title="Edit Guardian">
                    <form method="POST" action="{{ route('admin.guardians.update', $guardian) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        @include('admin.guardians._form', ['guardian' => $guardian])

                        <div class="flex gap-2 pt-2">
                            <x-ui.button type="submit">Save Changes</x-ui.button>
                            <x-ui.button variant="secondary" href="{{ route('admin.guardians.show', $guardian) }}">Cancel</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>
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
