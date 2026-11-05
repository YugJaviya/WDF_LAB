<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/storage.php';

$errors = [];
$saved = false;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    $errors[] = 'Open the contact form and submit it using the Send message button.';
} else {
    $postString = static function (string $key): string {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : '';
    };
    $name = trim($postString('name'));
    $email = filter_var(trim($postString('email')), FILTER_SANITIZE_EMAIL);
    $subject = trim($postString('subject'));
    $message = trim($postString('message'));

    if (!preg_match("/^[\\p{L}\\p{M}][\\p{L}\\p{M} .'-]{1,59}$/u", $name)) {
        $errors[] = 'Enter a valid name containing 2 to 60 letters.';
    }
    if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($subject) < 3 || strlen($subject) > 100) {
        $errors[] = 'Subject must contain between 3 and 100 characters.';
    }
    if (strlen($message) < 10 || strlen($message) > 2000) {
        $errors[] = 'Message must contain between 10 and 2000 characters.';
    }

    if (!$errors) {
        $record = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'submitted_at' => date(DATE_ATOM),
        ];
        $saved = save_record('contacts', $record);
        if (!$saved) {
            $errors[] = 'The message could not be saved. Check that the storage folder is writable.';
        }
    }
}

page_start('Contact result');
if ($saved): ?>
    <h2>Message sent</h2>
    <div class="server-alert server-success" role="status">
        <p>Thank you, <?= h($name) ?>. Your message was validated and saved.</p>
    </div>
<?php else: ?>
    <h2>Message needs attention</h2>
    <div class="server-alert server-error" role="alert">
        <p>Please correct the following issue<?= count($errors) === 1 ? '' : 's' ?>:</p>
        <ul><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
<nav class="server-links" aria-label="Next steps">
    <a href="contact.html">Return to contact form</a>
    <a href="records.php">View stored records</a>
    <a href="home.html">Student Portal home</a>
</nav>
<?php page_end(); ?>