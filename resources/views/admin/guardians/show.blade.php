<x-layout title="Guardian View/Edit" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-2xl mx-auto">
        <x-ui.back-button :href="route('admin.users.index', ['tab' => 'guardians'])" label="Back to users" class="mb-4" />

        <x-ui.card class="mb-6">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $guardian->first_name }} {{ $guardian->last_name }}</h2>
                    <p class="text-sm text-gray-500">{{ $guardian->relationship_to_student ?? 'Guardian' }}</p>
                </div>
                <x-ui.badge color="{{ $guardian->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($guardian->status) }}</x-ui.badge>
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

        <x-ui.card title="Edit Guardian" class="mb-6">
            <form method="POST" action="{{ route('admin.guardians.update', $guardian) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $guardian->first_name) }}" required maxlength="255"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('first_name') border-red-400 @enderror">
                        @error('first_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $guardian->last_name) }}" required maxlength="255"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('last_name') border-red-400 @enderror">
                        @error('last_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email (optional)</label>
                        <input type="email" name="email" value="{{ old('email', $guardian->email) }}" maxlength="255"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
                        @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                        <input type="tel" name="phone_number" value="{{ old('phone_number', $guardian->phone_number) }}" required inputmode="numeric" pattern="09[0-9]{9}" maxlength="11" title="Enter an 11-digit mobile number starting with 09"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('phone_number') border-red-400 @enderror">
                        @error('phone_number')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Relationship to Student</label>
                        <input type="text" name="relationship_to_student" value="{{ old('relationship_to_student', $guardian->relationship_to_student) }}" maxlength="50"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active" @selected(old('status', $guardian->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $guardian->status) === 'inactive')>Inactive</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Inactive guardians stop receiving attendance SMS.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="address" rows="2" maxlength="500" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address', $guardian->address) }}</textarea>
                    </div>
                </div>
                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Save Changes</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('admin.guardians.show', $guardian) }}">Cancel</x-ui.button>
                </div>
            </form>
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

            <form method="POST" action="{{ route('admin.guardians.students.link', $guardian) }}" class="flex flex-wrap items-end gap-2">
                @csrf
                <div class="min-w-0 flex-1">
                    <label for="student-lrn" class="mb-1 block text-xs font-medium text-gray-700">Student LRN</label>
                    <input id="student-lrn" type="text" name="lrn" placeholder="Student LRN" inputmode="numeric" pattern="\d{12}" maxlength="12" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
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