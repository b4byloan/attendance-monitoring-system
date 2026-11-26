@props(['icon', 'name' => null])

<div @class([
    'mt-2 flex items-stretch overflow-hidden rounded-lg border border-slate-300 bg-white transition focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-100',
    'border-red-400' => $name && $errors->has($name),
])>
    <span class="flex w-11 shrink-0 items-center justify-center border-r border-slate-200 bg-slate-50 text-emerald-700" aria-hidden="true">
        <x-ui.icon :name="$icon" class="h-4 w-4" />
    </span>
    <div class="min-w-0 flex-1">
        {{ $slot }}
    </div>
</div>
