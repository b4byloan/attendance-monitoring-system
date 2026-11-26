@props([
    'name' => '',
    'photo' => null,
    'size' => 'md',
])

@php
    $sizes = [
        'xs' => 'h-8 w-8 text-xs',
        'sm' => 'h-9 w-9 text-sm',
        'md' => 'h-12 w-12 text-base',
        'lg' => 'h-16 w-16 text-xl',
        'xl' => 'h-20 w-20 text-2xl',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $initials = collect(preg_split('/\s+/', trim((string) $name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
        ->implode('');
@endphp

@if ($photo)
    <img src="{{ asset('storage/'.$photo) }}" alt="{{ $name }}"
         {{ $attributes->merge(['class' => "shrink-0 rounded-full object-cover ring-1 ring-gray-200 $sizeClass"]) }}>
@else
    <span role="img" aria-label="{{ $name }}"
          {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center rounded-full bg-emerald-700 font-semibold text-white ring-1 ring-emerald-800/10 $sizeClass"]) }}>
        {{ $initials !== '' ? $initials : '?' }}
    </span>
@endif
