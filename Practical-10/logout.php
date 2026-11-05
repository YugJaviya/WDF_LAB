<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validCsrf($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Invalid logout request. Return to the dashboard and try again.');
}

revokeRememberToken();
logoutSession();
session_start();
$_SESSION['login_message'] = 'You have been logged out successfully.';
header('Location: login.php', true, 303);
exit;
