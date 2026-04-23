<?php
require_once "auth.php";

requireLogin($conn);

$bookingId = (int)($_GET["booking_id"] ?? 0);
$userId = (int)$_SESSION["user_id"];

if ($bookingId < 1) {
    mysqli_close($conn);
    authRedirect("myaccount.php");
}

$stmt = $conn->prepare(
    "SELECT b.destination, b.total_amount, p.payment_method, p.amount, p.status, p.transaction_id
     FROM bookings b
     INNER JOIN payments p ON p.booking_id = b.id
     WHERE b.id = ? AND b.user_id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Receipt query preparation failed: " . $conn->error);
}

$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$stmt->bind_result($destination, $bookingAmount, $paymentMethod, $paidAmount, $paymentStatus, $transactionId);

if (!$stmt->fetch()) {
    $stmt->close();
    mysqli_close($conn);
    authRedirect("myaccount.php");
}

$stmt->close();
$conn->close();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

function money(float $value): string
{
    return "Rs. " . number_format($value, 2);
}

$receiptId = "#ST-" . str_pad((string)$bookingId, 5, "0", STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Saffron Tourism</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="success-shell">
    <header class="success-nav">
        <a href="index.html" class="payment-brand">
            <img src="Saffron_Logo.jpeg" alt="Saffron Tourism Logo">
            <span>Saffron Tourism</span>
        </a>
        <nav>
            <a href="index.html">Home</a>
            <a href="index.html#tours">Tours</a>
            <a href="Contact.html">Contact</a>
            <a href="myaccount.php" class="profile-link" aria-label="My Account">&#9711;</a>
        </nav>
    </header>

    <main class="success-main">
        <section class="success-card">
            <div class="success-mark">&#10003;</div>
            <h1>Payment Successful!</h1>
            <p>Your booking for the <?php echo e($destination ?: "selected destination"); ?> is confirmed.</p>

            <div class="receipt-box">
                <h2>Receipt</h2>
                <div class="receipt-row">
                    <strong>Booking ID:</strong>
                    <span><?php echo e($receiptId); ?></span>
                </div>
                <div class="receipt-row">
                    <strong>Total Amount Paid:</strong>
                    <span><?php echo e(money((float)($paidAmount ?: $bookingAmount))); ?></span>
                </div>
                <div class="receipt-row">
                    <strong>Payment Method:</strong>
                    <span><?php echo e($paymentMethod); ?></span>
                </div>
                <div class="receipt-row">
                    <strong>Transaction ID:</strong>
                    <span><?php echo e($transactionId); ?></span>
                </div>
            </div>

            <div class="next-steps">
                <h2>Next Steps</h2>
                <p><span>&#9993;</span> Check your email for the itinerary</p>
                <p><span>&#9742;</span> Our agent will call you within 24 hours</p>
            </div>

            <div class="success-actions">
                <button type="button" class="invoice-btn" onclick="window.print()">Download Invoice</button>
                <a href="myaccount.php" class="bookings-btn">Go to My Bookings</a>
            </div>
        </section>
    </main>
</body>
</html>
