<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/storage.php';

$errors = [];
$saved = false;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    $errors[] = 'Open the registration form and submit it using the Create account button.';
} else {
    $postString = static function (string $key): string {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : '';
    };
    $name = trim($postString('studentName'));
    $email = filter_var(trim($postString('studentEmail')), FILTER_SANITIZE_EMAIL);
    $mobile = trim($postString('mobileNumber'));
    $password = $postString('newPassword');
    $confirm = $postString('confirmPassword');
    $course = $postString('course');
    $year = $postString('studyYear');
    $gender = $postString('gender');
    $termsAccepted = isset($_POST['acceptTerms']);

    $courses = [
        'computer-engineering',
        'information-technology',
        'electronics',
        'mechanical',
        'civil',
    ];
    $years = ['1', '2', '3', '4'];
    $genders = ['female', 'male', 'other', 'prefer-not-to-say'];

    if (!preg_match("/^[\\p{L}\\p{M}][\\p{L}\\p{M} .'-]{1,49}$/u", $name)) {
        $errors[] = 'Enter a valid name containing 2 to 50 letters.';
    }
    if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
        $errors[] = 'Enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.';
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/', $password)) {
        $errors[] = 'Password must be at least 8 characters and include uppercase, lowercase, a number, and a symbol.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password and confirm password must match.';
    }
    if (!in_array($course, $courses, true)) {
        $errors[] = 'Choose a valid course from the list.';
    }
    if (!in_array($year, $years, true)) {
        $errors[] = 'Choose a valid year of study.';
    }
    if (!in_array($gender, $genders, true)) {
        $errors[] = 'Choose a gender option.';
    }
    if (!$termsAccepted) {
        $errors[] = 'Accept the terms to complete registration.';
    }

    if (!$errors) {
        $record = [
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'course' => $course,
            'year' => $year,
            'gender' => $gender,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'submitted_at' => date(DATE_ATOM),
        ];
        $saved = save_record('registrations', $record);
        if (!$saved) {
            $errors[] = 'The registration could not be saved. Check that the storage folder is writable.';
        }
    }
}

page_start('Registration result');
if ($saved): ?>
    <h2>Registration successful</h2>
    <div class="server-alert server-success" role="status">
        <p>Thank you, <?= h($name) ?>. Your registration was validated and saved.</p>
        <p>Your password is stored as a one-way hash and is never written as plain text.</p>
    </div>
<?php else: ?>
    <h2>Registration needs attention</h2>
    <div class="server-alert server-error" role="alert">
        <p>Please correct the following issue<?= count($errors) === 1 ? '' : 's' ?>:</p>
        <ul><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
<nav class="server-links" aria-label="Next steps">
    <a href="login.html#registrationCard">Return to registration form</a>
    <a href="records.php">View stored records</a>
    <a href="home.html">Student Portal home</a>
</nav>
<?php page_end(); ?>