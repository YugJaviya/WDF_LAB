<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$message = $_SESSION['form_message'] ?? '';
$messageType = $_SESSION['form_message_type'] ?? 'success';
unset($_SESSION['form_message'], $_SESSION['form_message_type']);
$old = $_SESSION['form_old'] ?? ['full_name' => '', 'username' => '', 'email' => ''];
unset($_SESSION['form_old']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub Registration</title>
    <link rel="stylesheet" href="style.css">
    <script src="validation.js" defer></script>
</head>
<body>
<main class="page">
    <section class="card" aria-labelledby="page-title">
        <p class="eyebrow">STUDENTHUB</p>
        <h1 id="page-title">Create your account</h1>
        <p class="intro">Enter your details to register.</p>

        <?php if ($message !== ''): ?>
            <p class="notice <?= h($messageType) ?>" role="status" aria-live="polite">
                <?= h($message) ?>
            </p>
        <?php endif; ?>

        <form id="registration-form" action="register.php" method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">

            <div class="field">
                <label for="full_name">Full name</label>
                <input id="full_name" name="full_name" type="text" autocomplete="name"
                       maxlength="100" required value="<?= h((string) $old['full_name']) ?>"
                       aria-describedby="full_name_error">
                <small class="error" id="full_name_error"></small>
            </div>

            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" autocomplete="username"
                       minlength="3" maxlength="30" pattern="[A-Za-z0-9_]+" required
                       value="<?= h((string) $old['username']) ?>"
                       aria-describedby="username_hint username_error">
                <small class="hint" id="username_hint">3–30 characters: letters, numbers, and underscore.</small>
                <small class="error" id="username_error"></small>
            </div>

            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" autocomplete="email"
                       maxlength="190" required value="<?= h((string) $old['email']) ?>"
                       aria-describedby="email_error">
                <small class="error" id="email_error"></small>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password"
                       minlength="8" maxlength="72" required
                       aria-describedby="password_hint password_error">
                <small class="hint" id="password_hint">At least 8 characters with uppercase, lowercase, and a number.</small>
                <small class="error" id="password_error"></small>
            </div>

            <div class="field">
                <label for="confirm_password">Confirm password</label>
                <input id="confirm_password" name="confirm_password" type="password"
                       autocomplete="new-password" minlength="8" maxlength="72" required
                       aria-describedby="confirm_password_error">
                <small class="error" id="confirm_password_error"></small>
            </div>

            <button type="submit">Register</button>
        </form>
        <p class="footnote">Passwords are stored as secure hashes, never as plain text.</p>
    </section>
</main>
</body>
</html>
