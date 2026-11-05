<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

if (empty($_SESSION['user_id'])) {
    $_SESSION['login_message'] = 'Please log in to continue.';
    header('Location: login.php', true, 303);
    exit;
}

redirectByRole();
