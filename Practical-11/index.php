<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/db.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function bindValues(mysqli_stmt $statement, string $types, array &$values): void
{
    if ($types === '') {
        return;
    }
    $references = [$types];
    foreach ($values as $key => &$value) {
        $references[] = &$value;
    }
    call_user_func_array([$statement, 'bind_param'], $references);
}

function setFlash(string $message, string $type): never
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Location: index.php', true, 303);
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$courses = [
    'Computer Engineering',
    'Information Technology',
    'Data Science',
];
$genders = ['Female', 'Male', 'Other', 'Prefer not to say'];
$message = (string) ($_SESSION['flash_message'] ?? '');
$messageType = (string) ($_SESSION['flash_type'] ?? 'success');
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
$formError = '';
$editing = null;
$formValues = [
    'student_code' => '',
    'full_name' => '',
    'email' => '',
    'course' => '',
    'year_of_study' => '',
    'gender' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        setFlash('The form expired. Please try again.', 'error');
    }

    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'delete') {
        $studentId = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$studentId || $studentId < 1) {
            setFlash('Invalid student record.', 'error');
        }
        $delete = $mysqli->prepare('DELETE FROM students WHERE student_id = ?');
        $delete->bind_param('i', $studentId);
        $delete->execute();
        $deleted = $delete->affected_rows;
        $delete->close();
        setFlash($deleted ? 'Student record deleted successfully.' : 'Student record was not found.', $deleted ? 'success' : 'error');
    }

    if ($action === 'save') {
        $studentId = filter_var($_POST['student_id'] ?? 0, FILTER_VALIDATE_INT);
        $formValues = [
            'student_code' => trim((string) ($_POST['student_code'] ?? '')),
            'full_name' => trim((string) ($_POST['full_name'] ?? '')),
            'email' => strtolower(trim((string) ($_POST['email'] ?? ''))),
            'course' => trim((string) ($_POST['course'] ?? '')),
            'year_of_study' => trim((string) ($_POST['year_of_study'] ?? '')),
            'gender' => (string) ($_POST['gender'] ?? ''),
        ];

        $year = filter_var($formValues['year_of_study'], FILTER_VALIDATE_INT);
        if (!preg_match('/^[A-Za-z0-9-]{3,20}$/', $formValues['student_code'])) {
            $formError = 'Student code must be 3–20 letters, numbers, or hyphens.';
        } elseif ($formValues['full_name'] === '' || strlen($formValues['full_name']) > 100
            || !preg_match("/^[\\p{L}][\\p{L} .'-]*$/u", $formValues['full_name'])) {
            $formError = 'Enter a valid full name.';
        } elseif (!filter_var($formValues['email'], FILTER_VALIDATE_EMAIL)
            || strlen($formValues['email']) > 190) {
            $formError = 'Enter a valid email address.';
        } elseif (!in_array($formValues['course'], $courses, true)) {
            $formError = 'Select a valid course.';
        } elseif ($year === false || $year < 1 || $year > 6) {
            $formError = 'Year of study must be from 1 to 6.';
        } elseif (!in_array($formValues['gender'], $genders, true)) {
            $formError = 'Select a valid gender option.';
        } else {
            try {
                if ($studentId && $studentId > 0) {
                    $save = $mysqli->prepare(
                        'UPDATE students
                         SET student_code = ?, full_name = ?, email = ?, course = ?,
                             year_of_study = ?, gender = ?
                         WHERE student_id = ?'
                    );
                    $save->bind_param(
                        'ssssisi',
                        $formValues['student_code'],
                        $formValues['full_name'],
                        $formValues['email'],
                        $formValues['course'],
                        $year,
                        $formValues['gender'],
                        $studentId
                    );
                    $save->execute();
                    $save->close();
                    setFlash('Student record updated successfully.', 'success');
                } else {
                    $save = $mysqli->prepare(
                        'INSERT INTO students
                            (student_code, full_name, email, course, year_of_study, gender)
                         VALUES (?, ?, ?, ?, ?, ?)'
                    );
                    $save->bind_param(
                        'ssssis',
                        $formValues['student_code'],
                        $formValues['full_name'],
                        $formValues['email'],
                        $formValues['course'],
                        $year,
                        $formValues['gender']
                    );
                    $save->execute();
                    $save->close();
                    setFlash('Student record added successfully.', 'success');
                }
            } catch (mysqli_sql_exception $exception) {
                error_log('Practical 11 save failed: ' . $exception->getMessage());
                $formError = (int) $exception->getCode() === 1062
                    ? 'Student code or email already exists.'
                    : 'The student record could not be saved. Please try again.';
            }
        }

        if ($formError !== '') {
            $editing = $studentId && $studentId > 0 ? ['student_id' => $studentId] : null;
        }
    }
}

