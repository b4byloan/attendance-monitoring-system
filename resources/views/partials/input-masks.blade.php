<script>
    document.addEventListener('input', function (event) {
        const input = event.target.closest('[data-digits-only]');

        if (! input) {
            return;
        }

        let value = input.value.replace(/\D+/g, '');

        if (input.hasAttribute('data-phone-number') && value.startsWith('9')) {
            value = '0' + value;
        }

        const maxDigits = Number(input.dataset.maxDigits || 0);

        input.value = maxDigits > 0 ? value.slice(0, maxDigits) : value;
    });
</script>
