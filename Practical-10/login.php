<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['user_id'])) {
    redirectByRole();
}

$message = (string) ($_SESSION['login_message'] ?? '');
unset($_SESSION['login_message']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'The form expired. Refresh the page and try again.';
    } else {
        $identity = trim((string) ($_POST['identity'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($identity === '' || $password === '') {
            $error = 'Enter your username or email and password.';
        } else {
            $statement = $mysqli->prepare(
                'SELECT user_id, full_name, username, email, password_hash, role
                 FROM users WHERE username = ? OR email = ? LIMIT 1'
            );
            $statement->bind_param('ss', $identity, $identity);
            $statement->execute();
            $user = $statement->get_result()->fetch_assoc();
            $statement->close();

            if ($user && password_verify($password, $user['password_hash'])) {
                establishLogin($user);

                $update = $mysqli->prepare('UPDATE users SET last_login = NOW() WHERE user_id = ?');
                $update->bind_param('i', $user['user_id']);
                $update->execute();
                $update->close();

                if (isset($_POST['remember_me']) && $_POST['remember_me'] === '1') {
                    issueRememberToken((int) $user['user_id']);
                } else {
                    revokeRememberToken();
                }

                redirectByRole();
            }

            $error = 'The username/email or password is incorrect.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="page">
    <section class="card" aria-labelledby="login-title">
        <p class="eyebrow">STUDENTHUB</p>
        <h1 id="login-title">Sign in</h1>
        <p class="intro">Login redirects each account to its role dashboard.</p>

        <?php if ($message !== ''): ?>
            <p class="notice" role="status" aria-live="polite"><?= h($message) ?></p>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <p class="notice error" role="alert"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <div class="field">
                <label for="identity">Username or email</label>
                <input id="identity" name="identity" type="text" autocomplete="username" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <label class="remember">
                <input type="checkbox" name="remember_me" value="1">
                Remember me on this device for 30 days
            </label>
            <button type="submit">Log in</button>
        </form>

        <aside class="credentials" aria-label="Sample login accounts">
            <strong>Sample accounts for this practical</strong>
            <p>Admin: <code>admin@studenthub.test</code> / <code>Admin@123</code></p>
            <p>Student: <code>student@studenthub.test</code> / <code>Student@123</code></p>
        </aside>
    </section>
</main>
</body>
</html>
