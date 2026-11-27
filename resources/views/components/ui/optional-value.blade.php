@props([
    'value' => null,
    'fallback' => 'N/A',
])

@php
    $isMissing = blank($value);
@endphp

<span
    @class(['text-slate-400 italic' => $isMissing])
    @style(['color: #94a3b8; font-style: italic' => $isMissing])
>{{ $isMissing ? $fallback : $value }}</span>
