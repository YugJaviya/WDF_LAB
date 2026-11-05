<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function writeAuditLog(
    string $action,
    string $targetType,
    ?int $targetId = null,
    string $details = ''
): void {
    global $mysqli;

    $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    $username = isset($_SESSION['username']) ? (string) $_SESSION['username'] : null;
    $role = isset($_SESSION['role']) ? (string) $_SESSION['role'] : null;
    $ipAddress = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    $details = mb_substr($details, 0, 255);

    $statement = $mysqli->prepare(
        'INSERT INTO audit_logs
            (user_id, username, role, action, target_type, target_id, details, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->bind_param(
        'issssiss',
        $userId,
        $username,
        $role,
        $action,
        $targetType,
        $targetId,
        $details,
        $ipAddress
    );
    $statement->execute();
    $statement->close();
}
