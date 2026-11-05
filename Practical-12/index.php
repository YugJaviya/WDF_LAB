<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
requireAdmin();
require_once __DIR__ . '/db.php';

$categories = ['Academic', 'Cultural', 'Technical', 'Sports', 'Other'];
$statuses = ['open', 'closed', 'draft'];
$message = (string) ($_SESSION['flash_message'] ?? '');
$messageType = (string) ($_SESSION['flash_type'] ?? 'success');
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
$error = '';
$editing = null;
$values = [
    'title' => '', 'category' => '', 'description' => '',
    'event_date' => '', 'venue' => '', 'status' => 'open',
];

function flash(string $text, string $type = 'success'): never
{
    $_SESSION['flash_message'] = $text;
    $_SESSION['flash_type'] = $type;
    header('Location: index.php', true, 303);
    exit;
}

function validateEvent(array $input, array $categories, array $statuses): string
{
    if ($input['title'] === '' || mb_strlen($input['title']) > 150) {
        return 'Enter an event title of up to 150 characters.';
    }
    if (!in_array($input['category'], $categories, true)) {
        return 'Choose a valid event category.';
    }
    if ($input['description'] === '' || mb_strlen($input['description']) > 5000) {
        return 'Enter a description of up to 5,000 characters.';
    }
    $date = DateTime::createFromFormat('!Y-m-d', $input['event_date']);
    if (!$date || $date->format('Y-m-d') !== $input['event_date']) {
        return 'Enter a valid event date.';
    }
    if ($input['venue'] === '' || mb_strlen($input['venue']) > 150) {
        return 'Enter a venue of up to 150 characters.';
    }
    if (!in_array($input['status'], $statuses, true)) {
        return 'Choose a valid event status.';
    }
    return '';
}

