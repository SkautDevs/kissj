if (!window._copyToClipboardInitialized) {
    window._copyToClipboardInitialized = true;

    async function copyText(text) {
        if (navigator.clipboard) {
            await navigator.clipboard.writeText(text);
            return;
        }

        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        const copied = document.execCommand('copy');
        document.body.removeChild(textarea);
        if (!copied) {
            throw new Error('execCommand copy failed');
        }
    }

    function flashButtonFeedback(button, symbol) {
        if (!button.dataset.originalText) {
            button.dataset.originalText = button.textContent;
        }
        button.textContent = symbol;
        if (button.dataset.resetTimerId) {
            clearTimeout(Number(button.dataset.resetTimerId));
        }
        button.dataset.resetTimerId = String(setTimeout(function () {
            button.textContent = button.dataset.originalText;
            delete button.dataset.resetTimerId;
        }, 1500));
    }

    document.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-copy]');
        if (!button) {
            return;
        }

        const text = button.getAttribute('data-copy');
        if (!text) {
            return;
        }

        try {
            await copyText(text);
            flashButtonFeedback(button, '✓');
        } catch (error) {
            flashButtonFeedback(button, '✗');
            console.error('Copy failed:', error);
        }
    });
}
