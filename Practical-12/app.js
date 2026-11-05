'use strict';

document.querySelectorAll('.delete-form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm || 'Delete this event?')) {
            event.preventDefault();
        }
    });
});
