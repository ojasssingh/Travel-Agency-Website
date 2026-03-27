<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: myaccount.php");
    exit();
}

session_start();
session_unset();
session_destroy();

header("Location: login.html");
exit();
?>
