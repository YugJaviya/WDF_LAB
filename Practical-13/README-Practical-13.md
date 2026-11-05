# Practical 13 — PHP JSON API and Fetch CRUD

Sign in with the Practical 10 admin, then open `http://localhost:8080/Practical-13/`.

The JSON endpoint is `api/events.php`. It uses the `events` table and database from Practical 12. GET returns filtered event data; POST accepts JSON actions `create`, `update`, and `delete`. Responses include a success flag and message, use HTTP status codes, and return JSON. The interface uses Fetch, shows loading/success/error feedback, and debounces search input by 350 ms. Admin access and a session CSRF token are checked server side. Event changes are added to the Practical 14 audit log when available.

Suggested screenshots: event list in the Network tab, JSON GET response, add/update/delete without page reload, and validation/error feedback.
