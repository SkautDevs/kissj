if (!window._ageGroupToggleInitialized) {
    window._ageGroupToggleInitialized = true;

    function showField(field, show) {
        field.style.display = show ? '' : 'none';
        const input = field.querySelector('input, textarea, select');
        if (input) {
            if (show) input.setAttribute('required', 'true');
            else input.removeAttribute('required');
        }
    }

    function updateAgeGroupFields(birthDateInput) {
        const under18 = document.querySelectorAll('[data-age-group="under18"]');
        const over18 = document.querySelectorAll('[data-age-group="over18"]');
        if (under18.length === 0 && over18.length === 0) return;

        const eventStartRaw = birthDateInput && birthDateInput.dataset.eventStartDay;
        if (birthDateInput && birthDateInput.value && eventStartRaw) {
            const eighteenYearsAt = new Date(birthDateInput.value);
            eighteenYearsAt.setFullYear(eighteenYearsAt.getFullYear() + 18);
            const isUnder18 = eighteenYearsAt.getTime() / 1000 > parseInt(eventStartRaw, 10);
            under18.forEach(f => showField(f, isUnder18));
            over18.forEach(f => showField(f, !isUnder18));
        } else {
            under18.forEach(f => showField(f, true));
            over18.forEach(f => showField(f, true));
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const birthDate = document.getElementById('birthDate');
        if (birthDate) {
            birthDate.addEventListener('change', () => updateAgeGroupFields(birthDate));
        }
        updateAgeGroupFields(birthDate);
    });
}
