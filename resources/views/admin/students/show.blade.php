<x-layout title="Student View/Edit" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    @php
        $tabs = ['information' => 'Student Information', 'attendance' => 'Attendance', 'guardians' => 'Guardians'];
        $activeTab = array_key_exists($activeTab, $tabs) ? $activeTab : 'information';
    @endphp

    <div class="max-w-3xl mx-auto">
        <x-ui.back-button :href="route('admin.users.index', ['tab' => 'students'])" label="Back to users" class="mb-4" />

        <x-ui.card class="mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if ($student->photo_path)
                        <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-16 h-16 rounded-full object-cover" alt="">
                    @else
                        <div class="w-16 h-16 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold">
                            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $student->full_name }}</h2>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <p class="text-sm text-gray-500">LRN: <span class="font-mono text-gray-700">{{ $student->lrn }}</span></p>
                            <x-ui.copy-button :value="$student->lrn" />
                        </div>
                    </div>
                </div>
                <x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge>
            </div>
        </x-ui.card>

        <div class="mb-6 overflow-x-auto border-b border-gray-200">
            <nav class="flex min-w-max gap-1" aria-label="Student information sections">
                @foreach ($tabs as $value => $label)
                    <a href="{{ route('admin.students.show', ['student' => $student, 'tab' => $value]) }}"
                       @class([
                           'border-b-2 px-4 py-2.5 text-sm font-medium transition',
                           'border-emerald-600 text-emerald-700' => $activeTab === $value,
                           'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => $activeTab !== $value,
                       ])>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>

        @if ($activeTab === 'information')
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-ui.card title="Student Information">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Section</dt><dd class="font-medium">{{ $student->section->name ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Registered Email</dt><dd class="font-medium">{{ $student->user->email ?? 'Not registered yet' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Grade Level</dt><dd class="font-medium">{{ $student->section->grade_level ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Current Teacher</dt><dd class="font-medium">{{ $student->section->adviser->name ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Gate Records</dt><dd class="font-medium">{{ $attendanceCount }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Subject Records</dt><dd class="font-medium">{{ $subjectRecords->total() }}</dd></div>
                    </dl>
                </x-ui.card>

                <x-ui.card title="QR Code">
                    <p class="text-sm text-gray-500 mb-3">Used to check this student in and out at the gate and in class.</p>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button type="button" variant="secondary" onclick="document.getElementById('student-qr-modal').showModal()">View QR Code</x-ui.button>
                        <x-ui.button href="{{ route('admin.students.qr-code.download', $student) }}" variant="secondary">Download QR</x-ui.button>
                    </div>
                </x-ui.card>

                <x-ui.card title="Edit Student" class="sm:col-span-2">
                    <form method="POST" action="{{ route('admin.students.update', $student) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">LRN (12 digits)</label>
                                <input type="text" name="lrn" required inputmode="numeric" pattern="\d{12}" maxlength="12" minlength="12" title="Enter the 12-digit LRN" value="{{ old('lrn', $student->lrn) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('lrn') border-red-400 @enderror">
                                @error('lrn')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" name="first_name" required maxlength="255" value="{{ old('first_name', $student->first_name) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('first_name') border-red-400 @enderror">
                                @error('first_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                <input type="text" name="middle_name" maxlength="255" value="{{ old('middle_name', $student->middle_name) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" name="last_name" required maxlength="255" value="{{ old('last_name', $student->last_name) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('last_name') border-red-400 @enderror">
                                @error('last_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Suffix</label>
                                <input type="text" name="suffix" maxlength="20" value="{{ old('suffix', $student->suffix) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                                <select name="section_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('section_id') border-red-400 @enderror">
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}" @selected(old('section_id', $student->section_id) == $section->id)>{{ $section->name }}</option>
                                    @endforeach
                                </select>
                                @error('section_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @foreach (['active', 'inactive', 'transferred', 'graduated'] as $status)
                                        <option value="{{ $status }}" @selected(old('status', $student->status) === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <x-ui.button type="submit">Save Changes</x-ui.button>
                            <x-ui.button variant="secondary" href="{{ route('admin.students.show', $student) }}">Cancel</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        @elseif ($activeTab === 'attendance')
            <x-ui.card title="Subject Attendance" class="mb-6">
                <form method="GET" action="{{ route('admin.students.show', $student) }}" class="mb-4 flex flex-wrap items-end gap-3">
                    <input type="hidden" name="tab" value="attendance">
                    <div class="min-w-[200px] flex-1">
                        <label for="subject-filter" class="mb-1 block text-xs text-gray-500">Filter by subject</label>
                        <select id="subject-filter" name="subject" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">All subjects</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected($selectedSubjectId === $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-ui.button type="submit" variant="secondary">Apply Filter</x-ui.button>
                </form>

                <div class="overflow-x-auto">
                    <x-ui.table>
                        <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Subject</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Recorded Time</th>
                                <th class="px-4 py-3 text-left">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($subjectRecords as $record)
                                <tr>
                                    <td class="px-4 py-3">{{ $record->attendance_date->format('M j, Y') }}</td>
                                    <td class="px-4 py-3">{{ $record->subject->name ?? '—' }}</td>
                                    <td class="px-4 py-3"><x-ui.badge color="green">{{ ucfirst($record->status) }}</x-ui.badge></td>
                                    <td class="px-4 py-3">{{ $record->scanned_at->format('h:i A') }}</td>
                                    <td class="px-4 py-3">{{ $record->recordedBy->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><x-ui.empty-state message="No subject attendance recorded yet." /></td></tr>
                            @endforelse
                        </tbody>
                    </x-ui.table>
                </div>
                <div class="mt-4">{{ $subjectRecords->links() }}</div>
            </x-ui.card>

            <x-ui.card title="Gate Attendance">
                <div class="overflow-x-auto">
                    <x-ui.table>
                        <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-left">Time</th>
                                <th class="px-4 py-3 text-left">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($gateRecords as $record)
                                <tr>
                                    <td class="px-4 py-3">{{ $record->attendance_date->format('M j, Y') }}</td>
                                    <td class="px-4 py-3">
                                        <x-ui.badge color="{{ $record->type === 'check_in' ? 'green' : 'blue' }}">{{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}</x-ui.badge>
                                    </td>
                                    <td class="px-4 py-3">{{ $record->scanned_at->format('h:i A') }}</td>
                                    <td class="px-4 py-3">{{ $record->recordedBy->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><x-ui.empty-state message="No gate attendance recorded yet." /></td></tr>
                            @endforelse
                        </tbody>
                    </x-ui.table>
                </div>
                <div class="mt-4">{{ $gateRecords->links() }}</div>
            </x-ui.card>
        @elseif ($activeTab === 'guardians')
            <x-ui.card title="Guardians">
                @forelse ($student->guardians as $guardian)
                    <a href="{{ route('admin.guardians.show', $guardian) }}" class="block py-2 border-b border-gray-100 text-sm last:border-0 hover:text-indigo-600">
                        {{ $guardian->first_name }} {{ $guardian->last_name }}
                        <span class="text-gray-400">— {{ $guardian->relationship_to_student ?? 'Guardian' }}</span>
                    </a>
                @empty
                    <x-ui.empty-state message="No guardians linked yet." />
                @endforelse
            </x-ui.card>
        @endif

    </div>

    <x-ui.student-qr-modal
        id="student-qr-modal"
        :student="$student"
        :data-uri="$student->qrCodeDataUri()" />

</x-layout>
