<?php
require_once "auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    if (attemptAutoLogin($conn) || isset($_SESSION['user_id'])) {
        mysqli_close($conn);
        authRedirect('myaccount.php');
    }

    mysqli_close($conn);
    authRedirect('login.html');
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$rememberMe = isset($_POST['remember_me']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    mysqli_close($conn);
    authAlertRedirect('Please enter a valid email address.', 'login.html');
}

if ($password === '') {
    mysqli_close($conn);
    authAlertRedirect('Password is required.', 'login.html');
}

$stmt = $conn->prepare("SELECT id, username, name, email, password FROM users WHERE email = ? LIMIT 1");

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    $statementError = $stmt->error;
    $stmt->close();
    die("Login query failed: " . $statementError);
}

$stmt->bind_result($id, $username, $name, $userEmail, $passwordHash);
$userFound = $stmt->fetch();
$stmt->close();

if (!$userFound || !password_verify($password, $passwordHash)) {
    mysqli_close($conn);
    authAlertRedirect('Invalid email or password.', 'login.html');
}

setLoggedInUserSession([
    'id' => $id,
    'username' => $username,
    'name' => $name,
    'email' => $userEmail,
]);

if ($rememberMe) {
    createRememberMeToken($conn, (int)$id);
} else {
    forgetRememberedLogin($conn, (int)$id);
}

mysqli_close($conn);
authRedirect('myaccount.php');
?>
