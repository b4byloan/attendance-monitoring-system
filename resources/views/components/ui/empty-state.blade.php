@props(['message' => 'Nothing here yet.'])

<div class="text-center py-12">
    <p class="text-gray-400 text-sm">{{ $message }}</p>
    @isset($action){{ $action }}@endisset
</div>