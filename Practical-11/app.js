'use strict';

document.querySelectorAll('.delete-form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const promptText = form.dataset.confirm || 'Delete this record?';
        if (!window.confirm(promptText)) {
            event.preventDefault();
        }
    });
});
