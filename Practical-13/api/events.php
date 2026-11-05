<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    respond(401, ['success' => false, 'message' => 'Admin login is required.']);
}

require_once dirname(__DIR__) . '/db.php';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$categories = ['Academic', 'Cultural', 'Technical', 'Sports', 'Other'];
$statuses = ['open', 'closed', 'draft'];

try {
    if ($method === 'GET') {
        $search = trim((string) ($_GET['q'] ?? ''));
        $category = trim((string) ($_GET['category'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $sql = 'SELECT event_id, title, category, description, event_date,
                       venue, status, poster_file
                FROM events WHERE 1=1';
        $conditions = [];
        $params = [];
        $types = '';

        if ($search !== '') {
            $conditions[] = '(title LIKE ? OR description LIKE ? OR venue LIKE ?)';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like);
            $types .= 'sss';
        }
        if ($category !== '' && in_array($category, $categories, true)) {
            $conditions[] = 'category = ?';
            $params[] = $category;
            $types .= 's';
        }
        if ($status !== '' && in_array($status, $statuses, true)) {
            $conditions[] = 'status = ?';
            $params[] = $status;
            $types .= 's';
        }
        if ($conditions) {
            $sql .= ' AND ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY event_date DESC, event_id DESC';
        $statement = $mysqli->prepare($sql);
        if ($types !== '') {
            $statement->bind_param($types, ...$params);
        }
        $statement->execute();
        $events = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        $statement->close();
        respond(200, ['success' => true, 'data' => $events]);
    }

    if ($method !== 'POST') {
        header('Allow: GET, POST');
        respond(405, ['success' => false, 'message' => 'Method not allowed.']);
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $token)) {
        respond(403, ['success' => false, 'message' => 'The security token is invalid. Refresh the page.']);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        respond(400, ['success' => false, 'message' => 'Request body must contain valid JSON.']);
    }
    $action = (string) ($input['action'] ?? '');
    $eventId = filter_var($input['event_id'] ?? null, FILTER_VALIDATE_INT);

    if ($action === 'delete') {
        if (!$eventId || $eventId < 1) {
            respond(422, ['success' => false, 'message' => 'A valid event ID is required.']);
        }
        $find = $mysqli->prepare('SELECT poster_file FROM events WHERE event_id = ?');
        $find->bind_param('i', $eventId);
        $find->execute();
        $event = $find->get_result()->fetch_assoc();
        $find->close();
        if (!$event) {
            respond(404, ['success' => false, 'message' => 'Event not found.']);
        }
        $delete = $mysqli->prepare('DELETE FROM events WHERE event_id = ?');
        $delete->bind_param('i', $eventId);
        $delete->execute();
        $delete->close();
        if ($event['poster_file']) {
            $poster = dirname(__DIR__, 2) . '/Practical-12/uploads/' . basename($event['poster_file']);
            if (is_file($poster)) { unlink($poster); }
        }
        $auditPath = dirname(__DIR__, 2) . '/Practical-14/audit.php';
        if (is_file($auditPath)) {
            require_once $auditPath;
            writeAuditLog('event.deleted', 'event', (int) $eventId, 'Deleted through Practical 13 API');
        }
        respond(200, ['success' => true, 'message' => 'Event deleted.']);
    }

    $title = trim((string) ($input['title'] ?? ''));
    $category = trim((string) ($input['category'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $eventDate = trim((string) ($input['event_date'] ?? ''));
    $venue = trim((string) ($input['venue'] ?? ''));
    $status = trim((string) ($input['status'] ?? 'open'));
    $date = DateTime::createFromFormat('!Y-m-d', $eventDate);

    if ($title === '' || mb_strlen($title) > 150) {
        respond(422, ['success' => false, 'message' => 'Enter a title of up to 150 characters.']);
    }
    if (!in_array($category, $categories, true)) {
        respond(422, ['success' => false, 'message' => 'Choose a valid category.']);
    }
    if ($description === '' || mb_strlen($description) > 5000) {
        respond(422, ['success' => false, 'message' => 'Enter a description of up to 5,000 characters.']);
    }
    if (!$date || $date->format('Y-m-d') !== $eventDate) {
        respond(422, ['success' => false, 'message' => 'Enter a valid event date.']);
    }
    if ($venue === '' || mb_strlen($venue) > 150) {
        respond(422, ['success' => false, 'message' => 'Enter a venue of up to 150 characters.']);
    }
    if (!in_array($status, $statuses, true)) {
        respond(422, ['success' => false, 'message' => 'Choose a valid status.']);
    }

    if ($action === 'create') {
        $statement = $mysqli->prepare(
            'INSERT INTO events (title, category, description, event_date, venue, status)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $statement->bind_param('ssssss', $title, $category, $description, $eventDate, $venue, $status);
        $statement->execute();
        $newId = (int) $mysqli->insert_id;
        $statement->close();
        $message = 'Event added successfully.';
    } elseif ($action === 'update') {
        if (!$eventId || $eventId < 1) {
            respond(422, ['success' => false, 'message' => 'A valid event ID is required.']);
        }
        $statement = $mysqli->prepare(
            'UPDATE events SET title = ?, category = ?, description = ?,
             event_date = ?, venue = ?, status = ? WHERE event_id = ?'
        );
        $statement->bind_param('ssssssi', $title, $category, $description, $eventDate, $venue, $status, $eventId);
        $statement->execute();
        $changed = $statement->affected_rows;
        $statement->close();
        if (!$changed) {
            $check = $mysqli->prepare('SELECT event_id FROM events WHERE event_id = ?');
            $check->bind_param('i', $eventId);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            $check->close();
            if (!$exists) { respond(404, ['success' => false, 'message' => 'Event not found.']); }
        }
        $newId = (int) $eventId;
        $message = 'Event updated successfully.';
    } else {
        respond(400, ['success' => false, 'message' => 'Choose create, update, or delete action.']);
    }

    $auditPath = dirname(__DIR__, 2) . '/Practical-14/audit.php';
    if (is_file($auditPath)) {
        require_once $auditPath;
        writeAuditLog('event.' . ($action === 'create' ? 'created' : 'updated'), 'event', $newId, $message);
    }
    respond(200, ['success' => true, 'message' => $message, 'event_id' => $newId]);
} catch (Throwable $exception) {
    error_log('Practical 13 API error: ' . $exception->getMessage());
    respond(500, ['success' => false, 'message' => 'The request could not be completed. Try again.']);
}
