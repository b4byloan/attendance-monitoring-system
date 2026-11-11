@props(['items' => []])

@if (count($items) > 0)
    <nav aria-label="Breadcrumb" class="mb-6 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
        @foreach ($items as $item)
            @unless ($loop->first)
                <span class="text-gray-300" aria-hidden="true">|</span>
            @endunless

            @if ($item['url'] ?? null)
                <a href="{{ $item['url'] }}" class="text-gray-500 transition hover:text-emerald-700">{{ $item['label'] }}</a>
            @elseif ($loop->last)
                <span class="font-medium text-emerald-700" aria-current="page">{{ $item['label'] }}</span>
            @else
                <span class="text-gray-500">{{ $item['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif
