<?php
declare(strict_types=1);
session_start();

function redirectWithMessage(string $message, string $type = 'error'): never
{
    $_SESSION['form_message'] = $message;
    $_SESSION['form_message_type'] = $type;
    header('Location: index.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 303);
    exit;
}

$postedToken = $_POST['csrf_token'] ?? '';
if (!is_string($postedToken) || !isset($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
    redirectWithMessage('Your form session expired. Please submit the form again.');
}

$fullName = trim((string) ($_POST['full_name'] ?? ''));
$username = trim((string) ($_POST['username'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');

$_SESSION['form_old'] = [
    'full_name' => $fullName,
    'username' => $username,
    'email' => $email,
];

if ($fullName === '' || strlen($fullName) > 100
    || !preg_match("/^[\\p{L}][\\p{L} .'-]*$/u", $fullName)) {
    redirectWithMessage('Enter a valid full name using letters, spaces, apostrophes, periods, or hyphens.');
}

if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
    redirectWithMessage('Username must be 3–30 characters and use only letters, numbers, or underscore.');
}

if (strlen($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithMessage('Enter a valid email address.');
}

if (strlen($password) < 8 || strlen($password) > 72
    || !preg_match('/[A-Z]/', $password)
    || !preg_match('/[a-z]/', $password)
    || !preg_match('/[0-9]/', $password)) {
    redirectWithMessage('Password must be 8–72 characters and include uppercase, lowercase, and a number.');
}

if (!hash_equals($password, $confirmPassword)) {
    redirectWithMessage('The password and confirmation do not match.');
}

require_once __DIR__ . '/db.php';

try {
    $check = $mysqli->prepare(
        'SELECT user_id FROM users WHERE email = ? OR username = ? LIMIT 1'
    );
    $check->bind_param('ss', $email, $username);
    $check->execute();
    $existingUser = $check->get_result()->fetch_assoc();
    $check->close();

    if ($existingUser) {
        redirectWithMessage('That email address or username is already registered.');
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $mysqli->prepare(
        'INSERT INTO users (full_name, username, email, password_hash) VALUES (?, ?, ?, ?)'
    );
    $insert->bind_param('ssss', $fullName, $username, $email, $passwordHash);
    $insert->execute();
    $insert->close();

    unset($_SESSION['form_old']);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    redirectWithMessage('Registration successful. Your account has been created.', 'success');
} catch (mysqli_sql_exception $exception) {
    error_log('Practical 9 registration failed: ' . $exception->getMessage());

    if ((int) $exception->getCode() === 1062) {
        redirectWithMessage('That email address or username is already registered.');
    }

    redirectWithMessage('Registration could not be completed. Please try again.');
}
