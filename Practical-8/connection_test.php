<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$studentCode = isset($_GET['student_code']) && is_string($_GET['student_code'])
    ? trim($_GET['student_code'])
    : 'SH101';
$studentCode = substr($studentCode, 0, 20);

$counts = $pdo->query(
    'SELECT
        (SELECT COUNT(*) FROM students) AS students,
        (SELECT COUNT(*) FROM events) AS events,
        (SELECT COUNT(*) FROM registrations) AS registrations'
)->fetch();

$statement = $pdo->prepare(
    'SELECT s.student_code, s.full_name, s.course_code, s.year_of_study,
            e.event_code, e.title, e.event_date, e.venue, r.status, r.registered_at
     FROM registrations AS r
     INNER JOIN students AS s ON s.student_id = r.student_id
     INNER JOIN events AS e ON e.event_id = r.event_id
     WHERE s.student_code = :student_code
     ORDER BY e.event_date'
);
$statement->bindValue(':student_code', $studentCode, PDO::PARAM_STR);
$statement->execute();
$registrations = $statement->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 8 Database Connection</title>
    <style>
        :root { color-scheme: light; --navy: #173b57; --blue: #176b87; --line: #d5e0e7; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 32px 18px; background: #f2f6f8; color: #203342; font: 16px/1.5 Arial, sans-serif; }
        main { max-width: 980px; margin: auto; padding: 28px; background: #fff; border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 8px 24px #173b5712; }
        h1, h2 { color: var(--navy); }
        .status { padding: 12px 16px; border: 1px solid #9ac8a9; border-radius: 8px; background: #eef8f0; color: #1e6133; }
        .counts { display: flex; flex-wrap: wrap; gap: 12px; margin: 18px 0; }
        .count { min-width: 145px; padding: 14px; border: 1px solid var(--line); border-radius: 8px; background: #f9fbfc; }
        .count strong { display: block; color: var(--blue); font-size: 24px; }
        form { display: flex; flex-wrap: wrap; gap: 10px; align-items: end; margin: 20px 0; }
        label { display: grid; gap: 5px; font-weight: 700; }
        input, button { min-height: 42px; padding: 8px 12px; border: 1px solid #9fb1bd; border-radius: 6px; font: inherit; }
        button { border-color: var(--blue); background: var(--blue); color: #fff; cursor: pointer; }
        .record { padding: 14px 0; border-top: 1px solid var(--line); }
        .muted { color: #5d6f7b; }
        a { color: var(--blue); }
        :focus-visible { outline: 3px solid #edaa42; outline-offset: 2px; }
    </style>
</head>
<body>
<main>
    <h1>StudentHub Database Connection</h1>
    <p class="status" role="status">Connected to MySQL successfully using PDO.</p>

    <h2>Seed data summary</h2>
    <div class="counts" aria-label="Database record counts">
        <div class="count"><strong><?= (int)$counts['students'] ?></strong>Students</div>
        <div class="count"><strong><?= (int)$counts['events'] ?></strong>Events</div>
        <div class="count"><strong><?= (int)$counts['registrations'] ?></strong>Registrations</div>
    </div>

    <h2>Prepared statement example</h2>
    <p class="muted">Search registrations by student code. The value is bound as a parameter.</p>
    <form method="get" action="connection_test.php">
        <label for="student_code">Student code
            <input id="student_code" name="student_code" maxlength="20" value="<?= h($studentCode) ?>">
        </label>
        <button type="submit">Search</button>
    </form>

    <section aria-live="polite">
        <?php if ($registrations): ?>
            <?php foreach ($registrations as $record): ?>
                <article class="record">
                    <h3><?= h($record['title']) ?></h3>
                    <p><strong>Student:</strong> <?= h($record['full_name']) ?> (<?= h($record['student_code']) ?>)</p>
                    <p><strong>Course/year:</strong> <?= h($record['course_code']) ?> / <?= (int)$record['year_of_study'] ?></p>
                    <p><strong>Date and venue:</strong> <?= h($record['event_date']) ?> — <?= h($record['venue']) ?></p>
                    <p><strong>Status:</strong> <?= h($record['status']) ?></p>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No registrations were found for student code <strong><?= h($studentCode) ?></strong>.</p>
        <?php endif; ?>
    </section>
    <p class="muted">This page uses a PDO prepared statement for the student-code search.</p>
</main>
</body>
</html>