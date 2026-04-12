<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: Contact.html");
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    echo "<script>alert('Please fill in all required contact fields correctly'); window.location.href='Contact.html';</script>";
    mysqli_close($conn);
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    echo "<script>alert('Phone number should contain exactly 10 digits'); window.location.href='Contact.html';</script>";
    mysqli_close($conn);
    exit;
}

$stmt = mysqli_prepare($conn, "INSERT INTO contact_message (name, email, phone, message) VALUES (?, ?, ?, ?)");

if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $phone, $message);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Message sent successfully!'); window.location.href='Contact.html';</script>";
} else {
    echo "Error inserting contact_message record: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
