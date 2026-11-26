@props([
    'id',
    'student',
    'dataUri',
])

<dialog id="{{ $id }}"
        data-qr-name="{{ $student->full_name }}"
        data-qr-lrn="{{ $student->lrn }}"
        {{ $attributes->merge(['class' => 'w-full max-w-sm rounded-xl p-0 shadow-xl backdrop:bg-black/40']) }}>
    <div class="relative p-6">
        <button type="button" onclick="document.getElementById('{{ $id }}').close()" aria-label="Close QR code"
                class="absolute left-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <x-ui.icon name="x" class="h-4 w-4" />
        </button>

        <div class="mb-4 flex items-start justify-between gap-3 pl-8">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $student->full_name }}</h3>
                <p class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                    LRN: <span class="font-mono text-xs text-gray-700">{{ $student->lrn }}</span>
                    <x-ui.copy-button :value="$student->lrn" what="LRN" />
                </p>
            </div>
        </div>

        <div class="mb-4 flex justify-center rounded-lg border border-gray-200 bg-gray-50 p-3">
            <img data-qr-image src="{{ $dataUri }}" alt="QR code for {{ $student->full_name }}" class="h-56 w-56">
        </div>

        <dl class="mb-5 space-y-2 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Section</dt>
                <dd class="font-medium text-gray-900">{{ $student->section->name ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Grade Level</dt>
                <dd class="font-medium text-gray-900">{{ $student->section->grade_level ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Status</dt>
                <dd><x-ui.badge color="{{ $student->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($student->status) }}</x-ui.badge></dd>
            </div>
        </dl>

        <div class="flex flex-wrap justify-end gap-2">
            <x-ui.button href="{{ route('admin.students.qr-code.download', $student) }}" size="sm">
                <x-ui.icon name="download" class="h-4 w-4" /> Download
            </x-ui.button>
            <x-ui.button type="button" variant="secondary" size="sm" onclick="window.printStudentQrCode('{{ $id }}')">Print</x-ui.button>
            <x-ui.button type="button" variant="secondary" size="sm" onclick="document.getElementById('{{ $id }}').close()">Close</x-ui.button>
        </div>
    </div>
</dialog>

@once
    <script>
        window.printStudentQrCode = function (dialogId) {
            const dialog = document.getElementById(dialogId);
            const image = dialog ? dialog.querySelector('[data-qr-image]') : null;

            if (!dialog || !image) {
                return;
            }

            const printWindow = window.open('', '_blank', 'width=420,height=620');

            if (!printWindow) {
                return;
            }

            const printDocument = printWindow.document;
            printDocument.title = 'Student QR Code';
            printDocument.body.style.cssText = 'margin:0;padding:32px;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:ui-sans-serif,system-ui,sans-serif;';

            const card = printDocument.createElement('div');
            card.style.textAlign = 'center';

            const heading = printDocument.createElement('h1');
            heading.style.cssText = 'margin:0;font-size:20px;';
            heading.textContent = dialog.dataset.qrName || '';

            const lrn = printDocument.createElement('p');
            lrn.style.cssText = 'margin:4px 0 16px;color:#6b7280;font-size:14px;';
            lrn.textContent = 'LRN: ' + (dialog.dataset.qrLrn || '');

            const qr = printDocument.createElement('img');
            qr.src = image.src;
            qr.alt = heading.textContent + ' QR code';
            qr.style.cssText = 'width:260px;height:260px;';

            card.append(heading, lrn, qr);
            printDocument.body.append(card);

            printWindow.focus();
            printWindow.print();
        };
    </script>
@endonce
