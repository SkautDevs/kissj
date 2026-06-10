if (!window._adminAjaxSubmitInitialized) {
    window._adminAjaxSubmitInitialized = true;

    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('.js-ajax-submit');
        if (!form) return;
        e.preventDefault();

        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        const url = form.dataset.ajaxAction || form.action;
        const removeTargetSelector = form.dataset.removeTarget;
        const errorFallback = form.dataset.errorFallback || '';

        const previousAlert = form.previousElementSibling;
        if (previousAlert?.matches('.alert.alert-warning.js-ajax-error')) {
            previousAlert.remove();
        }

        if (submitButton) {
            submitButton.disabled = true;
        }

        const showError = (message) => {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning js-ajax-error';
            alertDiv.textContent = message;
            form.parentNode.insertBefore(alertDiv, form);
            if (submitButton) {
                submitButton.disabled = false;
            }
        };

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json' },
                redirect: 'manual',
            });

            if (response.ok) {
                if (removeTargetSelector) {
                    const target = form.closest(removeTargetSelector);
                    if (target) {
                        target.remove();
                    }
                }
                return;
            }

            try {
                const payload = await response.json();
                showError(payload.translationMessage || errorFallback);
            } catch (parseError) {
                console.error(parseError);
                showError(errorFallback);
            }
        } catch (error) {
            console.error(error);
            showError(errorFallback);
        }
    });
}
