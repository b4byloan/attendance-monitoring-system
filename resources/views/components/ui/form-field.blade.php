@props(['icon', 'name' => null])

@php
    $border = $name && $errors->has($name) ? 'border-red-400' : 'border-gray-300';
@endphp

<div {{ $attributes->merge(['class' => "flex items-stretch overflow-hidden rounded-lg border bg-white transition focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-100 $border"]) }}>
    <span class="flex w-10 shrink-0 items-center justify-center border-r border-gray-200 bg-gray-50 text-gray-500" aria-hidden="true">
        <x-ui.icon :name="$icon" class="h-4 w-4" />
    </span>
    <div class="min-w-0 flex-1">
        {{ $slot }}
    </div>
</div>
