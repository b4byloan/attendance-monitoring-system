@props(['status' => 'not_yet'])

@php
    $color = match ($status) {
        'checked_out' => 'green',
        'checked_in' => 'blue',
        default => 'gray',
    };

    $label = match ($status) {
        'checked_out' => 'Checked Out',
        'checked_in' => 'Checked In',
        default => 'Not Yet',
    };
@endphp

<x-ui.badge :color="$color">{{ $label }}</x-ui.badge>
