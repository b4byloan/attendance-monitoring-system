@props(['fit' => false, 'minWidth' => null])

@php
    // Flowbite-style wrapper: the table keeps a sensible minimum width and the
    // wrapper scrolls horizontally on small screens instead of crushing columns.
    $minWidthClass = $minWidth ?? ($fit ? 'min-w-[720px]' : 'min-w-[900px]');

    $wrapperClasses = 'relative w-full max-w-full overflow-x-auto overscroll-x-contain rounded-lg border border-gray-200 bg-white shadow-sm '
        .'[&_thead]:bg-gray-50 [&_thead]:text-xs [&_thead]:uppercase [&_thead]:tracking-wider [&_thead]:text-gray-700 '
        .'[&_thead_tr]:border-b [&_thead_tr]:border-gray-200 '
        .'[&_th]:whitespace-nowrap [&_th]:px-4 [&_th]:py-3 [&_th]:text-left [&_th]:font-semibold '
        .'[&_tbody]:divide-y [&_tbody]:divide-gray-200 [&_tbody]:bg-white '
        .'[&_tbody_tr]:bg-white [&_tbody_tr]:transition-colors [&_tbody_tr:hover]:bg-gray-50 '
        .'[&_td]:px-4 [&_td]:py-4';
@endphp

<div {{ $attributes->merge(['class' => $wrapperClasses]) }}>
    <table class="w-full {{ $minWidthClass }} text-left text-sm text-gray-500">
        {{ $slot }}
    </table>
</div>
