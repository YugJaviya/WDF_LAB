import { escapeHTML, fetchJSONArray, filterSortPage } from "./data-service.js";

const PAGE_SIZE = 5;

function makeCard(record, type) {
    if (type === "events") {
        const date = escapeHTML(record.date);
        const readableDate = new Date(String(record.date) + "T12:00:00").toLocaleDateString(
            undefined,
            { year: "numeric", month: "long", day: "numeric" }
        );
        return '<article class="data-card event-card">' +
            '<p class="data-category">' + escapeHTML(record.category) + '</p>' +
            '<h4>' + escapeHTML(record.title) + '</h4>' +
            '<p><strong>Date:</strong> <time datetime="' + date + '">' + escapeHTML(readableDate) + '</time></p>' +
            '<p><strong>Venue:</strong> ' + escapeHTML(record.venue) + '</p>' +
            '<p>' + escapeHTML(record.description) + '</p></article>';
    }

    if (type === "students") {
        return '<article class="data-card profile-card">' +
            '<h4>' + escapeHTML(record.name) + '</h4>' +
            '<div class="profile-details">' +
            '<p><strong>Enrollment:</strong> ' + escapeHTML(record.enrollment) + '</p>' +
            '<p><strong>Department:</strong> ' + escapeHTML(record.department) + '</p>' +
            '<p><strong>Course:</strong> ' + escapeHTML(record.course) + '</p>' +
            '<p><strong>Year:</strong> ' + escapeHTML(record.year) + '</p>' +
            '<p><strong>Division:</strong> ' + escapeHTML(record.division) + '</p>' +
            '<p><strong>Email:</strong> ' + escapeHTML(record.email) + '</p>' +
            '</div></article>';
    }

    return '<details class="data-card faq-card">' +
        '<summary>' + escapeHTML(record.question) + '</summary>' +
        '<p>' + escapeHTML(record.answer) + '</p>' +
        '<span class="data-category">' + escapeHTML(record.category) + '</span>' +
        '</details>';
}

function initializeView(root, records) {
    const type = root.dataset.collection;
    const filterField = root.dataset.filterField;
    const searchFields = root.dataset.searchFields.split(",").map((field) => field.trim());
    const search = root.querySelector('[data-role="search"]');
    const filter = root.querySelector('[data-role="filter"]');
    const sort = root.querySelector('[data-role="sort"]');
    const results = root.querySelector('[data-role="results"]');
    const status = root.querySelector('[data-role="status"]');
    const summary = root.querySelector('[data-role="summary"]');
    const pagination = root.querySelector('[data-role="pagination"]');
    const pageLabel = root.querySelector('[data-role="page"]');
    const previous = root.querySelector('[data-action="previous"]');
    const next = root.querySelector('[data-action="next"]');
    const error = root.querySelector('[data-role="error"]');
    const retry = root.querySelector('[data-role="retry"]');
    const state = { query: "", filterValue: "", sortValue: sort.value, page: 1 };

    const filterOptions = [...new Set(records.map((record) => record[filterField]))]
        .filter(Boolean)
        .sort((a, b) => String(a).localeCompare(String(b), undefined, { sensitivity: "base" }));
    filterOptions.forEach((value) => {
        const option = document.createElement("option");
        option.value = value;
        option.textContent = value;
        filter.append(option);
    });

    const render = () => {
        const page = filterSortPage(records, {
            ...state,
            filterField,
            searchFields,
            pageSize: PAGE_SIZE
        });
        state.page = page.page;

        if (page.matching.length === 0) {
            results.innerHTML = '<p class="empty-state">No matching records. Try a different search or filter.</p>';
        } else {
            results.innerHTML = page.pageItems.map((record) => makeCard(record, type)).join("");
        }

        summary.textContent = "Showing " + page.pageItems.length + " of " +
            page.matching.length + " matching records.";
        pageLabel.textContent = "Page " + page.page + " of " + page.pageCount;
        pagination.hidden = page.pageCount <= 1;
        previous.disabled = page.page <= 1;
        next.disabled = page.page >= page.pageCount;
    };

    search.addEventListener("input", () => {
        state.query = search.value;
        state.page = 1;
        render();
    });
    filter.addEventListener("change", () => {
        state.filterValue = filter.value;
        state.page = 1;
        render();
    });
    sort.addEventListener("change", () => {
        state.sortValue = sort.value;
        state.page = 1;
        render();
    });
    pagination.addEventListener("click", (event) => {
        const button = event.target.closest("[data-action]");
        const action = button ? button.dataset.action : "";
        if (action === "previous") state.page -= 1;
        if (action === "next") state.page += 1;
        render();
    });

    retry.hidden = true;
    error.hidden = true;
    status.textContent = records.length + " records loaded from " + root.dataset.source + ".";
    render();
}

async function loadView(root) {
    const status = root.querySelector('[data-role="status"]');
    const error = root.querySelector('[data-role="error"]');
    const retry = root.querySelector('[data-role="retry"]');

    const load = async () => {
        root.setAttribute("aria-busy", "true");
        status.textContent = "Loading " + root.dataset.source + "...";
        error.hidden = true;
        retry.hidden = true;

        try {
            const records = await fetchJSONArray(root.dataset.source);
            initializeView(root, records);
        } catch (reason) {
            status.textContent = "Data could not be loaded.";
            error.textContent = reason.message;
            error.hidden = false;
            retry.hidden = false;
        } finally {
            root.setAttribute("aria-busy", "false");
        }
    };

    retry.addEventListener("click", load);
    await load();
}

document.querySelectorAll(".data-view").forEach(loadView);
