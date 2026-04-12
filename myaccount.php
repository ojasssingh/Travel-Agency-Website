<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$email = $_SESSION['user'];
$userStmt = mysqli_prepare($conn, "SELECT name, email, phone, city FROM users WHERE email = ?");

if (!$userStmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($userStmt, "s", $email);
if (!mysqli_stmt_execute($userStmt)) {
    die("User query failed: " . mysqli_stmt_error($userStmt));
}
mysqli_stmt_bind_result($userStmt, $name, $userEmail, $phone, $city);
if (!mysqli_stmt_fetch($userStmt)) {
    mysqli_stmt_close($userStmt);
    mysqli_close($conn);
    session_unset();
    session_destroy();
    echo "<script>alert('Your account session is no longer valid. Please log in again.'); window.location.href='login.html';</script>";
    exit();
}
mysqli_stmt_close($userStmt);

$bookings = [];
$bookingStmt = mysqli_prepare(
    $conn,
    "SELECT destination, travel_date, end_date, days, people, hotel_type, preferences, created_at, status
     FROM bookings
     WHERE user_email = ?
     ORDER BY created_at DESC"
);

if ($bookingStmt) {
    mysqli_stmt_bind_param($bookingStmt, "s", $email);
    if (!mysqli_stmt_execute($bookingStmt)) {
        die("Booking query failed: " . mysqli_stmt_error($bookingStmt));
    }
    mysqli_stmt_bind_result($bookingStmt, $destination, $travelDate, $endDate, $days, $people, $hotelType, $preferences, $createdAt, $status);

    while (mysqli_stmt_fetch($bookingStmt)) {
        $bookings[] = [
            'destination' => $destination,
            'travel_date' => $travelDate,
            'end_date' => $endDate,
            'days' => $days,
            'people' => $people,
            'hotel_type' => $hotelType,
            'preferences' => $preferences,
            'created_at' => $createdAt,
            'status' => $status,
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
            <a href="booking.html">Book</a>
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
                            <p><strong>Destination:</strong> <?php echo htmlspecialchars($booking['destination'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Start:</strong> <?php echo htmlspecialchars($booking['travel_date'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>End:</strong> <?php echo htmlspecialchars($booking['end_date'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Days:</strong> <?php echo htmlspecialchars((string)$booking['days'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>People:</strong> <?php echo htmlspecialchars((string)$booking['people'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking['hotel_type'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Preferences:</strong> <?php echo htmlspecialchars($booking['preferences'] ?: 'None', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
