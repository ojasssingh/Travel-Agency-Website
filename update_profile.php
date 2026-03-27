<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$oldEmail = $_SESSION['user'];

$name = trim($_POST['name'] ?? '');
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$city = $_POST['city'] ?? '';

if ($name === '' || !preg_match('/^[A-Za-z ]+$/', $name)) {
    echo "<script>alert('Full name should contain only letters and spaces'); window.location.href='edit_profile.php';</script>";
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE users SET name=?, email=?, phone=?, city=? WHERE email=?");
mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $phone, $city, $oldEmail);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['user'] = $email;
    header("Location: myaccount.php");
    exit();
} else {
    echo "Update failed";
}
