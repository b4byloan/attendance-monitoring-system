@props(['type' => 'info'])

@php
$styles = [
    'success' => 'bg-green-50 text-green-800 border-green-200',
    'error' => 'bg-red-50 text-red-800 border-red-200',
    'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
    'info' => 'bg-blue-50 text-blue-800 border-blue-200',
];
$icons = ['success' => 'circle-check', 'error' => 'x', 'warning' => 'alert-triangle', 'info' => 'info'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-2 border rounded-lg px-4 py-3 text-sm mb-4 ' . ($styles[$type] ?? $styles['info'])]) }}>
    <x-ui.icon :name="$icons[$type] ?? $icons['info']" class="mt-0.5 h-4 w-4 shrink-0" />
    <div>{{ $slot }}</div>
</div>
