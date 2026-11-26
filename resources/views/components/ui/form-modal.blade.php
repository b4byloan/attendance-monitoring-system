@props([
    'id',
    'title',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save',
    'maxWidth' => 'max-w-3xl',
])

<dialog id="{{ $id }}" class="w-full {{ $maxWidth }} rounded-lg p-0 shadow-xl backdrop:bg-black/40">
    <div class="relative max-h-[90vh] overflow-y-auto p-6">
        <button type="button" onclick="document.getElementById('{{ $id }}').close()" aria-label="Close dialog"
                class="absolute left-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <x-ui.icon name="x" class="h-4 w-4" />
        </button>

        <h3 class="mb-4 pl-8 text-lg font-semibold text-gray-900">{{ $title }}</h3>

        <form method="POST" action="{{ $action }}">
            @csrf
            @if (strtoupper($method) !== 'POST')
                @method($method)
            @endif

            <input type="hidden" name="_modal" value="{{ $id }}">
            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">

            <div class="space-y-4">
                {{ $slot }}
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <x-ui.button type="button" variant="secondary" onclick="document.getElementById('{{ $id }}').close()">Cancel</x-ui.button>
                <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
            </div>
        </form>
    </div>
</dialog>
