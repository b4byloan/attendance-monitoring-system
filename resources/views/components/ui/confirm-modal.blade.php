@props(['id', 'title' => 'Are you sure?', 'formAction', 'formMethod' => 'POST', 'confirmLabel' => 'Confirm'])

<dialog id="{{ $id }}" class="rounded-lg shadow-xl p-0 w-full max-w-sm backdrop:bg-black/40">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $title }}</h3>
        <div class="text-sm text-gray-600 mb-6">{{ $slot }}</div>
        <form method="POST" action="{{ $formAction }}">
            @csrf
            @if (strtoupper($formMethod) !== 'POST')
                @method($formMethod)
            @endif
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('{{ $id }}').close()" class="px-4 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700">{{ $confirmLabel }}</button>
            </div>
        </form>
    </div>
</dialog>