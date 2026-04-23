<?php
require_once "auth.php";

requireLogin($conn);

$email = $_SESSION['user_email'];
$userStmt = mysqli_prepare($conn, "SELECT username, name, email, phone, city FROM users WHERE email = ?");

if (!$userStmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($userStmt, "s", $email);
if (!mysqli_stmt_execute($userStmt)) {
    die("User query failed: " . mysqli_stmt_error($userStmt));
}
mysqli_stmt_bind_result($userStmt, $username, $name, $userEmail, $phone, $city);
if (!mysqli_stmt_fetch($userStmt)) {
    mysqli_stmt_close($userStmt);
    logoutUser($conn);
    mysqli_close($conn);
    authAlertRedirect('Your account session is no longer valid. Please log in again.', 'login.html');
}
mysqli_stmt_close($userStmt);

$bookings = [];
$bookingStmt = mysqli_prepare(
    $conn,
    "SELECT
        b.id,
        b.destination,
        b.total_amount,
        b.status,
        p.payment_method,
        p.payment_date
     FROM bookings b
     LEFT JOIN payments p ON b.id = p.booking_id
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC"
);

if ($bookingStmt) {
    $sessionUserId = (int)$_SESSION['user_id'];
    mysqli_stmt_bind_param($bookingStmt, "i", $sessionUserId);
    if (!mysqli_stmt_execute($bookingStmt)) {
        die("Booking query failed: " . mysqli_stmt_error($bookingStmt));
    }
    mysqli_stmt_bind_result(
        $bookingStmt,
        $bookingId,
        $destination,
        $totalAmount,
        $status,
        $paymentMethod,
        $paymentDate
    );

    while (mysqli_stmt_fetch($bookingStmt)) {
        $bookings[] = [
            'booking_id' => $bookingId,
            'destination' => $destination,
            'total_amount' => $totalAmount,
            'status' => $status,
            'payment_method' => $paymentMethod,
            'payment_date' => $paymentDate,
        ];
    }

    mysqli_stmt_close($bookingStmt);
} else {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="navbar">
        <div class="logo">
            <a href="index.html">
                <img src="Saffron_Logo.jpeg" alt="Saffron Tourism Logo">
            </a>
            <h2>Saffron Tourism</h2>
        </div>

        <nav>
            <a href="myaccount.php">My Account</a>
            <a href="index.html#tours">Book</a>
            <a href="Contact.html">Contact Us</a>
        </nav>
    </header>

    <section class="page-hero">
        <div class="page-hero-copy">
            <h1>My Account</h1>
            <p>Manage your profile, bookings, and upcoming travel plans in one place.</p>
        </div>
    </section>

    <div class="auth-container page-auth-container">
        <div class="auth-box account-box">
            <h2>My Account</h2>

            <div class="account-info">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail ?? $email, ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>City:</strong> <?php echo htmlspecialchars($city ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="account-actions">
                <a href="edit_profile.php" class="auth-btn secondary-btn">Edit Profile</a>
                <form action="logout.php" method="POST" class="logout-form">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>

            <div class="booking-history">
                <h3 class="booking-title">My Bookings</h3>
                <?php if (count($bookings) === 0): ?>
                    <p class="empty-state">No bookings yet. Your next trip can start here.</p>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card">
                            <p><strong>Booking ID:</strong> #ST-<?php echo htmlspecialchars(str_pad((string)$booking['booking_id'], 5, '0', STR_PAD_LEFT), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Destination:</strong> <?php echo htmlspecialchars($booking['destination'] ?: 'Tour Booking', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Amount:</strong> Rs. <?php echo htmlspecialchars(number_format((float)$booking['total_amount'], 2), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($booking['status'] ?: 'CONFIRMED', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Payment:</strong> <?php echo htmlspecialchars($booking['payment_method'] ?: 'Pending', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Payment Date:</strong> <?php echo htmlspecialchars($booking['payment_date'] ?: 'Pending', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
