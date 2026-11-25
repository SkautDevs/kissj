if (!window._ageGroupToggleInitialized) {
    window._ageGroupToggleInitialized = true;

    function showField(field, show) {
        field.style.display = show ? '' : 'none';
        const input = field.querySelector('input, textarea, select');
        if (input) {
            if (!show) input.removeAttribute('required');
            else if (input.dataset.required !== undefined) input.setAttribute('required', 'required');
        }
    }

    // mirrors PHP DateTime::diff()->y on calendar dates ('YYYY-MM-DD')
    function isUnder18(birthDate, eventStartDate) {
        const [birthY, birthM, birthD] = birthDate.split('-').map(Number);
        const [startY, startM, startD] = eventStartDate.split('-').map(Number);
        const hadBirthday = startM > birthM || (startM === birthM && startD >= birthD);
        const age = startY - birthY - (hadBirthday ? 0 : 1);

        return age < 18;
    }

    function updateAgeGroupFields(birthDateInput) {
        const under18 = document.querySelectorAll('[data-age-group="under18"]');
        const over18 = document.querySelectorAll('[data-age-group="over18"]');
        if (under18.length === 0 && over18.length === 0) return;

        const eventStartDate = birthDateInput.dataset.eventStartDate;
        if (birthDateInput.value && eventStartDate) {
            const isMinor = isUnder18(birthDateInput.value, eventStartDate);
            under18.forEach(f => showField(f, isMinor));
            over18.forEach(f => showField(f, !isMinor));
        } else {
            under18.forEach(f => showField(f, true));
            over18.forEach(f => showField(f, true));
        }
    }

    // server rendering is authoritative on load, so only react to changes
    document.addEventListener('DOMContentLoaded', function() {
        const birthDate = document.getElementById('birthDate');
        if (birthDate) {
            birthDate.addEventListener('change', () => updateAgeGroupFields(birthDate));
        }
    });
}
