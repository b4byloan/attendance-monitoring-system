<x-layout
    title="Student Details"
    :unread-count="$unreadCount ?? 0"
    :recent-notifications="$recentNotifications ?? collect()"
>

    @php
        $tabs = ['information' => 'Student Information', 'attendance' => 'Attendance', 'guardians' => 'Guardians'];
        $activeTab = array_key_exists($activeTab, $tabs) ? $activeTab : 'information';
    @endphp

    <div class="mx-auto max-w-3xl">
        <x-ui.back-button :href="route('sub-admin.sections.students', $student->section)" :label="'Back to '.$student->section->name" class="mb-4" />

        <x-ui.card class="mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <x-ui.avatar :name="$student->full_name" :photo="$student->photo_path" size="lg" />
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $student->full_name }}</h2>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <p class="text-sm text-gray-500">LRN: <span class="font-mono text-gray-700">{{ $student->lrn }}</span></p>
                            <x-ui.copy-button :value="$student->lrn" />
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge color="green">Read-only</x-ui.badge>
                    <x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge>
                </div>
            </div>
        </x-ui.card>

        <div class="mb-6 overflow-x-auto border-b border-gray-200">
            <nav class="flex min-w-max gap-1" aria-label="Student information sections">
                @foreach ($tabs as $value => $label)
                    <a href="{{ route('sub-admin.students.show', ['student' => $student, 'tab' => $value]) }}"
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
            <div class="mb-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <x-ui.card title="Student Information">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Section</dt><dd class="font-medium">{{ $student->section->name ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Grade Level</dt><dd class="font-medium">{{ $student->section->grade_level ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Current Teacher</dt><dd class="font-medium">{{ $student->section->adviser->name ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">School Year</dt><dd class="font-medium">{{ $student->section->school_year ?? '—' }}</dd></div>
                    </dl>
                </x-ui.card>

                <x-ui.card title="Today's Gate Attendance">
                    <x-ui.attendance-status :status="$todayStatus" />
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-xs uppercase text-gray-500">Check-In</p>
                            <p class="mt-1 text-lg font-semibold">{{ $todayCheckIn ? $todayCheckIn->scanned_at->format('h:i A') : '—' }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-xs uppercase text-gray-500">Check-Out</p>
                            <p class="mt-1 text-lg font-semibold">{{ $todayCheckOut ? $todayCheckOut->scanned_at->format('h:i A') : '—' }}</p>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        @elseif ($activeTab === 'attendance')
            <x-ui.card title="Subject Attendance" class="mb-6">
                @if ($subjects->isEmpty())
                    <x-ui.empty-state message="No subjects are assigned to you yet. Subject attendance will appear here once your administrator assigns one." />
                @else
                    <form method="GET" action="{{ route('sub-admin.students.show', $student) }}" class="mb-4 flex flex-wrap items-end gap-3">
                        <input type="hidden" name="tab" value="attendance">
                        <div class="min-w-[200px] flex-1">
                            <label for="subject-filter" class="mb-1 block text-xs text-gray-500">Filter by subject</label>
                            <select id="subject-filter" name="subject" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">All my subjects</option>
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
                                    <th class="px-4 py-3 text-left">#</th>
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
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $subjectRecords->firstItem() + $loop->index }}</td>
                                        <td class="px-4 py-3">{{ $record->attendance_date->format('M j, Y') }}</td>
                                        <td class="px-4 py-3"><x-ui.optional-value :value="$record->subject->name ?? null" /></td>
                                        <td class="px-4 py-3"><x-ui.badge color="green">{{ ucfirst($record->status) }}</x-ui.badge></td>
                                        <td class="px-4 py-3">{{ $record->scanned_at->format('h:i A') }}</td>
                                        <td class="px-4 py-3"><x-ui.optional-value :value="$record->recordedBy->name ?? null" /></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><x-ui.empty-state message="No subject attendance recorded yet." /></td></tr>
                                @endforelse
                            </tbody>
                        </x-ui.table>
                    </div>
                    <div class="mt-4">{{ $subjectRecords->links() }}</div>
                @endif
            </x-ui.card>

            <x-ui.card title="Recent Gate Attendance">
                <x-ui.table :fit="true">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Date</th>
                            <th scope="col">Type</th>
                            <th scope="col">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentRecords as $record)
                            <tr>
                                <td class="font-medium text-gray-500">{{ $loop->iteration }}</td>
                                <td class="whitespace-nowrap">{{ $record->attendance_date->format('M j, Y') }}</td>
                                <td class="whitespace-nowrap">{{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}</td>
                                <td class="whitespace-nowrap">{{ $record->scanned_at->format('h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"><x-ui.empty-state message="No gate attendance recorded yet." /></td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </x-ui.card>
        @else
            <x-ui.card title="Guardians">
                @forelse ($student->guardians as $guardian)
                    <div class="border-b border-gray-100 py-2 text-sm last:border-0">
                        <p class="font-medium text-gray-900">{{ $guardian->first_name }} {{ $guardian->last_name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $guardian->relationship_to_student ?? 'Guardian' }}
                            @if ($guardian->phone_number)
                                · {{ $guardian->phone_number }}
                                <x-ui.copy-button :value="$guardian->phone_number" what="phone number" :icon-only="true" class="ml-1 align-middle" />
                            @endif
                        </p>
                    </div>
                @empty
                    <x-ui.empty-state message="No guardians linked yet." />
                @endforelse
            </x-ui.card>
        @endif
    </div>

</x-layout>
