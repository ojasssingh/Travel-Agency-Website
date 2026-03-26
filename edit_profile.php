<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$email = $_SESSION['user'];

$stmt = mysqli_prepare($conn, "SELECT name, email, phone, city FROM users WHERE email=?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $name, $userEmail, $phone, $city);
mysqli_stmt_fetch($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
</head>
<body>
    <form action="update_profile.php" method="POST">
        <input type="text" name="name" value="<?php echo $name; ?>" required>
        <input type="email" name="email" value="<?php echo $userEmail; ?>" required>
        <input type="text" name="phone" value="<?php echo $phone; ?>">
        <input type="text" name="city" value="<?php echo $city; ?>">

        <button type="submit">Update</button>
    </form>
</body>
</html>
