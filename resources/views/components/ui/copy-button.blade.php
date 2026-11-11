@props(['value', 'label' => 'Copy'])

<button type="button"
        data-copy-button
        data-copy-value="{{ $value }}"
        aria-label="Copy LRN {{ $value }}"
        {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center gap-1.5 rounded-md border border-gray-200 bg-white px-2 py-1 text-xs font-medium text-gray-500 transition hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1']) }}>
    <x-ui.icon name="copy" class="h-3.5 w-3.5" />
    <span data-copy-text aria-live="polite">{{ $label }}</span>
</button>
