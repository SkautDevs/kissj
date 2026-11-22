// Shrinks chosen photos in the browser before upload, so phone photos fit under the upload limit.
// Without this script, or on any failure, the original files are uploaded and the server shrinks them.
(() => {
    if (typeof createImageBitmap !== 'function' || typeof DataTransfer !== 'function') {
        return;
    }

    const toJpegName = (name) => {
        const dot = name.lastIndexOf('.');

        return `${dot > 0 ? name.slice(0, dot) : name}.jpg`;
    };

    const resize = async (file, maxEdge, quality) => {
        const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
        try {
            const scale = Math.min(1, maxEdge / Math.max(bitmap.width, bitmap.height));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(bitmap.width * scale));
            canvas.height = Math.max(1, Math.round(bitmap.height * scale));

            const context = canvas.getContext('2d');
            if (context === null) {
                throw new Error('No 2D canvas context');
            }
            // JPEG has no alpha: without a white background, transparent pixels would turn black
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);

            const blob = await new Promise((resolve, reject) => {
                canvas.toBlob(
                    (result) => (result === null ? reject(new Error('Canvas export failed')) : resolve(result)),
                    'image/jpeg',
                    quality,
                );
            });

            return new File([blob], toJpegName(file.name), { type: 'image/jpeg' });
        } finally {
            bitmap.close();
        }
    };

    const setSubmitDisabled = (form, disabled) => {
        if (form === null) {
            return;
        }
        for (const button of form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])')) {
            button.disabled = disabled;
        }
    };

    // a newer choice of files wins over a resize still running for an older one
    const latestRun = new WeakMap();

    const onChange = async (input) => {
        const run = (latestRun.get(input) ?? 0) + 1;
        latestRun.set(input, run);
        const form = input.form;
        const originals = Array.from(input.files ?? []);
        const maxEdge = Number(input.dataset.resizeMaxEdge);
        const quality = Number(input.dataset.resizeQuality);
        if (originals.length === 0 || !(maxEdge > 0) || !(quality > 0 && quality <= 1)) {
            // an older run may still hold the buttons disabled
            setSubmitDisabled(form, false);

            return;
        }

        setSubmitDisabled(form, true);
        try {
            const resized = [];
            for (const file of originals) {
                resized.push(await resize(file, maxEdge, quality));
            }

            if (latestRun.get(input) !== run) {
                return;
            }
            const dataTransfer = new DataTransfer();
            for (const file of resized) {
                dataTransfer.items.add(file);
            }
            input.files = dataTransfer.files;
        } catch {
            // the original files stay chosen, the server shrinks them
        } finally {
            if (latestRun.get(input) === run) {
                setSubmitDisabled(form, false);
            }
        }
    };

    document.addEventListener('change', (event) => {
        const input = event.target;
        if (input instanceof HTMLInputElement && input.matches('input[type="file"][data-resize-max-edge]')) {
            onChange(input);
        }
    });
})();
