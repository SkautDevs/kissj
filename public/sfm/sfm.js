(function () {
    'use strict';

    const THIRD_TOPIC_PREFIX = '3. preferované téma: ';
    const THIRD_TOPIC_PENDING = 'vyber 1. a 2. preferované téma';
    const FIRST_TOPIC_LABEL = '1. preferované téma:';
    const SECOND_TOPIC_LABEL = '2. preferované téma:';
    const GROUP_TWO_TOPICS = ['Rovering a vedení', 'Zapojení mladých do rozhodování', 'Environmentální výzvy'];

    function thirdTopicName(firstValue, secondValue, options) {
        const remaining = options.find(
            (option) => option.value !== '' && option.value !== firstValue && option.value !== secondValue,
        );

        const bothChosen = firstValue !== '' && secondValue !== '' && remaining !== undefined;

        return bothChosen ? remaining.name : THIRD_TOPIC_PENDING;
    }

    function moveHelpTextAboveLabel(select) {
        const group = select === null ? null : select.closest('.form-group');
        const helpText = group === null ? null : group.querySelector(':scope > .help-text');
        if (helpText !== null) {
            helpText.style.marginBottom = '0.75rem';
            group.prepend(helpText);
        }
    }

    function initForm(first, second) {
        moveHelpTextAboveLabel(document.getElementById('swimming'));
        moveHelpTextAboveLabel(first);

        const thirdTopic = document.createElement('div');
        thirdTopic.className = 'help-text';
        thirdTopic.style.marginTop = '0.75rem';
        second.after(thirdTopic);

        const options = Array.from(second.options).map((option) => ({
            value: option.value,
            name: option.textContent.trim(),
        }));

        function sync() {
            if (first.value !== '' && first.value === second.value) {
                second.value = '';
            }
            Array.from(second.options).forEach((option) => {
                option.disabled = option.value !== '' && option.value === first.value;
            });

            thirdTopic.textContent = THIRD_TOPIC_PREFIX + thirdTopicName(first.value, second.value, options);
        }

        first.addEventListener('change', sync);
        second.addEventListener('change', sync);

        sync();
    }

    function isDetailOf(element, label) {
        const labelNode = element.previousSibling;

        return element.tagName === 'B'
            && labelNode !== null
            && labelNode.nodeType === Node.TEXT_NODE
            && labelNode.textContent.trim() === label;
    }

    function initDetails(second) {
        let first = second.previousElementSibling;
        while (first !== null && isDetailOf(first, FIRST_TOPIC_LABEL) === false) {
            first = first.previousElementSibling;
        }
        if (first === null) {
            return;
        }

        const options = GROUP_TWO_TOPICS.map((topic) => ({value: topic, name: topic}));
        const name = document.createElement('b');
        name.textContent = thirdTopicName(first.textContent.trim(), second.textContent.trim(), options);
        second.nextElementSibling.after(THIRD_TOPIC_PREFIX, name, document.createElement('br'));
    }

    const first = document.getElementById('scarf');
    const second = document.getElementById('driversLicense');
    if (first !== null && second !== null) {
        initForm(first, second);
    }

    Array.from(document.querySelectorAll('b'))
        .filter((element) => isDetailOf(element, SECOND_TOPIC_LABEL))
        .forEach(initDetails);
})();
