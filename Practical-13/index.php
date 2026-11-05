<?php
declare(strict_types=1);
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /Practical-10/login.php', true, 303);
    exit;
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied. This page is restricted to administrators.');
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= $csrf ?>">
    <title>Practical 13 | Fetch API Events</title>
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
</head>
<body>
<main class="shell">
    <header class="page-header">
        <div><p class="eyebrow">STUDENTHUB · PRACTICAL 13</p><h1>Events API</h1>
            <p class="muted">Create, search, edit, and delete events without reloading the page.</p></div>
        <a class="button secondary" href="/Practical-14/">Admin dashboard</a>
    </header>
    <p id="notice" class="notice" role="status" aria-live="polite">Loading events…</p>

    <section class="panel">
        <h2 id="form-title">Add event</h2>
        <form id="event-form" class="event-form">
            <input type="hidden" id="event-id" value="">
            <div class="field"><label for="title">Event title</label><input id="title" maxlength="150" required></div>
            <div class="field"><label for="category">Category</label><select id="category" required><option value="">Choose category</option><option>Academic</option><option>Cultural</option><option>Technical</option><option>Sports</option><option>Other</option></select></div>
            <div class="field"><label for="event-date">Date</label><input id="event-date" type="date" required></div>
            <div class="field"><label for="venue">Venue</label><input id="venue" maxlength="150" required></div>
            <div class="field"><label for="status">Status</label><select id="status"><option value="open">Open</option><option value="closed">Closed</option><option value="draft">Draft</option></select></div>
            <div class="field wide"><label for="description">Description</label><textarea id="description" maxlength="5000" rows="3" required></textarea></div>
            <div class="form-actions"><button id="save-button" type="submit">Add event</button><button id="cancel-edit" class="secondary" type="button" hidden>Cancel edit</button></div>
        </form>
    </section>

    <section class="panel">
        <div class="list-header"><div><h2>Event list</h2><p id="result-count" class="muted"></p></div>
            <form id="search-form" class="filters"><label for="search">Search</label><input id="search" type="search" placeholder="Title, description, venue"><label for="filter-category">Category</label><select id="filter-category"><option value="">All categories</option><option>Academic</option><option>Cultural</option><option>Technical</option><option>Sports</option><option>Other</option></select><label for="filter-status">Status</label><select id="filter-status"><option value="">All statuses</option><option value="open">Open</option><option value="closed">Closed</option><option value="draft">Draft</option></select></form>
        </div>
        <div id="event-list" class="event-grid" aria-live="polite"></div>
    </section>
</main>
</body>
</html>
