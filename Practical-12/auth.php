<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function requireAdmin(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /Practical-10/login.php', true, 303);
        exit;
    }

    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied. This page is restricted to administrators.');
    }
}

function csrfValue(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function eventAudit(string $action, int $eventId, string $details): void
{
    $auditPath = dirname(__DIR__) . '/Practical-14/audit.php';
    if (is_file($auditPath)) {
        require_once $auditPath;
        writeAuditLog('event.' . $action, 'event', $eventId, $details);
    }
}
