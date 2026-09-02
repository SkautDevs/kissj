(function () {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    // aim-space: 0deg = tip straight up; the art's tip sits exactly above the pivot, so no
    // lean correction is needed. clamp keeps the sweep anatomically plausible (up-left quadrant)
    const CLAMP_MIN = -95;
    const CLAMP_MAX = -5;
    let target = null;
    let queued = false;

    function findTarget() {
        const active = document.activeElement;
        if (active instanceof Element && active.matches('form :invalid')) {
            return active;
        }

        // stay inside the form being edited - admin pages hold many independent forms
        const form = active instanceof Element ? active.closest('form') : null;
        if (form !== null) {
            return form.querySelector(':invalid')
                ?? form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');
        }

        return document.querySelector('form :invalid')
            ?? document.querySelector('form button[type="submit"], form input[type="submit"], form button:not([type])');
    }

    function update() {
        queued = false;
        const body = document.body;

        if (reducedMotion.matches || window.innerWidth < 900) {
            body.classList.remove('tentacle-tracking');
            body.style.removeProperty('--tentacle-rot');
            return;
        }

        const rect = target === null || !target.isConnected ? null : target.getBoundingClientRect();

        if (rect === null || (rect.width === 0 && rect.height === 0)) {
            body.classList.remove('tentacle-tracking');
            body.style.removeProperty('--tentacle-rot');
            return;
        }

        // base pivot sits at right:-20px bottom:-30px - see the tentacle block in stylesNavigamus27.css
        const anchorX = window.innerWidth + 20;
        const anchorY = window.innerHeight + 30;
        const dx = rect.left + rect.width / 2 - anchorX;
        const dy = rect.top + rect.height / 2 - anchorY;
        // +90 turns the atan2 angle into rotation from vertical
        let rotation = Math.atan2(dy, dx) * 180 / Math.PI + 90;
        // targets below the anchor line wrap past +180 - bring them back to the left-leaning side
        if (rotation > 180) {
            rotation -= 360;
        }
        rotation = Math.min(CLAMP_MAX, Math.max(CLAMP_MIN, rotation));

        body.classList.add('tentacle-tracking');
        body.style.setProperty('--tentacle-rot', rotation.toFixed(1) + 'deg');
    }

    function schedule() {
        if (!queued) {
            queued = true;
            window.requestAnimationFrame(update);
        }
    }

    function retarget() {
        target = findTarget();
        schedule();
    }

    document.addEventListener('input', retarget);
    document.addEventListener('focusin', retarget);
    document.addEventListener('focusout', retarget);
    window.addEventListener('resize', retarget);
    // scrolling cannot change the target, only its on-screen position - skip the DOM re-scan
    window.addEventListener('scroll', schedule, { passive: true });
    retarget();
})();
