@props([
    'teachers',
    'selected' => [],
    'name' => 'teacher_ids',
    'emptyMessage' => 'No Teacher accounts exist yet — create one in Users first.',
])

<div data-teacher-picker>
    @if ($teachers->isEmpty())
        <p class="text-xs text-gray-500">{{ $emptyMessage }}</p>
    @else
        <x-ui.form-field icon="search" class="mb-2">
            <input type="search" data-teacher-search placeholder="Search teachers by name"
                   autocomplete="off" aria-label="Search teachers"
                   class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
        </x-ui.form-field>

        <div class="max-h-64 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3">
            @foreach ($teachers as $teacher)
                <label data-teacher-option data-teacher-search="{{ \Illuminate\Support\Str::lower($teacher->name.' '.$teacher->email) }}"
                       class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="{{ $name }}[]" value="{{ $teacher->id }}"
                           @checked(in_array($teacher->id, (array) $selected)) class="h-4 w-4 shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="min-w-0 break-words">{{ $teacher->name }}</span>
                    <span class="ml-auto shrink-0 text-xs text-gray-400">{{ $teacher->roleLabel() }}</span>
                </label>
            @endforeach
        </div>
        <p data-teacher-empty class="mt-2 hidden text-xs text-gray-500">No teachers match your search.</p>
    @endif
</div>

@once
    <script>
        document.addEventListener('input', function (event) {
            const search = event.target.closest('[data-teacher-search]');

            if (! search) {
                return;
            }

            const picker = search.closest('[data-teacher-picker]');
            const term = search.value.trim().toLowerCase();
            const empty = picker.querySelector('[data-teacher-empty]');
            let visible = 0;

            picker.querySelectorAll('[data-teacher-option]').forEach(function (option) {
                const matches = option.dataset.teacherSearch.includes(term);

                option.hidden = ! matches;

                if (matches) {
                    visible++;
                }
            });

            if (empty) {
                empty.classList.toggle('hidden', visible > 0);
            }
        });

        // Searching should never submit the surrounding form (pressing Enter inside
        // a search field would otherwise POST the modal form and bounce the user back).
        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' || ! event.target.closest('[data-teacher-search]')) {
                return;
            }

            event.preventDefault();
        });
    </script>
@endonce
