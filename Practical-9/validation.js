'use strict';

const form = document.querySelector('#registration-form');

if (form) {
    const fields = {
        full_name: document.querySelector('#full_name'),
        username: document.querySelector('#username'),
        email: document.querySelector('#email'),
        password: document.querySelector('#password'),
        confirm_password: document.querySelector('#confirm_password'),
    };

    function errorFor(name) {
        return document.querySelector(`#${name}_error`);
    }

    function validateField(name) {
        const input = fields[name];
        let message = '';

        if (name === 'full_name') {
            if (!input.value.trim()) message = 'Enter your full name.';
            else if (!/^[\p{L}][\p{L} .'-]*$/u.test(input.value.trim())) {
                message = 'Use letters, spaces, apostrophes, periods, or hyphens.';
            }
        }

        if (name === 'username'
            && !/^[A-Za-z0-9_]{3,30}$/.test(input.value.trim())) {
            message = 'Use 3–30 letters, numbers, or underscores.';
        }

        if (name === 'email'
            && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim())) {
            message = 'Enter a valid email address.';
        }

        if (name === 'password') {
            const value = input.value;
            if (value.length < 8 || value.length > 72
                || !/[A-Z]/.test(value) || !/[a-z]/.test(value) || !/[0-9]/.test(value)) {
                message = 'Use 8–72 characters with uppercase, lowercase, and a number.';
            }
        }

        if (name === 'confirm_password' && input.value !== fields.password.value) {
            message = 'The passwords do not match.';
        }

        errorFor(name).textContent = message;
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
        return message === '';
    }

    Object.entries(fields).forEach(([name, input]) => {
        input.addEventListener('input', () => validateField(name));
        input.addEventListener('blur', () => validateField(name));
    });

    fields.password.addEventListener('input', () => {
        if (fields.confirm_password.value) validateField('confirm_password');
    });

    form.addEventListener('submit', (event) => {
        const valid = Object.keys(fields).map(validateField).every(Boolean);
        if (!valid) {
            event.preventDefault();
            const firstInvalid = form.querySelector('[aria-invalid="true"]');
            if (firstInvalid) firstInvalid.focus();
        }
    });
}
