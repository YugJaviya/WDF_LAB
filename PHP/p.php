<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}

// Get and sanitize input
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$message = trim($_POST["message"] ?? "");

$name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
$email = htmlspecialchars($email, ENT_QUOTES, "UTF-8");
$phone = htmlspecialchars($phone, ENT_QUOTES, "UTF-8");
$message = htmlspecialchars($message, ENT_QUOTES, "UTF-8");

// Validation
$errors = [];

if ($name === "") {
    $errors[] = "Name is required.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}

if (!preg_match("/^[0-9]{10}$/", $phone)) {
    $errors[] = "Phone number must contain exactly 10 digits.";
}

if ($message === "") {
    $errors[] = "Message is required.";
}

// Display errors
if (!empty($errors)) {

    echo "<h2>Form Submission Failed</h2>";

    echo "<ul>";

    foreach ($errors as $error) {
        echo "<li>" . $error . "</li>";
    }

    echo "</ul>";

    echo '<a href="index.html">Go Back</a>';

    exit;
}

// CSV file
$file = "registrations.csv";

// Open file safely
$handle = fopen($file, "a");

if ($handle === false) {
    die("Error: Unable to open storage file.");
}

// Lock file before writing
if (flock($handle, LOCK_EX)) {

    // Add header if file is empty
    if (filesize($file) === 0) {
        fputcsv($handle, ["Name", "Email", "Phone", "Message", "Date"]);
    }

    // Prepare record
    $record = [
        $name,
        $email,
        $phone,
        $message,
        date("Y-m-d H:i:s")
    ];

    // Write record
    fputcsv($handle, $record);

    // Unlock file
    flock($handle, LOCK_UN);

    fclose($handle);

    echo "<h2>Registration Successful!</h2>";
    echo "<p>Your data has been stored successfully.</p>";

    echo '<a href="index.html">Submit Another Response</a>';

} else {

    fclose($handle);

    echo "<h2>Error</h2>";
    echo "<p>Unable to lock the storage file.</p>";
}

?>