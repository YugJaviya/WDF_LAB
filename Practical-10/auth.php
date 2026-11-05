<?php
declare(strict_types=1);

const SESSION_TIMEOUT = 900;
const REMEMBER_COOKIE = 'studenthub_remember';
const REMEMBER_DAYS = 30;

$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/db.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validCsrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function setRememberCookie(string $value, int $expires): void
{
    global $isHttps;
    setcookie(REMEMBER_COOKIE, $value, [
        'expires' => $expires,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function clearRememberCookie(): void
{
    setRememberCookie('', time() - 3600);
    unset($_COOKIE[REMEMBER_COOKIE]);
}

function issueRememberToken(int $userId): void
{
    global $mysqli;
    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $validator);
    $expires = time() + (REMEMBER_DAYS * 86400);
    $expiresAt = date('Y-m-d H:i:s', $expires);

    $statement = $mysqli->prepare(
        'INSERT INTO remember_tokens (selector, user_id, token_hash, expires_at)
         VALUES (?, ?, ?, ?)'
    );
    $statement->bind_param('siss', $selector, $userId, $tokenHash, $expiresAt);
    $statement->execute();
    $statement->close();

    setRememberCookie($selector . ':' . $validator, $expires);
}

function revokeRememberToken(): void
{
    global $mysqli;
    $cookie = $_COOKIE[REMEMBER_COOKIE] ?? '';
    if (is_string($cookie) && preg_match('/^([a-f0-9]{32}):[a-f0-9]{64}$/', $cookie, $matches)) {
        $statement = $mysqli->prepare('DELETE FROM remember_tokens WHERE selector = ?');
        $statement->bind_param('s', $matches[1]);
        $statement->execute();
        $statement->close();
    }
    clearRememberCookie();
}

function establishLogin(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['user_id'];
    $_SESSION['username'] = (string) $user['username'];
    $_SESSION['full_name'] = (string) $user['full_name'];
    $_SESSION['role'] = (string) $user['role'];
    $_SESSION['last_activity'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function redirectByRole(): never
{
    $destination = ($_SESSION['role'] ?? '') === 'admin'
        ? 'admin_dashboard.php'
        : 'student_dashboard.php';
    header('Location: ' . $destination, true, 303);
    exit;
}

function logoutSession(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}

function requireRole(string $role): void
{
    if (empty($_SESSION['user_id'])) {
        $_SESSION['login_message'] = 'Please log in to continue.';
        header('Location: login.php', true, 303);
        exit;
    }

    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Access denied. Your account does not have permission to view this page.');
    }
}

if (!empty($_SESSION['user_id'])) {
    if (time() - (int) ($_SESSION['last_activity'] ?? 0) > SESSION_TIMEOUT) {
        revokeRememberToken();
        logoutSession();
        session_start();
        $_SESSION['login_message'] = 'Your session expired after 15 minutes of inactivity. Please log in again.';
    } else {
        $_SESSION['last_activity'] = time();
    }
} elseif (!empty($_COOKIE[REMEMBER_COOKIE]) && empty($_SESSION['login_message'])) {
    $cookie = (string) $_COOKIE[REMEMBER_COOKIE];
    if (preg_match('/^([a-f0-9]{32}):([a-f0-9]{64})$/', $cookie, $matches)) {
        $selector = $matches[1];
        $validator = $matches[2];
        $statement = $mysqli->prepare(
            'SELECT rt.user_id, rt.token_hash, rt.expires_at,
                    u.username, u.full_name, u.role
             FROM remember_tokens AS rt
             INNER JOIN users AS u ON u.user_id = rt.user_id
             WHERE rt.selector = ? LIMIT 1'
        );
        $statement->bind_param('s', $selector);
        $statement->execute();
        $rememberedUser = $statement->get_result()->fetch_assoc();
        $statement->close();

        if ($rememberedUser && strtotime($rememberedUser['expires_at']) > time()
            && hash_equals($rememberedUser['token_hash'], hash('sha256', $validator))) {
            $delete = $mysqli->prepare('DELETE FROM remember_tokens WHERE selector = ?');
            $delete->bind_param('s', $selector);
            $delete->execute();
            $delete->close();

            establishLogin($rememberedUser);
            issueRememberToken((int) $rememberedUser['user_id']);
        } else {
            revokeRememberToken();
        }
    } else {
        clearRememberCookie();
    }
}
