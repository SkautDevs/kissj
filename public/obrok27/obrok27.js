(function () {
    'use strict';

    const INFO_URL = 'https://obrok.cz/predregistrace';
    const NOTE_PREFIX = 'Kód servisáka: ';
    const NOTE_LINE = /^Kód servisáka:.*$/m;
    const ERROR_TEXT = 'Kód se nepodařilo uložit, zkus to prosím znovu nebo ho vepiš do poznámky ručně.';
    const DETAILS_MISSING_TEXT = 'Nejdřív vyplň a ulož své údaje, potom ulož kód servisáka.';
    const MISSING_CODE_TEXT = 'Bez kódu servisáka to nepůjde! Vlož jeho šestipísmenný osobní kód.';
    const MISSING_CODE_ANIMATION_URL = new URL('hrozeni_prstem.webp', document.currentScript.src).href;
    const MISSING_CODE_ANIMATION_MS = 6000;

    function el(tag, attributes, text) {
        const element = document.createElement(tag);
        Object.entries(attributes).forEach(([name, value]) => element.setAttribute(name, value));
        if (text !== undefined) {
            element.textContent = text;
        }

        return element;
    }

    function upsertNote(notes, code) {
        const line = NOTE_PREFIX + code;
        if (NOTE_LINE.test(notes)) {
            return notes.replace(NOTE_LINE, line);
        }

        const trimmed = notes.trimEnd();

        return trimmed === '' ? line : trimmed + '\n' + line;
    }

    function localDateEighteenYearsAgo() {
        const date = new Date();
        date.setFullYear(date.getFullYear() - 18);

        return [date.getFullYear(), date.getMonth() + 1, date.getDate()]
            .map((part) => String(part).padStart(2, '0'))
            .join('-');
    }

    // an unsaved details form carries prefills (birthDate = today minus 18 years) that must not be persisted unseen
    function isDetailsFormFilled(detailsForm) {
        const birthDate = detailsForm.querySelector('input[name="birthDate"]');
        if (birthDate !== null && birthDate.value === localDateEighteenYearsAgo()) {
            return false;
        }

        return Array.from(detailsForm.querySelectorAll('[required]')).every((field) => field.value.trim() !== '');
    }

    // replays the TL's own details form so every other field is sent back unchanged -
    // changeDetails nulls any allowed field missing from the POST
    async function saveCode(code) {
        const detailsLink = document.querySelector('a[href$="/showChangeDetails"]');
        if (detailsLink === null) {
            throw new Error('details link not found');
        }

        const page = await fetch(detailsLink.href, {credentials: 'same-origin'});
        if (!page.ok) {
            throw new Error('details page failed: ' + page.status);
        }

        const doc = new DOMParser().parseFromString(await page.text(), 'text/html');
        const detailsForm = doc.querySelector('form[action$="/changeDetails"]');
        if (detailsForm === null || detailsForm.querySelector('textarea[name="notes"]') === null) {
            throw new Error('details form not found');
        }
        if (!isDetailsFormFilled(detailsForm)) {
            throw Object.assign(new Error('details not filled'), {userMessage: DETAILS_MISSING_TEXT});
        }

        const data = new FormData(detailsForm);
        data.set('notes', upsertNote(String(data.get('notes') ?? ''), code));

        // manual redirect keeps the "details saved" flash for the reload instead of letting fetch consume it
        const response = await fetch(new URL(detailsForm.getAttribute('action'), page.url), {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            redirect: 'manual',
        });
        if (response.type !== 'opaqueredirect' && !response.ok) {
            throw new Error('save failed: ' + response.status);
        }
    }

    function showMissingCodeOverlay() {
        if (document.getElementById('obrokMissingCode') !== null) {
            return;
        }

        const overlay = el('div', {
            id: 'obrokMissingCode',
            style: 'position: fixed; inset: 0; z-index: 1000; display: flex; align-items: center; justify-content: center;'
                + ' background: rgba(0, 0, 0, 0.6); cursor: pointer;',
        });
        const panel = el('div', {
            style: 'background: var(--color-card-background); border-radius: 8px; padding: 1rem; max-width: 90vw; text-align: center;',
        });
        const animation = el('img', {
            src: MISSING_CODE_ANIMATION_URL,
            alt: '',
            style: 'width: 410px; max-width: 100%; display: block; margin: 0 auto;',
        });

        panel.append(animation, el('p', {style: 'margin: 0.5rem 0 0; font-weight: bold;'}, MISSING_CODE_TEXT));
        overlay.append(panel);
        overlay.addEventListener('click', () => overlay.remove());
        setTimeout(() => overlay.remove(), MISSING_CODE_ANIMATION_MS);
        document.body.append(overlay);
    }

    function buildForm() {
        const form = el('form', {class: 'form-group form-group-middle'});
        const label = el('label', {for: 'obrokIstCode'}, 'Pro registraci už teď potřebuješ osobní kód servisáka. ');
        label.append(el('a', {href: INFO_URL, target: '_blank', rel: 'noopener'}, 'Víc info najdeš na webu'));
        const input = el('input', {
            id: 'obrokIstCode',
            type: 'text',
            required: 'required',
            class: 'form-control',
            pattern: '[a-zA-Z]{6}',
            title: 'šest písmen',
            placeholder: 'ABCDEF',
        });
        const button = el('input', {type: 'submit', value: 'Ulož kód servisáka', class: 'btn btn-small'});
        const error = el('p', {role: 'alert', hidden: 'hidden'}, ERROR_TEXT);

        input.addEventListener('invalid', (invalidEvent) => {
            if (input.value.trim() === '') {
                invalidEvent.preventDefault();
                showMissingCodeOverlay();
            }
        });

        form.append(el('h2', {}, 'Máš svého servisáka?'), label, input, el('br', {}), button, error);
        form.addEventListener('submit', async (submitEvent) => {
            submitEvent.preventDefault();
            button.disabled = true;
            error.hidden = true;
            try {
                await saveCode(input.value.trim().toUpperCase());
                window.location.reload();
            } catch (e) {
                console.error(e);
                error.textContent = e.userMessage ?? ERROR_TEXT;
                error.hidden = false;
                button.disabled = false;
            }
        });

        return form;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const tieForm = document.querySelector('form[action$="/tieParticipantToTroopByLeader"]');
        if (tieForm !== null) {
            const card = el('div', {class: 'card card-double'});
            card.append(buildForm());
            tieForm.closest('.card').after(card);
        }
    });
})();

