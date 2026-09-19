(function () {
    'use strict';

    const INFO_URL = 'https://obrok.cz/predregistrace';
    const NOTE_PREFIX = 'Kód servisáka: ';
    const NOTE_LINE = /^Kód servisáka: .*$/m;
    const ERROR_TEXT = 'Kód se nepodařilo uložit, zkus to prosím znovu nebo ho vepiš do poznámky ručně.';
    const DETAILS_MISSING_TEXT = 'Nejdřív vyplň a ulož své údaje, potom ulož kód servisáka.';

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
            tieForm.after(buildForm());
        }
    });
})();
