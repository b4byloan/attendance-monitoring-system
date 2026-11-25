@props(['column', 'label'])

@php
    $currentSort = (string) request('sort');
    $currentDirection = strtolower((string) request('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
    $isActive = $currentSort === $column;

    $query = collect(request()->query())
        ->reject(fn ($value, $key) => $key === 'page' || str_ends_with((string) $key, '_page'))
        ->all();

    $query['sort'] = $column;
    $query['direction'] = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
@endphp

<th {{ $attributes->merge(['class' => 'px-4 py-3 text-left']) }}
    aria-sort="{{ $isActive ? ($currentDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    <a href="{{ request()->url().'?'.http_build_query($query) }}"
       class="inline-flex items-center gap-1 transition hover:text-gray-900 {{ $isActive ? 'text-gray-900' : '' }}">
        {{ $label }}
        @if ($isActive)
            <span aria-hidden="true">{{ $currentDirection === 'asc' ? '↑' : '↓' }}</span>
        @else
            <span aria-hidden="true" class="text-gray-300">↕</span>
        @endif
    </a>
</th>
