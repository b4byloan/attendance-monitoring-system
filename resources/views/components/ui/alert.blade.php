@props(['type' => 'info'])

@php
$styles = [
    'success' => 'bg-green-50 text-green-800 border-green-200',
    'error' => 'bg-red-50 text-red-800 border-red-200',
    'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
    'info' => 'bg-blue-50 text-blue-800 border-blue-200',
];
$icons = ['success' => '✓', 'error' => '✕', 'warning' => '⚠', 'info' => 'ℹ'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-2 border rounded-lg px-4 py-3 text-sm mb-4 ' . ($styles[$type] ?? $styles['info'])]) }}>
    <span class="font-bold">{{ $icons[$type] ?? $icons['info'] }}</span>
    <div>{{ $slot }}</div>
</div>