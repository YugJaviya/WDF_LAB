# Practical 11 — Student Management CRUD

## Run with XAMPP

1. Start Apache and MySQL in XAMPP.
2. Import `database.sql` in phpMyAdmin.
3. Open `http://localhost:8080/Practical-11/`.

The database is `studenthub_pr11`. XAMPP MySQL is configured on port `3307`; the credentials can be overridden with `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` environment variables.

## Features

- Add, view, edit, and delete student records.
- Search by student name, email, or student code.
- Filter by course and paginate results (8 records per page).
- Confirm deletion in the browser and verify POST requests with a CSRF token.
- Use prepared MySQLi statements for CRUD, search, filters, and pagination.
- Export the current filtered student list to CSV.
- Seed 20 sample records in `database.sql`.

## Test cases

1. Check that the initial list displays 20 seeded records across multiple pages.
2. Search for a student by full name, email, or student code; verify matching rows.
3. Select a course filter and verify all displayed rows match it.
4. Add a new student with valid values; verify the success message and new row.
5. Edit the new record and verify the updated values.
6. Try adding an existing student code or email; verify a duplicate message.
7. Delete a record and accept the confirmation; verify that it disappears.
8. Start deleting a record and cancel the confirmation; verify that it remains.
9. Export the filtered list and open `students.csv` to check its rows.

## Screenshot suggestions

Capture the student list, add success, edit success, search/filter results, delete confirmation/result, and the downloaded CSV file.
