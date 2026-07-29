<script>
    function idejUuid() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (char) {
            const random = Math.floor(Math.random() * 16);
            const value = char === 'x' ? random : ((random & 0x3) | 0x8);
            return value.toString(16);
        });
    }

    function idejFormPageId() {
        const state = window.history.state || {};

        if (! state.idejFormPageId) {
            state.idejFormPageId = idejUuid();
            window.history.replaceState(state, document.title, window.location.href);
        }

        return state.idejFormPageId;
    }

    function idejPrepareForm(form, index = 0) {
        if (! form.matches('form') || form.dataset.allowResubmit === 'true') {
            return;
        }

        const method = String(form.getAttribute('method') || 'GET').toUpperCase();

        if (method === 'GET') {
            return;
        }

        let operationInput = form.querySelector('input[name="_idempotency_key"]');

        if (! operationInput) {
            operationInput = document.createElement('input');
            operationInput.type = 'hidden';
            operationInput.name = '_idempotency_key';
            form.appendChild(operationInput);
        }

        const formScope = [
            idejFormPageId(),
            method,
            form.getAttribute('action') || window.location.pathname,
            form.dataset.idempotencyScope || String(index),
        ].join('|');
        const storageKey = 'idej-form-operation:' + formScope;
        let operationUuid = null;

        try {
            operationUuid = window.sessionStorage.getItem(storageKey);
        } catch (error) {
            operationUuid = null;
        }

        if (! operationUuid) {
            operationUuid = idejUuid();

            try {
                window.sessionStorage.setItem(storageKey, operationUuid);
            } catch (error) {
                // El campo oculto conserva la protección durante la vida de la página.
            }
        }

        operationInput.value = operationUuid;
    }

    function idejResetFormButtons(form) {
        form.dataset.submitted = 'false';

        form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
            button.disabled = false;
            button.classList.remove('opacity-60', 'cursor-not-allowed');

            if (button.dataset.originalText) {
                button.innerHTML = button.dataset.originalText;
            }
        });
    }

    document.addEventListener('submit', function (event) {
        const form = event.target;

        if (! form.matches('form') || form.dataset.allowResubmit === 'true' || event.defaultPrevented) {
            return;
        }

        idejPrepareForm(form, Array.from(document.forms).indexOf(form));

        if (form.dataset.confirm && ! window.confirm(form.dataset.confirm)) {
            event.preventDefault();
            return;
        }

        if (form.dataset.submitted === 'true') {
            event.preventDefault();
            return;
        }

        form.dataset.submitted = 'true';

        form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
            button.disabled = true;
            button.classList.add('opacity-60', 'cursor-not-allowed');

            if (! button.dataset.originalText) {
                button.dataset.originalText = button.innerHTML;
            }

            if (! button.dataset.keepText && form.dataset.keepText !== 'true') {
                button.innerHTML = 'Procesando...';
            }
        });
    });

    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form').forEach(function (form, index) {
            idejPrepareForm(form, index);
            idejResetFormButtons(form);
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form').forEach(function (form, index) {
            idejPrepareForm(form, index);
        });
    });
</script>
