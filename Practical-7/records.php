<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/storage.php';

function read_records(string $filename): array
{
    $path = __DIR__ . '/storage/' . $filename;
    if (!is_file($path)) {
        return [];
    }
    $contents = file_get_contents($path);
    if ($contents === false) {
        return [];
    }
    $records = json_decode($contents, true);
    return is_array($records) ? $records : [];
}

$registrations = read_records('registrations.json');
$contacts = read_records('contacts.json');
page_start('Saved records');
?>
<h2>Saved form records</h2>
<p>Records are read from the JSON files; matching CSV files are also written for the practical submission.</p>
<h3>Student registrations (<?= count($registrations) ?>)</h3>
<?php if (!$registrations): ?>
    <p>No student registrations have been submitted yet.</p>
<?php else: ?>
    <div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;text-align:left">
        <thead><tr><th>Name</th><th>Email</th><th>Mobile</th><th>Course</th><th>Year</th><th>Gender</th><th>Submitted</th></tr></thead>
        <tbody><?php foreach ($registrations as $record): ?>
            <tr>
                <td><?= h((string)($record['name'] ?? '')) ?></td>
                <td><?= h((string)($record['email'] ?? '')) ?></td>
                <td><?= h((string)($record['mobile'] ?? '')) ?></td>
                <td><?= h((string)($record['course'] ?? '')) ?></td>
                <td><?= h((string)($record['year'] ?? '')) ?></td>
                <td><?= h((string)($record['gender'] ?? '')) ?></td>
                <td><?= h((string)($record['submitted_at'] ?? '')) ?></td>
            </tr>
        <?php endforeach; ?></tbody>
    </table></div>
<?php endif; ?>
<h3 style="margin-top:28px">Contact messages (<?= count($contacts) ?>)</h3>
<?php if (!$contacts): ?>
    <p>No contact messages have been submitted yet.</p>
<?php else: ?>
    <div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;text-align:left">
        <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Submitted</th></tr></thead>
        <tbody><?php foreach ($contacts as $record): ?>
            <tr>
                <td><?= h((string)($record['name'] ?? '')) ?></td>
                <td><?= h((string)($record['email'] ?? '')) ?></td>
                <td><?= h((string)($record['subject'] ?? '')) ?></td>
                <td><?= nl2br(h((string)($record['message'] ?? ''))) ?></td>
                <td><?= h((string)($record['submitted_at'] ?? '')) ?></td>
            </tr>
        <?php endforeach; ?></tbody>
    </table></div>
<?php endif; ?>
<nav class="server-links" aria-label="Related pages">
    <a href="login.html#registrationCard">Student registration</a>
    <a href="contact.html">Contact form</a>
    <a href="home.html">Student Portal home</a>
</nav>
<?php page_end(); ?>