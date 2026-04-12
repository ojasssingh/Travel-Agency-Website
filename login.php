<?php
session_start();
<<<<<<< HEAD
 
$conn = mysqli_connect("localhost", "root", "", "tourism", 3307);
 
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
 
=======
include "db.php";

>>>>>>> eeb3175 (Enhance email subscription functionality with validation and error handling)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}
 
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
 
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Please enter a valid email address'); window.location.href='login.html';</script>";
    mysqli_close($conn);
    exit;
}
 
if ($password === '') {
    echo "<script>alert('Password is required'); window.location.href='login.html';</script>";
    mysqli_close($conn);
    exit;
}
 
$stmt = mysqli_prepare($conn, "SELECT name, email FROM users WHERE email = ? AND password = ?");
 
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}
 
mysqli_stmt_bind_param($stmt, "ss", $email, $password);
if (!mysqli_stmt_execute($stmt)) {
    die("Login query failed: " . mysqli_stmt_error($stmt));
}
mysqli_stmt_bind_result($stmt, $name, $userEmail);
 
if (mysqli_stmt_fetch($stmt)) {
<<<<<<< HEAD
    // --- Session (server-side) ---
    $_SESSION['user']      = $email;
=======
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    $_SESSION['user'] = $email;
>>>>>>> eeb3175 (Enhance email subscription functionality with validation and error handling)
    $_SESSION['user_name'] = $name;
 
    // --- Cookie (client-side, 7-day remember-me) ---
    $cookieExpiry = time() + (7 * 24 * 60 * 60); // 7 days
    setcookie('user_email', $email, [
        'expires'  => $cookieExpiry,
        'path'     => '/',
        'secure'   => true,   // HTTPS only — set false for local dev
        'httponly' => true,   // not accessible via JavaScript
        'samesite' => 'Strict'
    ]);
    setcookie('user_name', $name, [
        'expires'  => $cookieExpiry,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
 
    header("Location: myaccount.php");
    exit();
} else {
    echo "<script>alert('Invalid email or password'); window.location.href='login.html';</script>";
}
 
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
