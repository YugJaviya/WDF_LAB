# Practical 8 - StudentHub MySQL and PDO

## Files

- `studenthub.sql` creates the `students`, `events`, and `registrations` tables, their primary and foreign keys, indexes, and fictional seed data.
- `db.php` creates a PDO MySQL connection with exception mode, UTF-8, associative results, and native prepared statements.
- `connection_test.php` checks the connection, summarizes the seed records, and demonstrates a parameterized join query.
- `ER-Model.svg` shows the one-to-many relationships through the registrations table.

## Run in XAMPP

1. Start Apache and MySQL from the XAMPP Control Panel.
2. Open phpMyAdmin at `http://localhost/phpmyadmin`.
3. Select **Import**, choose `studenthub.sql`, and import it. The script creates and selects the `studenthub_pr8` database.
4. Open `http://localhost/Practical-8/connection_test.php`.
5. The page should show a successful PDO connection and seed record counts. Search by a sample student code such as `SH101` to see the prepared statement result.

## Database connection settings

`db.php` reads `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` environment variables. If they are not set, it uses the usual local XAMPP defaults: `127.0.0.1`, `studenthub_pr8`, `root`, and a blank password. For any shared or public server, configure a restricted MySQL user and set credentials through the server environment.

## Normalization and relationships

Each table stores facts about one entity. The registration table connects students to events and prevents duplicate student/event pairs with a unique key. Its foreign keys ensure that each registration refers to an existing student and event. One student can have many registrations, and one event can have many registrations.