if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($editId && $editId > 0) {
        $editStatement = $mysqli->prepare('SELECT * FROM students WHERE student_id = ?');
        $editStatement->bind_param('i', $editId);
        $editStatement->execute();
        $editing = $editStatement->get_result()->fetch_assoc();
        $editStatement->close();
        if ($editing) {
            $formValues = $editing;
        }
    }
}

$query = trim((string) ($_GET['q'] ?? ''));
$courseFilter = trim((string) ($_GET['course'] ?? ''));
if (!in_array($courseFilter, $courses, true)) {
    $courseFilter = '';
}
$page = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
$perPage = 8;
$where = [];
$params = [];
$types = '';

if ($query !== '') {
    $where[] = '(full_name LIKE ? OR email LIKE ? OR student_code LIKE ?)';
    $wildcardQuery = '%' . $query . '%';
    array_push($params, $wildcardQuery, $wildcardQuery, $wildcardQuery);
    $types .= 'sss';
}
if ($courseFilter !== '') {
    $where[] = 'course = ?';
    $params[] = $courseFilter;
    $types .= 's';
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $exportStatement = $mysqli->prepare(
        'SELECT student_code, full_name, email, course, year_of_study, gender
         FROM students' . $whereSql . ' ORDER BY full_name'
    );
    bindValues($exportStatement, $types, $params);
    $exportStatement->execute();
    $exportRows = $exportStatement->get_result();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="students.csv"');
    $output = fopen('php://output', 'wb');
    fputcsv($output, ['Student code', 'Full name', 'Email', 'Course', 'Year', 'Gender']);
    while ($row = $exportRows->fetch_assoc()) {
        $safeRow = array_map(static function ($value): string {
            $text = (string) $value;
            return preg_match('/^[=+@-]/', $text) ? "'" . $text : $text;
        }, array_values($row));
        fputcsv($output, $safeRow);
    }
    fclose($output);
    $exportStatement->close();
    exit;
}

$countStatement = $mysqli->prepare('SELECT COUNT(*) AS total FROM students' . $whereSql);
bindValues($countStatement, $types, $params);
$countStatement->execute();
$totalRows = (int) $countStatement->get_result()->fetch_assoc()['total'];
$countStatement->close();
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$listSql = 'SELECT student_id, student_code, full_name, email, course, year_of_study, gender
            FROM students' . $whereSql . ' ORDER BY full_name LIMIT ? OFFSET ?';
$listStatement = $mysqli->prepare($listSql);
$listParams = $params;
$listTypes = $types . 'ii';
$listParams[] = $perPage;
$listParams[] = $offset;
bindValues($listStatement, $listTypes, $listParams);
$listStatement->execute();
$students = $listStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$listStatement->close();

