@props([
    'name' => 'password',
    'id' => null,
    'autocomplete' => 'new-password',
    'required' => false,
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="relative" data-password-field>
    <input id="{{ $inputId }}"
           type="password"
           name="{{ $name }}"
           autocomplete="{{ $autocomplete }}"
           @if ($required) required @endif
           {{ $attributes->merge(['class' => 'w-full rounded-lg border border-gray-300 px-3 py-2 pr-16 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500']) }}>

    <button type="button"
            data-password-toggle
            class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-emerald-700 hover:text-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
            aria-label="Show password"
            aria-pressed="false">
        Show
    </button>
</div>

@once
    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-password-toggle]');

            if (!button) {
                return;
            }

            const field = button.closest('[data-password-field]');
            const input = field ? field.querySelector('input') : null;

            if (!input) {
                return;
            }

            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.textContent = showing ? 'Show' : 'Hide';
            button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            button.setAttribute('aria-pressed', showing ? 'false' : 'true');
            input.focus({ preventScroll: true });
        });
    </script>
@endonce
