if (!window._adminNoteInitialized) {
    window._adminNoteInitialized = true;
    document.addEventListener('submit', async function(e) {
        const form = e.target.closest('.admin-note-form');
        if (!form) return;
        e.preventDefault();
        const resultElement = form.getElementsByClassName('formResult')[0];
        resultElement.innerText = '↻';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
            });

            await response.json();
            resultElement.innerText = response.ok ? '✔' : '☠';
        } catch (error) {
            resultElement.innerText = '☠';
            console.error(error.message);
        }
    });
}
