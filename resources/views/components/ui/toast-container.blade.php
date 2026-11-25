@php
$toasts = [];

foreach (['success', 'info', 'warning', 'error'] as $type) {
    if (filled(session($type))) {
        $toasts[] = ['type' => $type, 'message' => session($type)];
    }
}

if ($errors->any()) {
    $toasts[] = [
        'type' => 'error',
        'message' => $errors->count() === 1 ? $errors->first() : $errors->all(),
    ];
}
@endphp

<div id="toast-container"
     class="pointer-events-none fixed inset-x-4 top-4 z-[100] flex max-h-[calc(100vh-2rem)] flex-col items-end gap-2 overflow-y-auto sm:inset-x-auto sm:right-6 sm:top-20 sm:w-96"
     aria-live="polite">
    @foreach ($toasts as $toast)
        <x-ui.toast :type="$toast['type']" :message="$toast['message']" />
    @endforeach
</div>

<template id="toast-template">
    @foreach (['success', 'error', 'warning', 'info'] as $type)
        <x-ui.toast :type="$type" />
    @endforeach
</template>

<script>
    (function () {
        const durations = { success: 5000, info: 5000, warning: 7000, error: 8000 };
        const timers = new WeakMap();

        function container() {
            return document.getElementById('toast-container');
        }

        function dismiss(toast) {
            if (!toast || toast.dataset.dismissing === 'true') {
                return;
            }

            toast.dataset.dismissing = 'true';
            clearTimeout(timers.get(toast));

            window.setTimeout(function () {
                toast.remove();
            }, 180);
        }

        function schedule(toast) {
            const type = toast.dataset.toastType || 'info';
            const duration = Number(toast.dataset.toastDuration || durations[type] || 5000);

            clearTimeout(timers.get(toast));
            timers.set(toast, window.setTimeout(function () {
                dismiss(toast);
            }, duration));
        }

        function prepare(toast) {
            schedule(toast);

            toast.addEventListener('mouseenter', function () {
                clearTimeout(timers.get(toast));
            });

            toast.addEventListener('mouseleave', function () {
                schedule(toast);
            });

            toast.addEventListener('focusin', function () {
                clearTimeout(timers.get(toast));
            });

            toast.addEventListener('focusout', function () {
                schedule(toast);
            });
        }

        window.showToast = function (type, message, options) {
            const target = container();
            const templates = document.getElementById('toast-template');
            const settings = options || {};

            if (!target || !templates) {
                return null;
            }

            const template = templates.content.querySelector('[data-toast-type="' + (type || 'info') + '"]');

            if (!template) {
                return null;
            }

            const toast = template.cloneNode(true);
            const text = toast.querySelector('[data-toast-message]');

            if (text) {
                text.textContent = message || '';
            }

            if (settings.duration) {
                toast.dataset.toastDuration = settings.duration;
            }

            target.append(toast);
            prepare(toast);

            return toast;
        };

        document.addEventListener('click', function (event) {
            const target = event.target instanceof Element ? event.target : null;
            const button = target ? target.closest('[data-toast-close]') : null;

            if (button) {
                dismiss(button.closest('[data-toast]'));
            }
        });

        document.querySelectorAll('#toast-container [data-toast]').forEach(prepare);
    })();
</script>
