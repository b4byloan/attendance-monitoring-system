<x-layout title="Attendance Corrections" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">
            {{ $canReview ? 'Attendance Correction Requests' : 'My Correction Requests' }}
        </h2>
        <x-ui.button href="{{ route('attendance-corrections.create') }}">+ New Request</x-ui.button>
    </div>

    @if ($canReview)
        <div class="flex gap-2 mb-4">
            <a href="{{ route('attendance-corrections.index') }}" class="px-3 py-1.5 rounded-lg text-sm {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700' }}">All</a>
            <a href="{{ route('attendance-corrections.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-sm {{ request('status') === 'pending' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700' }}">
                Pending @if($pendingCount) ({{ $pendingCount }}) @endif
            </a>
            <a href="{{ route('attendance-corrections.index', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded-lg text-sm {{ request('status') === 'approved' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700' }}">Approved</a>
            <a href="{{ route('attendance-corrections.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded-lg text-sm {{ request('status') === 'rejected' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700' }}">Rejected</a>
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($corrections as $c)
            @php
                $statusColor = match($c->status) { 'pending' => 'amber', 'approved' => 'green', default => 'red' };
                $isOwnRequest = $c->requested_by === auth()->id();
            @endphp
            <x-ui.card class="{{ $c->status === 'pending' ? 'border-l-4 border-l-amber-400' : '' }}">
                <div class="flex flex-wrap justify-between items-start gap-2 mb-3">
                    <div>
                        <h3 class="font-bold text-gray-900">{{ $c->student->full_name }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Record date: {{ $c->attendanceRecord->attendance_date->format('M j, Y') }}
                            @if ($canReview) · Requested by {{ $c->requestedBy->name ?? 'Unknown' }} @endif
                            · {{ $c->created_at->format('M j, Y h:i A') }}
                        </p>
                    </div>
                    <x-ui.badge color="{{ $statusColor }}">{{ ucfirst($c->status) }}</x-ui.badge>
                </div>

                <div class="bg-gray-50 rounded-lg p-3 text-sm mb-3">
                    @if ($c->requested_check_in_time)
                        <p>Requested Check-In: <strong>{{ \Carbon\Carbon::parse($c->requested_check_in_time)->format('h:i A') }}</strong></p>
                    @endif
                    @if ($c->requested_check_out_time)
                        <p>Requested Check-Out: <strong>{{ \Carbon\Carbon::parse($c->requested_check_out_time)->format('h:i A') }}</strong></p>
                    @endif
                </div>

                <p class="text-sm text-gray-700"><span class="text-gray-500">Reason:</span> {{ $c->reason }}</p>

                @if ($c->status === 'rejected' && $c->admin_remarks)
                    <div class="mt-2 bg-red-50 text-red-800 text-sm rounded-lg p-3">Admin remarks: {{ $c->admin_remarks }}</div>
                @endif
                @if ($c->status === 'approved')
                    <p class="text-xs text-gray-400 mt-2">Reviewed by {{ $c->reviewedBy->name ?? 'Unknown' }} on {{ $c->reviewed_at?->format('M j, Y h:i A') }}</p>
                @endif

                @if ($canReview && $c->status === 'pending')
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        @if ($isOwnRequest)
                            <p class="text-xs text-gray-400">You submitted this request — another admin or sub-admin needs to review it.</p>
                        @else
                            <div class="flex gap-3">
                                <button type="button" onclick="document.getElementById('approve-modal-{{ $c->id }}').showModal()"
                                        class="px-4 py-2 text-sm rounded-lg bg-green-600 text-white hover:bg-green-700">Approve</button>
                                <button type="button" onclick="document.getElementById('reject-modal-{{ $c->id }}').showModal()"
                                        class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
                            </div>
                        @endif
                    </div>
                @endif
            </x-ui.card>
        @empty
            <x-ui.card>
                <x-ui.empty-state message="No correction requests found." />
            </x-ui.card>
        @endforelse
    </div>

    <div class="mt-4">{{ $corrections->links() }}</div>

    @foreach ($corrections as $c)
        @if ($canReview && $c->status === 'pending' && $c->requested_by !== auth()->id())
            <x-ui.confirm-modal
                id="approve-modal-{{ $c->id }}"
                title="Approve this correction?"
                form-action="{{ route('attendance-corrections.approve', $c) }}"
                form-method="PATCH"
                confirm-label="Yes, approve">
                This updates {{ $c->student->full_name }}'s attendance record immediately, using the times requested above.
            </x-ui.confirm-modal>

            <dialog id="reject-modal-{{ $c->id }}" class="rounded-lg shadow-xl p-0 w-full max-w-sm backdrop:bg-black/40">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Reject this correction?</h3>
                    <p class="text-sm text-gray-600 mb-4">The original attendance record stays exactly as it is — nothing changes if you reject.</p>
                    <form method="POST" action="{{ route('attendance-corrections.reject', $c) }}">
                        @csrf
                        @method('PATCH')
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason for rejecting</label>
                        <textarea name="admin_remarks" rows="3" required
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="document.getElementById('reject-modal-{{ $c->id }}').close()"
                                    class="px-4 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
                        </div>
                    </form>
                </div>
            </dialog>
        @endif
    @endforeach

</x-layout>