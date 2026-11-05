# Practical 12 — Event Management CRUD and Poster Upload

Import `database.sql`, sign in as the Practical 10 admin, then open `http://localhost:8080/Practical-12/`.

The app uses the Practical 10 admin session. The seeded demo admin is `admin@studenthub.test` / `Admin@123`. MySQL uses XAMPP port 3307. Event changes are written to Practical 14's audit log when that database is available.

CRUD operations use MySQLi prepared statements and CSRF checks. Posters accept JPG, PNG, or WebP up to 3 MB. MIME and image data are checked server side; SVG is rejected. Filenames are random and uploads are stored in a protected directory. Updating a poster replaces the previous file, and deleting an event removes its poster.

Suggested tests: add/update/delete an event; upload valid JPG/PNG/WebP; try SVG, a non-image file, and an image over 3 MB; cancel a delete; access as a student and confirm access is denied. Capture the event list, successful CRUD messages, valid upload, and rejected uploads.