function storePoster(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('The poster upload did not complete. Try again.');
    }
    if ((int) $file['size'] > 3 * 1024 * 1024) {
        throw new RuntimeException('Poster files must be 3 MB or smaller.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $imageInfo = @getimagesize($file['tmp_name']);
    if (!isset($allowed[$mime]) || !$imageInfo || $imageInfo['mime'] !== $mime) {
        throw new RuntimeException('Upload a valid JPG, PNG, or WebP image. SVG files are not accepted.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $target = __DIR__ . '/uploads/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('The poster could not be saved. Check the uploads folder permissions.');
    }
    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrfValue(), $token)) {
        flash('The form expired. Please try again.', 'error');
    }

    $action = (string) ($_POST['action'] ?? '');
    $eventId = filter_var($_POST['event_id'] ?? 0, FILTER_VALIDATE_INT);

    if ($action === 'delete') {
        if (!$eventId || $eventId < 1) {
            flash('Invalid event record.', 'error');
        }
        $find = $mysqli->prepare('SELECT poster_file FROM events WHERE event_id = ?');
        $find->bind_param('i', $eventId);
        $find->execute();
        $old = $find->get_result()->fetch_assoc();
        $find->close();

        $delete = $mysqli->prepare('DELETE FROM events WHERE event_id = ?');
        $delete->bind_param('i', $eventId);
        $delete->execute();
        $deleted = $delete->affected_rows;
        $delete->close();

        if ($deleted) {
            if ($old && $old['poster_file']) {
                $oldPath = __DIR__ . '/uploads/' . basename($old['poster_file']);
                if (is_file($oldPath)) { unlink($oldPath); }
            }
            eventAudit('deleted', (int) $eventId, 'Deleted event record');
            flash('Event deleted successfully.');
        }
        flash('Event was not found.', 'error');
    }

    if ($action === 'save') {
        $values = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'event_date' => trim((string) ($_POST['event_date'] ?? '')),
            'venue' => trim((string) ($_POST['venue'] ?? '')),
            'status' => trim((string) ($_POST['status'] ?? '')),
        ];
        $error = validateEvent($values, $categories, $statuses);
        $newPoster = null;

        if ($error === '') {
            try {
                $newPoster = storePoster($_FILES['poster'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
                if ($eventId && $eventId > 0) {
                    $find = $mysqli->prepare('SELECT poster_file FROM events WHERE event_id = ?');
                    $find->bind_param('i', $eventId);
                    $find->execute();
                    $old = $find->get_result()->fetch_assoc();
                    $find->close();
                    if (!$old) {
                        throw new RuntimeException('Event was not found.');
                    }
                    $poster = $newPoster ?? $old['poster_file'];
                    $update = $mysqli->prepare(
                        'UPDATE events SET title = ?, category = ?, description = ?,
                         event_date = ?, venue = ?, status = ?, poster_file = ?
                         WHERE event_id = ?'
                    );
                    $update->bind_param(
                        'sssssssi', $values['title'], $values['category'],
                        $values['description'], $values['event_date'], $values['venue'],
                        $values['status'], $poster, $eventId
                    );
                    $update->execute();
                    $update->close();
                    if ($newPoster && $old['poster_file']) {
                        $oldPath = __DIR__ . '/uploads/' . basename($old['poster_file']);
                        if (is_file($oldPath)) { unlink($oldPath); }
                    }
                    eventAudit('updated', (int) $eventId, 'Updated event: ' . $values['title']);
                    flash('Event updated successfully.');
                }

                $insert = $mysqli->prepare(
                    'INSERT INTO events (title, category, description, event_date,
                     venue, status, poster_file) VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $insert->bind_param(
                    'sssssss', $values['title'], $values['category'],
                    $values['description'], $values['event_date'], $values['venue'],
                    $values['status'], $newPoster
                );
                $insert->execute();
                $createdId = (int) $mysqli->insert_id;
                $insert->close();
                eventAudit('created', $createdId, 'Created event: ' . $values['title']);
                flash('Event added successfully.');
            } catch (Throwable $exception) {
                if ($newPoster && is_file(__DIR__ . '/uploads/' . $newPoster)) {
                    unlink(__DIR__ . '/uploads/' . $newPoster);
                }
                error_log('Practical 12 event save failed: ' . $exception->getMessage());
                $error = $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'The event could not be saved. Please try again.';
            }
        }

        if ($eventId && $eventId > 0) {
            $editing = ['event_id' => $eventId];
        }
    }
}

if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($editId && $editId > 0) {
        $edit = $mysqli->prepare('SELECT * FROM events WHERE event_id = ?');
        $edit->bind_param('i', $editId);
        $edit->execute();
        $editing = $edit->get_result()->fetch_assoc();
        $edit->close();
        if ($editing) { $values = $editing; }
    }
}

