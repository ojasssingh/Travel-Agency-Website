<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: myaccount.php");
    exit();
}
 
// --- Destroy session ---
session_start();
session_unset();
session_destroy();
 
// --- Clear cookies (expire them immediately) ---
$past = time() - 3600; // 1 hour in the past
 
setcookie('user_email', '', [
    'expires'  => $past,
    'path'     => '/',
    'secure'   => true,   // match the flags used when setting the cookie
    'httponly' => true,
    'samesite' => 'Strict'
]);
setcookie('user_name', '', [
    'expires'  => $past,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
 
header("Location: login.html");
exit();
?>