$queryString = http_build_query(array_filter([
    'q' => $query,
    'course' => $courseFilter,
]));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Management | Practical 11</title>
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
</head>
<body>
<main class="shell">
    <header class="page-header">
        <div>
            <p class="eyebrow">STUDENTHUB · PRACTICAL 11</p>
            <h1>Student Management</h1>
            <p class="muted">Add, view, edit, search, filter, and remove student records.</p>
        </div>
        <a class="button secondary" href="?<?= h($queryString) ?>&export=csv">Export CSV</a>
    </header>

    <?php if ($message !== ''): ?>
        <p class="notice <?= h($messageType) ?>" role="status" aria-live="polite">
            <?= h($message) ?>
        </p>
    <?php endif; ?>

    <section class="panel" aria-labelledby="form-title">
        <h2 id="form-title"><?= $editing ? 'Edit student' : 'Add a student' ?></h2>
        <?php if ($formError !== ''): ?>
            <p class="notice error" role="alert"><?= h($formError) ?></p>
        <?php endif; ?>

        <form method="post" action="index.php" class="student-form">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="student_id" value="<?= h((string) ($editing['student_id'] ?? 0)) ?>">

            <div class="field">
                <label for="student_code">Student code</label>
                <input id="student_code" name="student_code" required maxlength="20"
                       pattern="[A-Za-z0-9-]{3,20}" value="<?= h((string) $formValues['student_code']) ?>">
            </div>
            <div class="field">
                <label for="full_name">Full name</label>
                <input id="full_name" name="full_name" required maxlength="100"
                       value="<?= h((string) $formValues['full_name']) ?>">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required maxlength="190"
                       value="<?= h((string) $formValues['email']) ?>">
            </div>
            <div class="field">
                <label for="course_input">Course</label>
                <select id="course_input" name="course" required>
                    <option value="">Choose a course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= h($course) ?>" <?= $formValues['course'] === $course ? 'selected' : '' ?>>
                            <?= h($course) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="year_of_study">Year of study</label>
                <select id="year_of_study" name="year_of_study" required>
                    <option value="">Choose year</option>
                    <?php for ($yearOption = 1; $yearOption <= 6; $yearOption++): ?>
                        <option value="<?= $yearOption ?>" <?= (string) $formValues['year_of_study'] === (string) $yearOption ? 'selected' : '' ?>>
                            Year <?= $yearOption ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="field">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">Choose an option</option>
                    <?php foreach ($genders as $gender): ?>
                        <option value="<?= h($gender) ?>" <?= $formValues['gender'] === $gender ? 'selected' : '' ?>>
                            <?= h($gender) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit"><?= $editing ? 'Save changes' : 'Add student' ?></button>
                <?php if ($editing): ?>
                    <a class="button secondary" href="index.php">Cancel edit</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="panel" aria-labelledby="records-title">
        <div class="records-heading">
            <div>
                <h2 id="records-title">Student records</h2>
                <p class="muted"><?= $totalRows ?> matching record<?= $totalRows === 1 ? '' : 's' ?></p>
            </div>
            <form method="get" action="index.php" class="filters">
                <label for="q">Search name, email, or code</label>
                <input id="q" name="q" type="search" value="<?= h($query) ?>" placeholder="Enter a search term">
                <label for="course_filter">Filter by course</label>
                <select id="course_filter" name="course">
                    <option value="">All courses</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= h($course) ?>" <?= $courseFilter === $course ? 'selected' : '' ?>>
                            <?= h($course) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Apply</button>
                <a class="button secondary" href="index.php">Clear</a>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th><th>Name</th><th>Email</th><th>Course</th>
                        <th>Year</th><th>Gender</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($students): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= h($student['student_code']) ?></td>
                            <td><?= h($student['full_name']) ?></td>
                            <td><?= h($student['email']) ?></td>
                            <td><?= h($student['course']) ?></td>
                            <td><?= (int) $student['year_of_study'] ?></td>
                            <td><?= h($student['gender']) ?></td>
                            <td class="actions">
                                <a href="?edit=<?= (int) $student['student_id'] ?>">Edit</a>
                                <form method="post" action="index.php" class="delete-form" data-confirm="Delete this student record? This action cannot be undone.">
                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="student_id" value="<?= (int) $student['student_id'] ?>">
                                    <button class="link-button danger-text" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="empty">No matching student records were found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="pagination" aria-label="Student record pages">
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page > 1): ?>
                <a class="button secondary" href="?<?= h(http_build_query(array_filter(['q' => $query, 'course' => $courseFilter, 'page' => $page - 1]))) ?>">Previous</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a class="button secondary" href="?<?= h(http_build_query(array_filter(['q' => $query, 'course' => $courseFilter, 'page' => $page + 1]))) ?>">Next</a>
            <?php endif; ?>
        </nav>
    </section>
</main>
</body>
</html>
