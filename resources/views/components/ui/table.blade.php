@props(['fit' => false])

<div class="w-full max-w-full overflow-x-auto overscroll-x-contain rounded-lg border border-gray-200 bg-white shadow-sm">
    <table class="w-full {{ $fit ? '' : 'min-w-max' }} text-left text-sm">
        {{ $slot }}
    </table>
</div>