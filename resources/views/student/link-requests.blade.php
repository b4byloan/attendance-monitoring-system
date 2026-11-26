<x-simple-layout title="Guardian Link Requests" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <x-ui.back-button :href="route('student.dashboard')" label="Back to dashboard" class="mb-4" />

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Guardian Link Requests</h2>
        <p class="text-sm text-gray-500">Guardians who want to connect to your student account. Accept or deny each request.</p>
    </div>

    <x-ui.card>
        @if ($requests->isNotEmpty())
            <div class="mb-3 flex justify-end">
                <button type="button" onclick="document.getElementById('delete-all-student-link-requests').showModal()" class="text-sm text-red-600 hover:underline">
                    Delete All
                </button>
            </div>
            <x-ui.confirm-modal
                id="delete-all-student-link-requests"
                title="Delete all guardian link requests?"
                :form-action="route('student.link-requests.destroy-all')"
                form-method="DELETE"
                confirm-label="Yes, permanently delete all"
                warning="This action will permanently delete all guardian link request records. Accepted guardian links will remain active.">
                All guardian link request records in your history will be removed.
            </x-ui.confirm-modal>
        @endif

        @forelse ($requests as $linkRequest)
            <div class="border-b border-gray-100 py-4 text-sm last:border-0">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="font-medium text-gray-900">
                            {{ $linkRequest->guardian->first_name }} {{ $linkRequest->guardian->last_name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $linkRequest->guardian->relationship_to_student ?? 'Guardian' }}
                            · {{ $linkRequest->guardian->phone_number }}
                            <x-ui.copy-button :value="$linkRequest->guardian->phone_number" what="phone number" :icon-only="true" class="ml-1 align-middle" />
                            @if ($linkRequest->guardian->email)
                                · {{ $linkRequest->guardian->email }}
                                <x-ui.copy-button :value="$linkRequest->guardian->email" what="email" :icon-only="true" class="ml-1 align-middle" />
                            @endif
                        </p>
                        <p class="mt-1 text-[11px] text-gray-400">Requested {{ $linkRequest->created_at->format('M j, Y h:i A') }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-ui.badge color="{{ match ($linkRequest->status) { 'accepted' => 'green', 'denied' => 'red', default => 'amber' } }}">
                            {{ ucfirst($linkRequest->status) }}
                        </x-ui.badge>
                        <button type="button" onclick="document.getElementById('delete-student-link-request-{{ $linkRequest->id }}').showModal()" class="text-red-600 hover:underline">
                            Delete
                        </button>
                    </div>
                </div>

                @if ($linkRequest->isPending())
                    <div class="mt-3 flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('student.link-requests.accept', $linkRequest) }}">
                            @csrf
                            <x-ui.button type="submit" size="sm">Accept</x-ui.button>
                        </form>
                        <form method="POST" action="{{ route('student.link-requests.deny', $linkRequest) }}">
                            @csrf
                            <x-ui.button type="submit" variant="secondary" size="sm">Deny</x-ui.button>
                        </form>
                    </div>
                @elseif ($linkRequest->responded_at)
                    <p class="mt-2 text-xs text-gray-400">Answered {{ $linkRequest->responded_at->format('M j, Y h:i A') }}</p>
                @endif
            </div>
            <x-ui.confirm-modal
                id="delete-student-link-request-{{ $linkRequest->id }}"
                title="Delete this guardian link request?"
                :form-action="route('student.link-requests.destroy', $linkRequest)"
                form-method="DELETE"
                confirm-label="Yes, permanently delete"
                warning="This action will permanently delete the guardian link request. If it was accepted, deleting the request will not unlink the guardian from your account.">
                This request will be permanently removed from your link request history.
            </x-ui.confirm-modal>
        @empty
            <x-ui.empty-state message="No guardian link requests yet." />
        @endforelse
    </x-ui.card>

</x-simple-layout>