(function () {
    'use strict';

    const TAGS = [
        'O27tag_fajvka.svg',
        'O27tag_hvezda.svg',
        'O27tag_hvezda_man.svg',
        'O27tag_lilie.svg',
        'O27tag_nahoru.svg',
        'O27tag_obrok.svg',
        'O27tag_otaznik.svg',
        'O27tag_rychle.svg',
        'O27tag_slib.svg',
        'O27tag_stan.svg',
        'O27tag_uzel.svg',
        'O27tag_vykricnik.svg',
    ];
    const TAGS_URL = new URL('tags/', document.currentScript.src);
    const REEL_LENGTH = 24;
    const SPIN_MS = 4000;
    const MIN_OVERSHOOT = 0.05;
    const MAX_OVERSHOOT = 0.4;

    function randomTagOtherThan(tag) {
        const others = TAGS.filter((candidate) => candidate !== tag);

        return others[Math.floor(Math.random() * others.length)];
    }

    function tagImage(tag) {
        const image = document.createElement('img');
        image.src = new URL(tag, TAGS_URL).href;
        image.alt = '';

        return image;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const slot = document.createElement('div');
        slot.className = 'tag-slot';
        slot.setAttribute('aria-hidden', 'true');
        const reel = document.createElement('div');
        reel.className = 'tag-slot-reel';
        let current = TAGS[Math.floor(Math.random() * TAGS.length)];
        reel.append(tagImage(current));
        slot.append(reel);
        document.body.append(slot);
        window.addEventListener('load', () => TAGS.forEach(tagImage));

        let spinning = false;
        document.addEventListener('click', function (event) {
            const box = slot.getBoundingClientRect();
            const isInsideSlot = event.clientX >= box.left && event.clientX < box.right
                && event.clientY >= box.top && event.clientY < box.bottom;
            if (spinning || isInsideSlot === false || event.target.closest('a, button, input, select, textarea, label') !== null) {
                return;
            }
            const target = randomTagOtherThan(current);
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                reel.replaceChildren(tagImage(target));
                current = target;

                return;
            }
            spinning = true;

            const strip = [randomTagOtherThan(target), target];
            while (strip.length < REEL_LENGTH - 1) {
                strip.push(randomTagOtherThan(strip[strip.length - 1]));
            }
            const images = [...strip.map(tagImage), reel.firstElementChild];
            reel.replaceChildren(...images);

            const height = slot.clientHeight;
            const start = -(images.length - 1) * height;
            const distance = (images.length - 2) * height;
            const overshoot = (MIN_OVERSHOOT + Math.random() * (MAX_OVERSHOOT - MIN_OVERSHOOT)) * height;
            const at = (progress, extra = 0) => ({transform: `translateY(${start + progress * distance + extra}px)`});
            const animation = reel.animate([
                {...at(0), easing: 'cubic-bezier(.5, 0, 1, 1)'},
                {...at(0.07), offset: 0.08, easing: 'linear'},
                {...at(0.46), offset: 0.3, easing: 'cubic-bezier(.3, .6, .5, 1)'},
                {...at(1, overshoot), offset: 0.9, easing: 'ease-in-out'},
                at(1),
            ], {duration: SPIN_MS, fill: 'forwards'});

            animation.finished.finally(function () {
                reel.replaceChildren(images[1]);
                animation.cancel();
                current = target;
                spinning = false;
            });
        });
    });
})();
