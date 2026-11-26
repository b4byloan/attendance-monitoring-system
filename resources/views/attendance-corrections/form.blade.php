<x-layout title="Request Correction" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-3xl mx-auto">
        <x-ui.card>
            <h2 class="text-lg font-bold text-gray-900">{{ $attendanceRecord->student->full_name }}</h2>
            <p class="text-sm text-gray-500 mb-6">
                {{ $attendanceRecord->attendance_date->format('M j, Y') }} â€”
                current {{ $attendanceRecord->type === 'check_in' ? 'Check-In' : 'Check-Out' }}:
                <strong>{{ $attendanceRecord->scanned_at->format('h:i A') }}</strong>
            </p>

            <form method="POST" action="{{ route('attendance-corrections.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="attendance_record_id" value="{{ $attendanceRecord->id }}">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correct Check-In Time</label>
                        <x-ui.form-field icon="calendar" name="requested_check_in_time">
                            <input type="time" name="requested_check_in_time" value="{{ old('requested_check_in_time') }}"
                                   class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                        </x-ui.form-field>
                        @error('requested_check_in_time')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correct Check-Out Time</label>
                        <x-ui.form-field icon="calendar" name="requested_check_out_time">
                            <input type="time" name="requested_check_out_time" value="{{ old('requested_check_out_time') }}"
                                   class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                        </x-ui.form-field>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Leave either blank if it shouldn't change. Fill in check-out too if it's missing entirely and should exist.</p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Correction</label>
                    <x-ui.form-field icon="pencil" name="reason">
                        <textarea name="reason" rows="3"
                                  class="max-h-40 w-full resize-y overflow-y-auto border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">{{ old('reason') }}</textarea>
                    </x-ui.form-field>
                    @error('reason')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Submit Request</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('attendance-corrections.index') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layout>