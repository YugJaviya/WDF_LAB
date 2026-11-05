# Practical 10 — Secure Login and Session Management

## Run with XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Import `database.sql` in phpMyAdmin.
3. Open `http://localhost:8080/Practical-10/login.php`.

The local XAMPP MySQL instance uses port `3307`. `db.php` reads `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` environment variables if configured.

## Sample accounts

- Admin: `admin@studenthub.test` / `Admin@123`
- Student: `student@studenthub.test` / `Student@123`

These accounts are for the classroom demonstration. Change or remove them before using this application outside the local practical environment.

## Features

- Passwords are checked with `password_verify()`.
- Successful login regenerates the session ID.
- `dashboard.php` redirects according to role; each role dashboard independently checks authorization.
- Sessions expire after 15 minutes without activity.
- Logout is a POST request protected by a CSRF token; it removes the remember token and destroys the session.
- Optional remember-me uses a random selector and validator. Only a SHA-256 hash of the validator is stored in MySQL, and the token rotates after use. The browser cookie is HttpOnly and SameSite=Lax; Secure is enabled on HTTPS.
- Successful login updates `last_login`.

## Suggested screenshot and test report

Capture separate screenshots of: successful admin login, successful student login, failed login, logout message, unauthorized access to the other role's dashboard (HTTP 403), and session timeout after 15 minutes without activity. For a quick timeout demonstration during a classroom demo, temporarily lower `SESSION_TIMEOUT` in `auth.php`, then restore it to 900 seconds.
