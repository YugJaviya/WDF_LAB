<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: /Practical-10/login.php', true, 303);
    exit;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$adminMenu = [
    ['label' => 'Dashboard', 'href' => '/Practical-14/'],
    ['label' => 'Students', 'href' => '/Practical-11/'],
    ['label' => 'Events', 'href' => '/Practical-12/'],
    ['label' => 'Events API', 'href' => '/Practical-13/'],
];
$studentMenu = [
    ['label' => 'Student dashboard', 'href' => '/Practical-10/student_dashboard.php'],
];
$menu = $isAdmin ? $adminMenu : $studentMenu;
$stats = null;
$latestRegistrations = [];
$recentAudit = [];
$error = '';
$flash = (string) ($_SESSION['audit_flash'] ?? '');
unset($_SESSION['audit_flash']);

if ($isAdmin) {
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/audit.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(400);
            $error = 'The form expired. Refresh the page and try again.';
        } else {
            $details = trim((string) ($_POST['details'] ?? ''));
            if ($details === '' || mb_strlen($details) > 255) {
                $error = 'Enter an audit note of 1–255 characters.';
            } else {
                writeAuditLog('admin.note', 'dashboard', null, $details);
                $_SESSION['audit_flash'] = 'Audit note saved.';
                header('Location: index.php', true, 303);
                exit;
            }
        }
    } else {
        writeAuditLog('admin.dashboard.viewed', 'dashboard', null, 'Opened admin dashboard');
    }

    try {
        $stats = [
            'users' => (int) $mysqli->query('SELECT COUNT(*) AS total FROM studenthub_pr10.users')->fetch_assoc()['total'],
            'students' => (int) $mysqli->query('SELECT COUNT(*) AS total FROM studenthub_pr11.students')->fetch_assoc()['total'],
            'events' => (int) $mysqli->query('SELECT COUNT(*) AS total FROM studenthub_pr12.events')->fetch_assoc()['total'],
            'registrations' => (int) $mysqli->query('SELECT COUNT(*) AS total FROM studenthub_pr8.registrations')->fetch_assoc()['total'],
        ];

        $registrationQuery = $mysqli->query(
            'SELECT s.full_name, s.student_code, e.title, r.status, r.registered_at
             FROM studenthub_pr8.registrations AS r
             INNER JOIN studenthub_pr8.students AS s ON s.student_id = r.student_id
             INNER JOIN studenthub_pr8.events AS e ON e.event_id = r.event_id
             ORDER BY r.registered_at DESC LIMIT 8'
        );
        $latestRegistrations = $registrationQuery->fetch_all(MYSQLI_ASSOC);

        $auditQuery = $mysqli->query(
            'SELECT username, role, action, target_type, details, created_at
             FROM audit_logs ORDER BY audit_id DESC LIMIT 10'
        );
        $recentAudit = $auditQuery->fetch_all(MYSQLI_ASSOC);
    } catch (mysqli_sql_exception $exception) {
        error_log('Practical 14 dashboard query failed: ' . $exception->getMessage());
        $error = 'Some practical databases are unavailable. Import Practical 8, 10, 11, 12, and 14 SQL files.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isAdmin ? 'Admin Dashboard' : 'Student Menu' ?> | Practical 14</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <a class="brand" href="/Practical-14/">StudentHub</a>
        <p class="role-label"><?= $isAdmin ? 'ADMIN PANEL' : 'STUDENT MENU' ?></p>
        <nav aria-label="Main navigation">
            <?php foreach ($menu as $item): ?>
                <a href="<?= h($item['href']) ?>" <?= $item['label'] === 'Dashboard' ? 'aria-current="page"' : '' ?>>
                    <?= h($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-user">
            <strong><?= h((string) ($_SESSION['full_name'] ?? 'User')) ?></strong>
            <span><?= h((string) ($_SESSION['role'] ?? '')) ?></span>
            <a href="/Practical-10/">Account</a>
        </div>
    </aside>

    <main class="main-content">
        <?php if ($isAdmin): ?>
            <header class="page-header">
                <div><p class="eyebrow">ADMINISTRATION</p><h1>Dashboard</h1>
                    <p class="muted">StudentHub activity and records at a glance.</p></div>
                <span class="admin-pill">Administrator access</span>
            </header>
            <?php if ($flash !== ''): ?><p class="notice success" role="status"><?= h($flash) ?></p><?php endif; ?>
            <?php if ($error !== ''): ?><p class="notice error" role="alert"><?= h($error) ?></p><?php endif; ?>

            <?php if ($stats !== null): ?>
                <section class="stats" aria-label="System statistics">
                    <article class="stat-card"><span>Users</span><strong><?= $stats['users'] ?></strong><small>Practical 10 accounts</small></article>
                    <article class="stat-card"><span>Students</span><strong><?= $stats['students'] ?></strong><small>Practical 11 records</small></article>
                    <article class="stat-card"><span>Events</span><strong><?= $stats['events'] ?></strong><small>Practical 12 events</small></article>
                    <article class="stat-card"><span>Registrations</span><strong><?= $stats['registrations'] ?></strong><small>Practical 8 registrations</small></article>
                </section>

                <section class="panel">
                    <div class="section-heading"><div><h2>Latest registrations</h2><p class="muted">Most recent records from StudentHub.</p></div></div>
                    <div class="table-wrap"><table><thead><tr><th>Student</th><th>Code</th><th>Event</th><th>Status</th><th>Registered</th></tr></thead><tbody>
                    <?php foreach ($latestRegistrations as $registration): ?>
                        <tr><td><?= h($registration['full_name']) ?></td><td><?= h($registration['student_code']) ?></td><td><?= h($registration['title']) ?></td><td><?= h($registration['status']) ?></td><td><?= h($registration['registered_at']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$latestRegistrations): ?><tr><td colspan="5" class="empty">No registrations are available.</td></tr><?php endif; ?>
                    </tbody></table></div>
                </section>

                <section class="panel">
                    <div class="section-heading"><div><h2>Audit log</h2><p class="muted">Recent dashboard and event management activity.</p></div></div>
                    <form method="post" class="note-form"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>"><label for="details">Add an audit note</label><input id="details" name="details" maxlength="255" required placeholder="Describe an admin action"><button type="submit">Record note</button></form>
                    <div class="table-wrap"><table><thead><tr><th>User</th><th>Action</th><th>Target</th><th>Details</th><th>Time</th></tr></thead><tbody>
                    <?php foreach ($recentAudit as $log): ?>
                        <tr><td><?= h((string) ($log['username'] ?? '')) ?></td><td><?= h($log['action']) ?></td><td><?= h($log['target_type']) ?></td><td><?= h((string) ($log['details'] ?? '')) ?></td><td><?= h($log['created_at']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$recentAudit): ?><tr><td colspan="5" class="empty">No audit entries recorded yet.</td></tr><?php endif; ?>
                    </tbody></table></div>
                </section>
            <?php endif; ?>
        <?php else: ?>
            <header class="page-header"><div><p class="eyebrow">STUDENTHUB</p><h1>Student menu</h1><p class="muted">Welcome, <?= h((string) ($_SESSION['full_name'] ?? 'student')) ?>. Admin dashboard data is restricted to admin accounts.</p></div><span class="student-pill">Student access</span></header>
            <section class="panel"><h2>Your available page</h2><p>Use your student dashboard to view your account area.</p><a class="button" href="/Practical-10/student_dashboard.php">Open student dashboard</a></section>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
