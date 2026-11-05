# Practical 9 — Secure User Registration

## Run with XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Open phpMyAdmin and import `database.sql` (or run it in the SQL tab).
3. Open `http://localhost:8080/Practical-9/` in a browser.

The local setup uses MySQL port `3307`. `db.php` reads `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` environment variables when configured.

## Included files

- `database.sql` creates the `studenthub_pr9` database and a `users` table with unique email and username constraints.
- `db.php` opens a MySQLi connection and sets UTF-8 character encoding.
- `index.php` presents the registration form with labels, accessible field descriptions, and a CSRF token.
- `validation.js` validates fields as the user types and before submission.
- `register.php` validates the POST request again, checks duplicates with a prepared statement, hashes the password with `password_hash()`, and inserts using a prepared statement.
- `style.css` styles the responsive form and validation feedback.

## Test cases

1. Valid registration: use a unique username and email plus a password such as `Campus2026`; expect a success message.
2. Invalid email: submit `not-an-email`; expect an email validation message.
3. Invalid username: submit a username with spaces or fewer than 3 characters; expect a username validation message.
4. Weak password: submit a password without uppercase, lowercase, or a number; expect a password validation message.
5. Mismatched passwords: use different password and confirmation values; expect a mismatch message.
6. Duplicate: submit the same email or username again; expect a duplicate account message.

Passwords are never stored in plain text. To verify, inspect `users.password_hash` in phpMyAdmin; values should begin with a password-hash marker such as `$2y$` or `$2b$`.
