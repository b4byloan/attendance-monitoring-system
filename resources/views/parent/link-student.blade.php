<x-simple-layout title="Link a Student" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <x-ui.back-button :href="route('parent.dashboard')" label="Back to dashboard" class="mb-4" />

    <x-ui.card title="Request Student Linking" class="mb-6">
        <p class="mb-4 text-sm text-gray-500">
            Enter your student's details exactly as they appear in the school record, including their middle name if they have one. The student will receive a
            notification and must approve the request before you can view their records.
        </p>

        <form id="link-student-form" method="POST" action="{{ route('parent.link-student.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="link_lrn" class="block text-sm font-medium text-gray-700 mb-1">LRN (12 digits)</label>
                    <input id="link_lrn" type="text" name="lrn" value="{{ old('lrn') }}" required inputmode="numeric"
                           pattern="\d{12}" maxlength="12" minlength="12" title="Enter the 12-digit LRN"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('lrn') border-red-400 @enderror">
                    @error('lrn')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="link_grade_level" class="block text-sm font-medium text-gray-700 mb-1">Grade level</label>
                    <select id="link_grade_level" name="grade_level" required data-grade-level-select
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Select grade level</option>
                        @foreach ($gradeLevels as $gradeLevel)
                            <option value="{{ $gradeLevel }}" @selected(old('grade_level') === $gradeLevel)>{{ $gradeLevel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="link_first_name" class="block text-sm font-medium text-gray-700 mb-1">First name</label>
                    <input id="link_first_name" type="text" name="first_name" value="{{ old('first_name') }}" required maxlength="255"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('first_name') border-red-400 @enderror">
                    @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="link_last_name" class="block text-sm font-medium text-gray-700 mb-1">Last name</label>
                    <input id="link_last_name" type="text" name="last_name" value="{{ old('last_name') }}" required maxlength="255"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('last_name') border-red-400 @enderror">
                    @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="link_middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle name</label>
                    <input id="link_middle_name" type="text" name="middle_name" value="{{ old('middle_name') }}" maxlength="255" autocomplete="additional-name"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('middle_name') border-red-400 @enderror">
                    @error('middle_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="link_section_id" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                    <select id="link_section_id" name="section_id" required data-section-select
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Select a section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}" data-grade-level="{{ $section->grade_level }}" @selected(old('section_id') == $section->id)>{{ $section->name }}</option>
                        @endforeach
                    </select>
                    @error('section_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <x-ui.button type="button" onclick="document.getElementById('link-request-confirmation').showModal()">Send Link Request</x-ui.button>
        </form>
    </x-ui.card>

    <dialog id="link-request-confirmation" class="w-full max-w-md rounded-lg p-0 shadow-xl backdrop:bg-black/40">
        <div class="p-6">
            <h3 class="mb-2 text-lg font-semibold text-gray-900">Confirm the student details</h3>
            <p class="mb-5 text-sm text-gray-600">
                Are you sure the information you entered is correct? Please double-check the LRN, name (including middle name), grade level, and section before sending.
            </p>
            <div class="flex justify-end gap-2">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('link-request-confirmation').close()">Review details</x-ui.button>
                <x-ui.button type="submit" form="link-student-form">Yes, send request</x-ui.button>
            </div>
        </div>
    </dialog>

    <x-ui.card title="Link Requests">
        @if ($requests->isNotEmpty())
            <div class="mb-3 flex justify-end">
                <button type="button" onclick="document.getElementById('delete-all-link-requests').showModal()" class="text-sm text-red-600 hover:underline">
                    Delete All
                </button>
            </div>
            <x-ui.confirm-modal
                id="delete-all-link-requests"
                title="Delete all link requests?"
                :form-action="route('parent.link-requests.destroy-all')"
                form-method="DELETE"
                confirm-label="Yes, permanently delete all"
                warning="This action will permanently delete all your link requests. Accepted guardian-student links will remain active.">
                All link request records in your history will be removed.
            </x-ui.confirm-modal>
        @endif

        @forelse ($requests as $linkRequest)
            <div class="flex flex-wrap items-start justify-between gap-2 border-b border-gray-100 py-3 text-sm last:border-0">
                <div>
                    <p class="font-medium text-gray-900">{{ $linkRequest->student->full_name }}</p>
                    <p class="text-xs text-gray-500">
                        LRN {{ $linkRequest->student->lrn }} · {{ $linkRequest->student->section->grade_level ?? '—' }} ·
                        {{ $linkRequest->student->section->name ?? 'No section' }}
                    </p>
                    <p class="mt-1 text-[11px] text-gray-400">Sent {{ $linkRequest->created_at->format('M j, Y h:i A') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-ui.badge color="{{ match ($linkRequest->status) { 'accepted' => 'green', 'denied' => 'red', default => 'amber' } }}">
                        {{ ucfirst($linkRequest->status) }}
                    </x-ui.badge>
                    <button type="button" onclick="document.getElementById('delete-link-request-{{ $linkRequest->id }}').showModal()" class="text-red-600 hover:underline">
                        Delete
                    </button>
                </div>
            </div>
            <x-ui.confirm-modal
                id="delete-link-request-{{ $linkRequest->id }}"
                title="Delete this link request?"
                :form-action="route('parent.link-requests.destroy', $linkRequest)"
                form-method="DELETE"
                confirm-label="Yes, permanently delete"
                warning="This action will permanently delete the linking request. If it was accepted, deleting the request will not unlink the guardian from the student.">
                This request will be permanently removed from your link request history.
            </x-ui.confirm-modal>
        @empty
            <x-ui.empty-state message="No link requests yet. Send one above to connect with your student." />
        @endforelse
    </x-ui.card>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const gradeLevelSelect = document.querySelector('[data-grade-level-select]');
                const sectionSelect = document.querySelector('[data-section-select]');

                if (!gradeLevelSelect || !sectionSelect) {
                    return;
                }

                const sectionOptions = Array.from(sectionSelect.options).filter(function (option) {
                    return option.value !== '';
                });

                function syncSections() {
                    const gradeLevel = gradeLevelSelect.value;
                    const selectedSection = sectionSelect.value;
                    let keepSelection = selectedSection === '';

                    sectionOptions.forEach(function (option) {
                        const matchesGrade = gradeLevel === '' || option.dataset.gradeLevel === gradeLevel;

                        option.hidden = !matchesGrade;
                        option.disabled = !matchesGrade;

                        if (matchesGrade && option.value === selectedSection) {
                            keepSelection = true;
                        }
                    });

                    if (!keepSelection) {
                        sectionSelect.value = '';
                    }
                }

                gradeLevelSelect.addEventListener('change', syncSections);
                syncSections();
            });
        </script>
    @endpush

</x-simple-layout>

