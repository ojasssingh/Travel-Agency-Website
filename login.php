<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "tourism", 3307);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = mysqli_prepare($conn, "SELECT name, email FROM users WHERE email = ? AND password = ?");

if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ss", $email, $password);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $name, $userEmail);

if (mysqli_stmt_fetch($stmt)) {
    $_SESSION['user'] = $email;
    $_SESSION['user_name'] = $name;
    header("Location: myaccount.php");
    exit();
} else {
    echo "<script>alert('Invalid email or password'); window.location.href='login.html';</script>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
