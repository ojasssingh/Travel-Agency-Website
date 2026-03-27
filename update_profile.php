<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$oldEmail = $_SESSION['user'];

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$city = trim($_POST['city'] ?? '');

if ($name === '' || !preg_match('/^[A-Za-z ]{2,50}$/', $name)) {
    echo "<script>alert('Full name should contain only letters and spaces'); window.location.href='edit_profile.php';</script>";
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Please enter a valid email address'); window.location.href='edit_profile.php';</script>";
    exit();
}

if ($phone !== '' && !preg_match('/^[0-9]{10}$/', $phone)) {
    echo "<script>alert('Phone number should contain exactly 10 digits'); window.location.href='edit_profile.php';</script>";
    exit();
}

if ($city !== '' && !preg_match('/^[A-Za-z ]{2,50}$/', $city)) {
    echo "<script>alert('City should contain only letters and spaces'); window.location.href='edit_profile.php';</script>";
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE users SET name=?, email=?, phone=?, city=? WHERE email=?");
mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $phone, $city, $oldEmail);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['user'] = $email;
    header("Location: myaccount.php");
    exit();
} elseif (mysqli_errno($conn) === 1062) {
    echo "<script>alert('Email already registered'); window.location.href='edit_profile.php';</script>";
    exit();
} else {
    echo "Update failed";
}
