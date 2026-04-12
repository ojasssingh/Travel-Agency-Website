<?php
require_once "auth.php";

requireLogin($conn);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: edit_profile.php");
    exit();
}

$oldEmail = $_SESSION['user_email'];

$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
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

$userStmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, phone = ?, city = ? WHERE email = ?");
if (!$userStmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_begin_transaction($conn);
mysqli_stmt_bind_param($userStmt, "sssss", $name, $email, $phone, $city, $oldEmail);

if (mysqli_stmt_execute($userStmt)) {
    $bookingStmt = mysqli_prepare($conn, "UPDATE bookings SET user_email = ? WHERE user_email = ?");

    if (!$bookingStmt) {
        mysqli_rollback($conn);
        mysqli_stmt_close($userStmt);
        die("Query preparation failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($bookingStmt, "ss", $email, $oldEmail);

    if (!mysqli_stmt_execute($bookingStmt)) {
        $bookingError = mysqli_stmt_error($bookingStmt);
        mysqli_stmt_close($bookingStmt);
        mysqli_stmt_close($userStmt);
        mysqli_rollback($conn);
        die("Booking email update failed: " . $bookingError);
    }

    mysqli_stmt_close($bookingStmt);
    mysqli_commit($conn);
    mysqli_stmt_close($userStmt);
    $_SESSION['user'] = $email;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    mysqli_close($conn);
    header("Location: myaccount.php");
    exit();
} elseif (mysqli_errno($conn) === 1062) {
    mysqli_rollback($conn);
    mysqli_stmt_close($userStmt);
    mysqli_close($conn);
    echo "<script>alert('Email already registered'); window.location.href='edit_profile.php';</script>";
    exit();
} else {
    $updateError = mysqli_stmt_error($userStmt);
    mysqli_rollback($conn);
    mysqli_stmt_close($userStmt);
    mysqli_close($conn);
    echo "Update failed: " . $updateError;
}
?>
