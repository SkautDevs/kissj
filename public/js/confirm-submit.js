document.addEventListener('submit', function (event) {
    const message = event.target.dataset.confirm;
    if (message !== undefined && confirm(message) === false) {
        event.preventDefault();
    }
});
