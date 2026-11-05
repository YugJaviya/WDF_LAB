# Practical 14 — Admin Dashboard and Audit Log

Import `database.sql`, sign in through Practical 10, then open `http://localhost:8080/Practical-14/`.

The admin dashboard checks the Practical 10 session role. Administrators see dynamically queried counts for users, students, events, and registrations; latest registrations; and recent audit entries. Student accounts receive a student-only menu and do not see admin statistics. The menu changes according to the signed-in role.

The dashboard reads users from `studenthub_pr10`, students from `studenthub_pr11`, events from `studenthub_pr12`, and registrations from `studenthub_pr8`. Audit records are stored in `studenthub_pr14.audit_logs`. Practical 12 and 13 write event actions to the same audit log. The dashboard page view and manual audit notes are also recorded.

If a data table is missing, import the SQL files from Practicals 8, 10, 11, 12, and 14. Suggested tests: sign in as admin and inspect counts; sign in as student and confirm admin metrics are hidden; create/update/delete an event and verify the audit entry; submit an audit note; capture the dashboard and audit table.
