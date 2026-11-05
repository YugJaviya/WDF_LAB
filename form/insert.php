<?php
// Connects to the same database created by db.sql
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "student_portal";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$name    = trim($_POST["name"] ?? "");
$email   = trim($_POST["email"] ?? "");
$contact = trim($_POST["contact"] ?? "");
$city    = trim($_POST["city"] ?? "");

$errors = [];
if ($name === "")                                $errors[] = "Name is required";
if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = "Enter a valid email";
if (!preg_match('/^[6-9]\d{9}$/', $contact))      $errors[] = "Contact must be 10 digits starting 6-9";
if ($city === "")                                $errors[] = "City is required";

if ($errors) {
    foreach ($errors as $error) {
        echo "<p style='color:red'>$error</p>";
    }
    echo "<p><a href='index.html'>Back</a></p>";
    exit;
}

try {
    $sql = "INSERT INTO students (name, email, contact, city) VALUES (?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$name, $email, $contact, $city]);
    echo "<p style='color:green'><b>Registered successfully.</b> Row added to MySQL.</p>";
} catch (PDOException $e) {
    if ($e->getCode() === "23000") {
        echo "<p style='color:red'>That email is already registered.</p>";
    } else {
        echo "<p style='color:red'>Insert failed: " . $e->getMessage() . "</p>";
    }
}

echo "<p><a href='index.html'>Register another</a></p>";