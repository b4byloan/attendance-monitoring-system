<x-layout title="Users" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    @php
        $tabs = [
            'students' => 'Students',
            'guardians' => 'Guardians',
            'admin' => 'Admin',
            'teachers' => 'Teachers',
            'guard' => 'Guards',
        ];

        $searchPlaceholder = match ($tab) {
            'students' => 'Search by name, LRN, or registered email',
            'guardians' => 'Search by name, email, or phone',
            default => 'Search by name or email',
        };
    @endphp

    @php
        $defaultAccountType = match ($tab) {
            'guardians' => 'guardian',
            'teachers' => 'teacher',
            'admin' => 'admin',
            'guard' => 'guard',
            default => 'student',
        };
    @endphp

    <div class="mb-6 overflow-x-auto border-b border-gray-200">
        <nav class="flex min-w-max gap-1" aria-label="User categories">
            @foreach ($tabs as $value => $label)
                <a href="{{ route('admin.users.index', ['tab' => $value]) }}"
                   @class([
                       'inline-flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition',
                       'border-emerald-600 text-emerald-700' => $tab === $value,
                       'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => $tab !== $value,
                   ])>
                    {{ $label }}
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ $tabCounts[$value] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-1">
                <label for="user-search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input id="user-search" type="text" name="search" value="{{ $search }}"
                           placeholder="{{ $searchPlaceholder }}"
                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 pl-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            @if ($tab === 'students')
                <div class="sm:w-56">
                    <label for="user-section-filter" class="sr-only">Section</label>
                    <select id="user-section-filter" name="section"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Sections</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}" @selected((int) request('section') === $section->id)>{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-300">
                <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                </svg>
                Search
            </button>
            <x-ui.button type="button" onclick="document.getElementById('add-user-modal').showModal()"><x-ui.icon name="plus" class="h-4 w-4" /> Add User</x-ui.button>
            @if ($search !== '' || request('section'))
                <a href="{{ route('admin.users.index', ['tab' => $tab]) }}"
                   class="pb-1 text-sm font-medium text-gray-500 hover:text-gray-900 hover:underline">Clear</a>
            @endif
        </form>
    </x-ui.card>

    @if ($tab === 'students')
        <x-ui.card class="mb-6">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Student Records</h3>
            <x-ui.table :fit="true">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-700">
                    <tr>
                        <th scope="col" class="w-12 px-4 py-3 text-left font-semibold">#</th>
                        <x-ui.sortable-th column="lrn" label="LRN" class="font-semibold" />
                        <x-ui.sortable-th column="name" label="Name" class="font-semibold" />
                        <x-ui.sortable-th column="grade_level" label="Grade Level" class="font-semibold" />
                        <x-ui.sortable-th column="section" label="Section" class="font-semibold" />
                        <x-ui.sortable-th column="email" label="Email" class="font-semibold" />
                        <x-ui.sortable-th column="status" label="Status" class="font-semibold" />
                        <th scope="col" class="px-4 py-3 text-left font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($students as $student)
                        <tr class="bg-white transition-colors hover:bg-gray-50">
                            <td class="px-4 py-4 text-sm font-medium text-gray-500">{{ $students->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-4">
                                <span class="flex items-center gap-2">
                                    <span class="font-mono text-xs text-gray-700">{{ $student->lrn }}</span>
                                    <x-ui.copy-button :value="$student->lrn" what="LRN" :icon-only="true"/>
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="flex items-center gap-3">
                                    <x-ui.avatar :name="$student->full_name" :photo="$student->photo_path" size="xs" />
                                    <span class="whitespace-nowrap font-semibold text-gray-900">{{ $student->full_name }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap"><x-ui.optional-value :value="$student->section->grade_level ?? null" /></td>
                            <td class="px-4 py-4"><x-ui.optional-value :value="$student->section->name ?? null" /></td>
                            <td class="px-4 py-4">
                                @if ($student->user?->email)
                                    <span class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                                        {{ $student->user->email }}
                                        <x-ui.copy-button :value="$student->user->email" what="email" :icon-only="true" />
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Not registered</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-4 text-sm">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <a href="{{ route('admin.students.show', $student) }}" class="whitespace-nowrap font-medium text-blue-600 hover:underline">View/Edit</a>
                                    <button type="button" onclick="document.getElementById('student-qr-modal-{{ $student->id }}').showModal()" class="whitespace-nowrap font-medium text-purple-600 hover:underline">QR</button>
                                    @if ($student->user)
                                        <button type="button" onclick="document.getElementById('student-reset-password-modal-{{ $student->id }}').showModal()" class="whitespace-nowrap font-medium text-emerald-600 hover:underline">Reset password</button>
                                    @endif
                                    <button type="button" onclick="document.getElementById('student-status-modal-{{ $student->id }}').showModal()" class="whitespace-nowrap font-medium text-amber-600 hover:underline">
                                        {{ $student->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button type="button" onclick="document.getElementById('student-delete-modal-{{ $student->id }}').showModal()" class="whitespace-nowrap font-medium text-red-600 hover:underline">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-white">
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                                <x-ui.empty-state message="No student records found." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
            <div class="mt-4">{{ $students->links() }}</div>
        </x-ui.card>

        @foreach ($students as $student)
            <x-ui.student-qr-modal
                id="student-qr-modal-{{ $student->id }}"
                :student="$student"
                :data-uri="$student->qrCodeDataUri(240)" />

            <x-ui.confirm-modal
                id="student-status-modal-{{ $student->id }}"
                title="{{ $student->status === 'active' ? 'Deactivate' : 'Activate' }} {{ $student->full_name }}?"
                form-action="{{ route('admin.students.toggle-status', $student) }}"
                form-method="PATCH"
                confirm-label="{{ $student->status === 'active' ? 'Yes, deactivate' : 'Yes, activate' }}"
                warning="{{ $student->status === 'active' ? 'The student is skipped by the scanner until reactivated.' : 'The student becomes scannable again immediately.' }}">
                This changes the student's status right away.
            </x-ui.confirm-modal>

            <x-ui.confirm-modal
                id="student-delete-modal-{{ $student->id }}"
                title="Delete {{ $student->full_name }}?"
                form-action="{{ route('admin.students.destroy', $student) }}"
                form-method="DELETE"
                confirm-label="Yes, delete">
                This removes the student record{{ $student->user ? ' and their portal account' : '' }}, freeing the email address for a new registration.
                It only works if this student has no attendance history. Otherwise, deactivate them instead.
            </x-ui.confirm-modal>

            @if ($student->user)
                <x-ui.confirm-modal
                    id="student-reset-password-modal-{{ $student->id }}"
                    title="Send a password reset email"
                    form-action="{{ route('admin.students.reset-password', $student) }}"
                    form-method="PATCH"
                    confirm-label="Yes, send reset email"
                    warning="The student's current password stays active until they open the email and set a new one.">
                    A password reset email will be sent to <strong>{{ $student->user->email }}</strong>.
                </x-ui.confirm-modal>
            @endif
        @endforeach
    @elseif ($tab === 'guardians')
        <x-ui.card class="mb-6">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Guardian Records</h3>
            <x-ui.table :fit="true">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-700">
                    <tr>
                        <th scope="col" class="w-12 px-4 py-3 text-left font-semibold">#</th>
                        <x-ui.sortable-th column="name" label="Name" class="font-semibold" />
                        <x-ui.sortable-th column="contact" label="Contact" class="font-semibold" />
                        <x-ui.sortable-th column="relationship" label="Relationship" class="font-semibold" />
                        <x-ui.sortable-th column="status" label="Status" class="font-semibold" />
                        <x-ui.sortable-th column="students" label="Students" class="font-semibold" />
                        <th scope="col" class="px-4 py-3 text-left font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($guardians as $guardian)
                        <tr class="bg-white transition-colors hover:bg-gray-50">
                            <td class="px-4 py-4 text-sm font-medium text-gray-500">{{ $guardians->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-4">
                                <span class="flex items-center gap-3">
                                    <x-ui.avatar :name="$guardian->first_name.' '.$guardian->last_name" size="xs" />
                                    <span class="whitespace-nowrap font-semibold text-gray-900">{{ $guardian->first_name }} {{ $guardian->last_name }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-500">
                                <div class="flex flex-wrap items-center gap-2">
                                    {{ $guardian->phone_number }}
                                    <x-ui.copy-button :value="$guardian->phone_number" what="phone number" :icon-only="true" />
                                </div>
                                @if ($guardian->email)
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                                        {{ $guardian->email }}
                                        <x-ui.copy-button :value="$guardian->email" what="email" :icon-only="true" />
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4"><x-ui.optional-value :value="$guardian->relationship_to_student" /></td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <x-ui.badge color="{{ $guardian->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($guardian->status) }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-4 font-medium text-gray-900">{{ $guardian->students_count }}</td>
                            <td class="px-4 py-4 text-sm">
                                <a href="{{ route('admin.guardians.show', $guardian) }}" class="whitespace-nowrap font-medium text-blue-600 hover:underline">View/Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-white">
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                <x-ui.empty-state message="No guardian records found." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
            <div class="mt-4">{{ $guardians->links() }}</div>
        </x-ui.card>
    @else
        <x-ui.card>
            <h3 class="mb-4 text-base font-semibold text-gray-900">{{ $tabs[$tab] }}</h3>
            @include('admin.users._accounts-table', [
                'accounts' => $users,
                'showSections' => $tab === 'teachers',
            ])
            <div class="mt-4">{{ $users->links() }}</div>
        </x-ui.card>
    @endif

    <x-ui.form-modal id="add-user-modal" title="Add User" action="{{ route('admin.users.store-account') }}" submit-label="Create">
        @include('admin.users._add_account_form', [
            'sections' => $sections,
            'defaultType' => old('account_type', $defaultAccountType),
        ])
    </x-ui.form-modal>

</x-layout>
