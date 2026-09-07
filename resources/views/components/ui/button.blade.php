@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'button'])

@php
$base = 'inline-flex items-center justify-center gap-2 font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed';

$variants = [
    'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
    'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-gray-400',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'ghost' => 'text-gray-600 hover:bg-gray-100 focus:ring-gray-400',
];

$sizes = [
    'sm' => 'text-sm px-3 py-1.5',
    'md' => 'text-sm px-4 py-2',
    'lg' => 'text-base px-5 py-3',
];

$classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif