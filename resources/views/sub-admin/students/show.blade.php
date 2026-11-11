<x-layout
    title="Student Details"
    :unread-count="$unreadCount ?? 0"
    :recent-notifications="$recentNotifications ?? collect()"
>

    <div class="mx-auto max-w-2xl">
        <x-ui.back-button :href="route('sub-admin.sections.students', $student->section)" :label="'Back to '.$student->section->name" class="mb-4" />

        <x-ui.card class="mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if ($student->photo_path)
                        <img src="{{ asset('storage/' . $student->photo_path) }}" class="h-16 w-16 rounded-full object-cover" alt="">
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-xl font-bold text-emerald-700">
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
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge color="green">Read-only</x-ui.badge>
                    <x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge>
                </div>
            </div>
        </x-ui.card>

        <div class="mb-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-ui.card title="Student Information">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Section</dt><dd class="font-medium">{{ $student->section->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Grade Level</dt><dd class="font-medium">{{ $student->section->grade_level ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Current Teacher</dt><dd class="font-medium">{{ $student->section->adviser->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">School Year</dt><dd class="font-medium">{{ $student->section->school_year ?? '—' }}</dd></div>
                </dl>
            </x-ui.card>

            <x-ui.card title="Today's Attendance">
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

        <x-ui.card title="Guardians" class="mb-6">
            @forelse ($student->guardians as $guardian)
                <div class="border-b border-gray-100 py-2 text-sm last:border-0">
                    <p class="font-medium text-gray-900">{{ $guardian->first_name }} {{ $guardian->last_name }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $guardian->relationship_to_student ?? 'Guardian' }}
                        @if ($guardian->phone_number)
                            · {{ $guardian->phone_number }}
                        @endif
                    </p>
                </div>
            @empty
                <x-ui.empty-state message="No guardians linked yet." />
            @endforelse
        </x-ui.card>

        <x-ui.card title="Recent Attendance">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-gray-500">
                        <tr>
                            <th class="py-2 pr-4">Date</th>
                            <th class="py-2 pr-4">Type</th>
                            <th class="py-2">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($recentRecords as $record)
                            <tr>
                                <td class="py-2 pr-4">{{ $record->attendance_date->format('M j, Y') }}</td>
                                <td class="py-2 pr-4">{{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}</td>
                                <td class="py-2">{{ $record->scanned_at->format('h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3"><x-ui.empty-state message="No attendance recorded yet." /></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

</x-layout>
