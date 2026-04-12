<?php
require_once "auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    mysqli_close($conn);
    authRedirect('myaccount.php');
}

logoutUser($conn);
mysqli_close($conn);
authRedirect('login.html');
?>
