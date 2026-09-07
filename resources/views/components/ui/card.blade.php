@props(['title' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-gray-200 p-6']) }}>
    @if ($title)
        <h3 class="text-base font-semibold text-gray-900 mb-4">{{ $title }}</h3>
    @endif
    {{ $slot }}
</div>