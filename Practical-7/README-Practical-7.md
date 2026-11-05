# Practical 7 - PHP Form Processing

This folder continues the Student Portal from Practical 6. Existing HTML, CSS, JavaScript, and JSON project files are carried forward. The registration form now submits to PHP, and a contact form has been added.

## Files added or updated for Practical 7

- `login.html` sends the registration form to `register.php` using POST.
- `register.php` validates registration fields on the server and stores accepted submissions.
- `contact.html` and `contact.php` provide and process a contact form using POST.
- `includes/storage.php` contains shared output escaping and CSV/JSON storage helpers.
- `records.php` displays the stored registration and contact records.
- `storage/registrations.csv`, `storage/registrations.json`, `storage/contacts.csv`, and `storage/contacts.json` are generated data files.
- `storage/.htaccess` blocks direct web requests to stored data when Apache is used.

Passwords are checked on the server and stored only as password hashes. The clear-text password and confirmation are never written to CSV or JSON.

## Run with XAMPP

1. Copy this folder into XAMPP's `htdocs` directory, or configure Apache to serve it.
2. Start Apache in the XAMPP control panel.
3. Open `http://localhost/Practical-7/login.html` for registration or `http://localhost/Practical-7/contact.html` for the contact form.
4. Open `http://localhost/Practical-7/records.php` to see submitted records.

PHP pages will not run if opened directly as `file:///...`. The `storage` folder must be writable by the web server. Stored records are intended for this local practical demo; do not publish the records page on a public server without access control.