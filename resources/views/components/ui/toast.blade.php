@props(['type' => 'info', 'message' => null, 'duration' => null])

@php
$styles = [
    'success' => ['border' => 'border-green-200', 'icon' => 'bg-green-100 text-green-700', 'label' => 'text-green-700'],
    'error' => ['border' => 'border-red-200', 'icon' => 'bg-red-100 text-red-700', 'label' => 'text-red-700'],
    'warning' => ['border' => 'border-amber-200', 'icon' => 'bg-amber-100 text-amber-700', 'label' => 'text-amber-700'],
    'info' => ['border' => 'border-blue-200', 'icon' => 'bg-blue-100 text-blue-700', 'label' => 'text-blue-700'],
];
$icons = ['success' => 'circle-check', 'error' => 'x', 'warning' => 'alert-triangle', 'info' => 'info'];
$style = $styles[$type] ?? $styles['info'];
@endphp

<div data-toast
     data-toast-type="{{ $type }}"
     @if ($duration) data-toast-duration="{{ $duration }}" @endif
     role="{{ $type === 'error' ? 'alert' : 'status' }}"
     {{ $attributes->merge(['class' => 'pointer-events-auto flex w-full items-start gap-3 rounded-xl border bg-white px-4 py-3 shadow-lg ring-1 ring-black/5 ' . $style['border']]) }}>
    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $style['icon'] }}">
        <x-ui.icon :name="$icons[$type] ?? $icons['info']" class="h-4 w-4" />
    </span>

    <div class="min-w-0 flex-1">
        <p class="text-xs font-semibold uppercase tracking-wide {{ $style['label'] }}">{{ ucfirst($type) }}</p>
        <div data-toast-message class="mt-0.5 break-words text-sm text-gray-700">
            @if (is_array($message))
                <ul class="list-disc space-y-0.5 pl-4">
                    @foreach ($message as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            @else
                {{ $message }}
            @endif
        </div>
    </div>

    <button type="button" data-toast-close aria-label="Dismiss notification"
            class="-m-1 shrink-0 rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <x-ui.icon name="x" class="h-4 w-4" />
    </button>
</div>
