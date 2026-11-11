@props([
    'id',
    'title' => 'Are you sure?',
    'formAction' => null,
    'formMethod' => 'POST',
    'formId' => null,
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
    'confirmPhrase' => null,
    'warning' => 'This action cannot be undone.',
])

<dialog id="{{ $id }}" {{ $attributes->merge(['class' => 'w-full max-w-md rounded-lg p-0 shadow-xl backdrop:bg-black/40']) }}>
    <div class="p-6">
        <h3 class="mb-2 text-lg font-semibold text-gray-900">{{ $title }}</h3>
        <div class="mb-4 text-sm text-gray-600">{{ $slot }}</div>

        <p class="mb-4 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-800">
            <x-ui.icon name="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>{{ $warning }}</span>
        </p>

        @if ($formAction)
            <form method="POST" action="{{ $formAction }}">
                @csrf
                @if (strtoupper($formMethod) !== 'POST')
                    @method($formMethod)
                @endif
        @endif

        @isset($fields)
            <div class="mb-4 space-y-3">{{ $fields }}</div>
        @endisset

        @if ($confirmPhrase)
            <div class="mb-4">
                <label for="{{ $id }}-confirmation" class="mb-1 block text-sm font-medium text-gray-700">
                    Type <strong>{{ $confirmPhrase }}</strong> to confirm
                </label>
                <input type="text" id="{{ $id }}-confirmation" name="confirmation" required
                       pattern="{{ preg_quote($confirmPhrase, '/') }}" placeholder="{{ $confirmPhrase }}"
                       class="w-full rounded-lg border border-red-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
        @endif

        <div class="flex justify-end gap-2">
            <x-ui.button type="button" variant="secondary" onclick="document.getElementById('{{ $id }}').close()">
                {{ $cancelLabel }}
            </x-ui.button>
            @if ($formId)
                <x-ui.button type="submit" variant="danger" id="{{ $id }}-confirm" form="{{ $formId }}">{{ $confirmLabel }}</x-ui.button>
            @else
                <x-ui.button type="submit" variant="danger">{{ $confirmLabel }}</x-ui.button>
            @endif
        </div>

        @if ($formAction)
            </form>
        @endif
    </div>
</dialog>
