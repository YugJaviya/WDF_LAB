const escapeMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;"
};

export function escapeHTML(value) {
    return String(value ?? "").replace(/[&<>"']/g, (character) => escapeMap[character]);
}

export async function fetchJSONArray(url) {
    let response;
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000);
    try {
        response = await fetch(url, {
            headers: { Accept: "application/json" },
            signal: controller.signal
        });
    } catch (error) {
        if (error.name === "AbortError") {
            throw new Error("Loading " + url + " timed out. Check that the JSON file exists beside the HTML page and that the local server is running.");
        }
        throw new Error("Could not load " + url + ". Open this project with a local web server (for example, VS Code Live Server), not directly as a file.");
    } finally {
        clearTimeout(timeoutId);
    }

    if (!response.ok) {
        throw new Error("The server returned " + response.status + " for " + url + ".");
    }

    const records = await response.json();
    if (!Array.isArray(records)) {
        throw new TypeError(url + " must contain a JSON array.");
    }
    return records;
}

export function filterSortPage(records, options) {
    const term = options.query.trim().toLocaleLowerCase();
    const matching = records.filter((record) => {
        const matchesFilter = !options.filterValue
            || String(record[options.filterField]) === options.filterValue;
        const matchesSearch = !term || options.searchFields.some((field) =>
            String(record[field] ?? "").toLocaleLowerCase().includes(term)
        );
        return matchesFilter && matchesSearch;
    });

    const separator = options.sortValue.lastIndexOf("-");
    const sortField = options.sortValue.slice(0, separator);
    const direction = options.sortValue.slice(separator + 1) === "desc" ? -1 : 1;

    matching.sort((a, b) => {
        const left = a[sortField];
        const right = b[sortField];
        if (typeof left === "number" && typeof right === "number") {
            return (left - right) * direction;
        }
        return String(left ?? "").localeCompare(String(right ?? ""), undefined, {
            numeric: true,
            sensitivity: "base"
        }) * direction;
    });

    const pageCount = Math.max(1, Math.ceil(matching.length / options.pageSize));
    const page = Math.min(Math.max(1, options.page), pageCount);
    const start = (page - 1) * options.pageSize;

    return {
        matching,
        pageItems: matching.slice(start, start + options.pageSize),
        page,
        pageCount
    };
}
