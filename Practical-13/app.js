'use strict';

const apiUrl = 'api/events.php';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const notice = document.querySelector('#notice');
const list = document.querySelector('#event-list');
const form = document.querySelector('#event-form');
const eventId = document.querySelector('#event-id');
const formTitle = document.querySelector('#form-title');
const saveButton = document.querySelector('#save-button');
const cancelEdit = document.querySelector('#cancel-edit');
const searchInput = document.querySelector('#search');
const categoryFilter = document.querySelector('#filter-category');
const statusFilter = document.querySelector('#filter-status');
let searchTimer;

function showMessage(message, type = 'info') {
    notice.textContent = message;
    notice.className = `notice ${type}`;
}

async function request(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: {
            ...(options.headers || {}),
            ...(options.method === 'POST' ? {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
            } : {}),
        },
    });

    const payload = await response.json();
    if (!response.ok || !payload.success) {
        throw new Error(payload.message || `Request failed (${response.status}).`);
    }
    return payload;
}

function makeElement(tag, className, text) {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (text !== undefined) element.textContent = text;
    return element;
}

function renderEvents(events) {
    list.replaceChildren();
    document.querySelector('#result-count').textContent = `${events.length} event(s)`;

    if (!events.length) {
        list.append(makeElement('p', 'empty', 'No events match your search.'));
        return;
    }

    events.forEach((event) => {
        const card = makeElement('article', 'event-card');
        if (event.poster_file) {
            const image = makeElement('img', 'poster');
            image.src = `/Practical-12/uploads/${encodeURIComponent(event.poster_file)}`;
            image.alt = `Poster for ${event.title}`;
            card.append(image);
        }
        const body = makeElement('div', 'event-content');
        const category = makeElement('span', 'badge', event.category);
        const state = makeElement('span', `badge ${event.status}`, event.status[0].toUpperCase() + event.status.slice(1));
        const meta = makeElement('div', 'event-meta');
        meta.append(category, state);
        body.append(meta);
        body.append(makeElement('h3', '', event.title));
        body.append(makeElement('p', '', event.description));
        body.append(makeElement('p', 'muted', `${event.event_date} · ${event.venue}`));

        const actions = makeElement('div', 'row-actions');
        const edit = makeElement('button', 'text-button', 'Edit');
        edit.type = 'button';
        edit.addEventListener('click', () => beginEdit(event));
        const remove = makeElement('button', 'text-button danger', 'Delete');
        remove.type = 'button';
        remove.addEventListener('click', () => deleteEvent(event.event_id));
        actions.append(edit, remove);
        body.append(actions);
        card.append(body);
        list.append(card);
    });
}

async function loadEvents() {
    showMessage('Loading events…');
    const params = new URLSearchParams();
    if (searchInput.value.trim()) params.set('q', searchInput.value.trim());
    if (categoryFilter.value) params.set('category', categoryFilter.value);
    if (statusFilter.value) params.set('status', statusFilter.value);
    try {
        const result = await request(`${apiUrl}?${params.toString()}`);
        renderEvents(result.data);
        showMessage('Events loaded.', 'success');
    } catch (error) {
        showMessage(error.message, 'error');
    }
}

function resetForm() {
    form.reset();
    eventId.value = '';
    formTitle.textContent = 'Add event';
    saveButton.textContent = 'Add event';
    cancelEdit.hidden = true;
}

function beginEdit(event) {
    eventId.value = event.event_id;
    document.querySelector('#title').value = event.title;
    document.querySelector('#category').value = event.category;
    document.querySelector('#event-date').value = event.event_date;
    document.querySelector('#venue').value = event.venue;
    document.querySelector('#status').value = event.status;
    document.querySelector('#description').value = event.description;
    formTitle.textContent = 'Edit event';
    saveButton.textContent = 'Save changes';
    cancelEdit.hidden = false;
    document.querySelector('#title').focus();
}

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const editing = eventId.value !== '';
    const payload = {
        action: editing ? 'update' : 'create',
        event_id: editing ? Number(eventId.value) : undefined,
        title: document.querySelector('#title').value.trim(),
        category: document.querySelector('#category').value,
        event_date: document.querySelector('#event-date').value,
        venue: document.querySelector('#venue').value.trim(),
        status: document.querySelector('#status').value,
        description: document.querySelector('#description').value.trim(),
    };
    saveButton.disabled = true;
    showMessage('Saving event…');
    try {
        const result = await request(apiUrl, {
            method: 'POST',
            body: JSON.stringify(payload),
        });
        resetForm();
        showMessage(result.message, 'success');
        await loadEvents();
        showMessage(result.message, 'success');
    } catch (error) {
        showMessage(error.message, 'error');
    } finally {
        saveButton.disabled = false;
    }
});

async function deleteEvent(id) {
    if (!window.confirm('Delete this event? This action cannot be undone.')) return;
    showMessage('Deleting event…');
    try {
        const result = await request(apiUrl, {
            method: 'POST',
            body: JSON.stringify({ action: 'delete', event_id: Number(id) }),
        });
        await loadEvents();
        showMessage(result.message, 'success');
    } catch (error) {
        showMessage(error.message, 'error');
    }
}

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadEvents, 350);
});
categoryFilter.addEventListener('change', loadEvents);
statusFilter.addEventListener('change', loadEvents);
cancelEdit.addEventListener('click', resetForm);
loadEvents();
