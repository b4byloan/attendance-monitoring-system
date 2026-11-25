<x-layout title="Users" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    @php
        $tabs = [
            'students' => 'Students',
            'guardians' => 'Guardians',
            'admin' => 'Admin',
            'teachers' => 'Teachers',
            'guard' => 'Guards',
        ];
    @endphp

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Users</h2>
            <p class="text-sm text-gray-500">All students, guardians, and school personnel in one place.</p>
            @if ($tab === 'teachers')
                <p class="mt-1 text-xs text-gray-500">Includes Teacher and Sub-Admin accounts. Role badges distinguish Teacher (green) from Sub-Admin (blue).</p>
            @endif
        </div>
        @if ($tab === 'students')
            <x-ui.button href="{{ route('admin.students.create') }}"><x-ui.icon name="plus" class="h-4 w-4" /> Add Student</x-ui.button>
        @elseif ($tab === 'guardians')
            <x-ui.button href="{{ route('admin.guardians.create') }}"><x-ui.icon name="plus" class="h-4 w-4" /> Add Guardian</x-ui.button>
        @else
            <x-ui.button href="{{ route('admin.users.create') }}"><x-ui.icon name="plus" class="h-4 w-4" /> Add User</x-ui.button>
        @endif
    </div>

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
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search by name, email, LRN, or phone"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            @if ($tab === 'students')
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Section</label>
                    <select name="section" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Sections</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}" @selected((int) request('section') === $section->id)>{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-xs text-gray-500 mb-1">Role</label>
                <select name="role" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Roles</option>
                    @foreach (\App\Models\User::roleOptions() as $value => $label)
                        <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
            @if ($search !== '' || $role !== '' || request('section'))
                <a href="{{ route('admin.users.index', ['tab' => $tab]) }}" class="text-sm text-gray-500 hover:underline pb-2">Clear</a>
            @endif
        </form>
    </x-ui.card>

    @if ($tab === 'students')
        <x-ui.card class="mb-6">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Student Records</h3>
            <x-ui.table>
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <x-ui.sortable-th column="lrn" label="LRN" />
                        <x-ui.sortable-th column="name" label="Name" />
                        <x-ui.sortable-th column="section" label="Section" />
                        <x-ui.sortable-th column="email" label="Email" />
                        <x-ui.sortable-th column="status" label="Status" />
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($students as $student)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $students->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3">
                                <span class="flex items-center gap-2">
                                    <span class="font-mono text-xs">{{ $student->lrn }}</span>
                                    <x-ui.copy-button :value="$student->lrn" />
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $student->full_name }}</td>
                            <td class="px-4 py-3">{{ $student->section->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($student->user?->email)
                                    {{ $student->user->email }}
                                @else
                                    <span class="text-xs text-gray-400">Not registered</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 space-x-2 text-sm whitespace-nowrap">
                                <a href="{{ route('admin.students.show', $student) }}" class="text-indigo-600 hover:underline">View/Edit</a>
                                <button type="button" onclick="document.getElementById('student-qr-modal-{{ $student->id }}').showModal()" class="text-purple-600 hover:underline">QR</button>
                                @if ($student->user)
                                    <button type="button" onclick="document.getElementById('student-reset-password-modal-{{ $student->id }}').showModal()" class="text-purple-600 hover:underline">Reset password</button>
                                @endif
                                <button type="button" onclick="document.getElementById('student-status-modal-{{ $student->id }}').showModal()" class="text-amber-600 hover:underline">
                                    {{ $student->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button type="button" onclick="document.getElementById('student-delete-modal-{{ $student->id }}').showModal()" class="text-red-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state message="No student records found.">
                                    <x-slot:action>
                                        <x-ui.button href="{{ route('admin.students.create') }}" size="sm">+ Add Student</x-ui.button>
                                    </x-slot:action>
                                </x-ui.empty-state>
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
                This only works if this student has no attendance history. Otherwise, deactivate them instead.
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
            <x-ui.table>
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <x-ui.sortable-th column="name" label="Name" />
                        <x-ui.sortable-th column="contact" label="Contact" />
                        <x-ui.sortable-th column="relationship" label="Relationship" />
                        <x-ui.sortable-th column="status" label="Status" />
                        <x-ui.sortable-th column="students" label="Students" />
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($guardians as $guardian)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $guardians->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium">{{ $guardian->first_name }} {{ $guardian->last_name }}</td>
                            <td class="px-4 py-3">
                                <div>{{ $guardian->phone_number }}</div>
                                @if ($guardian->email)<div class="text-xs text-gray-400">{{ $guardian->email }}</div>@endif
                            </td>
                            <td class="px-4 py-3">{{ $guardian->relationship_to_student ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge color="{{ $guardian->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($guardian->status) }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3">{{ $guardian->students_count }}</td>
                            <td class="px-4 py-3 space-x-2 text-sm whitespace-nowrap">
                                <a href="{{ route('admin.guardians.show', $guardian) }}" class="text-indigo-600 hover:underline">View/Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state message="No guardian records found.">
                                    <x-slot:action>
                                        <x-ui.button href="{{ route('admin.guardians.create') }}" size="sm">+ Add Guardian</x-ui.button>
                                    </x-slot:action>
                                </x-ui.empty-state>
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

</x-layout>
