<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
requireRole('student');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="page">
    <section class="card dashboard">
        <p class="eyebrow">STUDENT AREA</p>
        <h1>Welcome, <?= h((string) $_SESSION['full_name']) ?></h1>
        <p class="intro">You are signed in with the student role.</p>
        <p class="status">Role: <strong><?= h((string) $_SESSION['role']) ?></strong></p>
        <p class="muted">This page checks the session role on the server before showing protected content.</p>
        <form method="post" action="logout.php">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <button type="submit">Log out</button>
        </form>
    </section>
</main>
</body>
</html>