$categoryFilter = trim((string) ($_GET['category'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$sql = 'SELECT * FROM events WHERE 1=1';
$params = [];
$types = '';
if (in_array($categoryFilter, $categories, true)) {
    $sql .= ' AND category = ?'; $params[] = $categoryFilter; $types .= 's';
} else { $categoryFilter = ''; }
if (in_array($statusFilter, $statuses, true)) {
    $sql .= ' AND status = ?'; $params[] = $statusFilter; $types .= 's';
} else { $statusFilter = ''; }
$sql .= ' ORDER BY event_date DESC, event_id DESC';
$list = $mysqli->prepare($sql);
if ($types !== '') { $list->bind_param($types, ...$params); }
$list->execute();
$events = $list->get_result()->fetch_all(MYSQLI_ASSOC);
$list->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 12 | Event Management</title>
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
</head>
<body>
<main class="shell">
    <header class="topbar">
        <div><p class="eyebrow">STUDENTHUB · PRACTICAL 12</p><h1>Event Management</h1>
            <p class="muted">Signed in as <?= h((string) $_SESSION['full_name']) ?> · Admin</p></div>
        <a class="button secondary" href="/Practical-14/">Admin dashboard</a>
    </header>
    <?php if ($message !== ''): ?><p class="notice <?= h($messageType) ?>" role="status"><?= h($message) ?></p><?php endif; ?>
    <?php if ($error !== ''): ?><p class="notice error" role="alert"><?= h($error) ?></p><?php endif; ?>

    <section class="panel">
        <h2><?= $editing ? 'Update event' : 'Add event' ?></h2>
        <form method="post" enctype="multipart/form-data" class="event-form">
            <input type="hidden" name="csrf_token" value="<?= h(csrfValue()) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="event_id" value="<?= (int) ($editing['event_id'] ?? 0) ?>">
            <div class="field"><label for="title">Event title</label><input id="title" name="title" maxlength="150" required value="<?= h((string) $values['title']) ?>"></div>
            <div class="field"><label for="category">Category</label><select id="category" name="category" required><option value="">Select category</option><?php foreach ($categories as $category): ?><option value="<?= h($category) ?>" <?= $values['category'] === $category ? 'selected' : '' ?>><?= h($category) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="event_date">Event date</label><input id="event_date" name="event_date" type="date" required value="<?= h((string) $values['event_date']) ?>"></div>
            <div class="field"><label for="venue">Venue</label><input id="venue" name="venue" maxlength="150" required value="<?= h((string) $values['venue']) ?>"></div>
            <div class="field"><label for="status">Status</label><select id="status" name="status" required><?php foreach ($statuses as $status): ?><option value="<?= h($status) ?>" <?= $values['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="poster">Poster (JPG, PNG, WebP; max 3 MB)</label><input id="poster" name="poster" type="file" accept="image/jpeg,image/png,image/webp"><small>Optional. A new upload replaces the current poster.</small></div>
            <div class="field wide"><label for="description">Description</label><textarea id="description" name="description" maxlength="5000" rows="4" required><?= h((string) $values['description']) ?></textarea></div>
            <div class="actions"><button type="submit"><?= $editing ? 'Save changes' : 'Add event' ?></button><?php if ($editing): ?><a class="button secondary" href="index.php">Cancel</a><?php endif; ?></div>
        </form>
    </section>

    <section class="panel">
        <div class="list-header"><div><h2>Events</h2><p class="muted"><?= count($events) ?> matching event(s)</p></div>
            <form method="get" class="filters"><label for="category_filter">Category</label><select id="category_filter" name="category"><option value="">All categories</option><?php foreach ($categories as $category): ?><option value="<?= h($category) ?>" <?= $categoryFilter === $category ? 'selected' : '' ?>><?= h($category) ?></option><?php endforeach; ?></select><label for="status_filter">Status</label><select id="status_filter" name="status"><option value="">All statuses</option><?php foreach ($statuses as $status): ?><option value="<?= h($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select><button type="submit">Filter</button><a class="button secondary" href="index.php">Clear</a></form>
        </div>
        <div class="event-grid">
        <?php foreach ($events as $event): ?>
            <article class="event-card">
                <?php if ($event['poster_file']): ?><img class="poster" src="uploads/<?= rawurlencode($event['poster_file']) ?>" alt="Poster for <?= h($event['title']) ?>"><?php else: ?><div class="poster placeholder" aria-label="No event poster">Event poster</div><?php endif; ?>
                <div class="event-content"><div class="event-meta"><span><?= h($event['category']) ?></span><span class="badge <?= h($event['status']) ?>"><?= ucfirst(h($event['status'])) ?></span></div>
                    <h3><?= h($event['title']) ?></h3><p><?= h($event['description']) ?></p><p class="muted"><?= h($event['event_date']) ?> · <?= h($event['venue']) ?></p>
                    <div class="row-actions"><a href="?edit=<?= (int) $event['event_id'] ?>">Edit</a><form method="post" class="delete-form" data-confirm="Delete this event and its poster? This cannot be undone."><input type="hidden" name="csrf_token" value="<?= h(csrfValue()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="event_id" value="<?= (int) $event['event_id'] ?>"><button class="text-danger" type="submit">Delete</button></form></div>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$events): ?><p class="empty">No events match this filter.</p><?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
