@once
    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-copy-button]');

            if (!button) {
                return;
            }

            const value = button.dataset.copyValue;
            const text = button.querySelector('[data-copy-text]');
            const original = text.textContent;

            function copy(valueToCopy) {
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(valueToCopy);
                }

                const area = document.createElement('textarea');
                area.value = valueToCopy;
                area.style.position = 'fixed';
                area.style.opacity = '0';
                document.body.appendChild(area);
                area.select();

                try {
                    document.execCommand('copy');
                } finally {
                    document.body.removeChild(area);
                }

                return Promise.resolve();
            }

            copy(value).then(function () {
                text.textContent = 'Copied';
                button.classList.add('border-emerald-300', 'bg-emerald-50', 'text-emerald-700');

                window.setTimeout(function () {
                    text.textContent = original;
                    button.classList.remove('border-emerald-300', 'bg-emerald-50', 'text-emerald-700');
                }, 2000);
            }).catch(function () {
                text.textContent = 'Press Ctrl+C';
            });
        });
    </script>
@endonce
