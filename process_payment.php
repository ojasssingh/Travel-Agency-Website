<?php
require_once "auth.php";

requireLogin($conn);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    authRedirect("payment.php");
}

$destination = trim($_SESSION["destination"] ?? "");
$basePrice = (float)($_SESSION["price"] ?? 0);
$travelers = (int)($_SESSION["travelers"] ?? 1);
$travelDateStart = $_SESSION["travel_date_start"] ?? "";
$travelDateEnd = $_SESSION["travel_date_end"] ?? "";
$accommodationType = trim($_SESSION["accommodation_type"] ?? "");

$paymentMethod = trim($_POST["payment_method"] ?? "");
$billingName = trim(preg_replace("/\s+/", " ", $_POST["billing_name"] ?? ""));
$billingEmail = strtolower(trim($_POST["billing_email"] ?? ""));
$billingPhone = trim($_POST["billing_phone"] ?? "");

$allowedMethods = ["card", "upi", "net_banking", "pay_at_arrival"];
$methodLabels = [
    "card" => "Credit/Debit Card",
    "upi" => "UPI",
    "net_banking" => "Net Banking",
    "pay_at_arrival" => "Pay at Arrival",
];
$allowedAccommodation = ["Agency", "Self"];

if (
    $destination === "" ||
    $basePrice <= 0 ||
    $travelers < 1 ||
    $travelDateStart === "" ||
    $travelDateEnd === "" ||
    $travelDateStart < date("Y-m-d") ||
    $travelDateEnd < $travelDateStart ||
    !in_array($accommodationType, $allowedAccommodation, true) ||
    !in_array($paymentMethod, $allowedMethods, true) ||
    $billingName === "" ||
    strlen($billingName) > 100 ||
    !filter_var($billingEmail, FILTER_VALIDATE_EMAIL) ||
    !preg_match("/^[0-9]{10}$/", $billingPhone)
) {
    echo "<script>alert('Please enter valid payment and billing details.'); window.location.href='payment.php';</script>";
    exit;
}

if ($paymentMethod === "card") {
    $cardNumber = preg_replace("/\D+/", "", $_POST["card_number"] ?? "");
    $cardName = trim($_POST["card_name"] ?? "");
    $expiry = trim($_POST["card_expiry"] ?? "");
    $cvc = trim($_POST["card_cvc"] ?? "");

    if (strlen($cardNumber) < 12 || strlen($cardNumber) > 19 || $cardName === "" || !preg_match("/^(0[1-9]|1[0-2])\/[0-9]{2}$/", $expiry) || !preg_match("/^[0-9]{3,4}$/", $cvc)) {
        echo "<script>alert('Please enter valid card details.'); window.location.href='payment.php';</script>";
        exit;
    }
} elseif ($paymentMethod === "upi") {
    $upiId = strtolower(trim($_POST["upi_id"] ?? ""));

    if (!preg_match("/^[a-z0-9._-]{2,}@[a-z]{2,}$/", $upiId)) {
        echo "<script>alert('Please enter a valid UPI ID.'); window.location.href='payment.php';</script>";
        exit;
    }
} elseif ($paymentMethod === "net_banking") {
    $allowedBanks = ["State Bank of India", "HDFC Bank", "ICICI Bank", "Axis Bank"];
    $bankName = trim($_POST["bank_name"] ?? "");

    if (!in_array($bankName, $allowedBanks, true)) {
        echo "<script>alert('Please select a valid bank.'); window.location.href='payment.php';</script>";
        exit;
    }
}

$baseTotal = $basePrice * $travelers;
$accommodationCost = $accommodationType === "Agency" ? 2000 * $travelers : 0;
$discount = 0;

try {
    $today = new DateTime("today");
    $travelDate = new DateTime($travelDateStart);
    if ($travelDate >= $today && (int)$today->diff($travelDate)->days >= 90) {
        $discount = 0.05 * $baseTotal;
    }
} catch (Exception $e) {
    echo "<script>alert('Please enter a valid travel date.'); window.location.href='booking.php';</script>";
    exit;
}

$amount = $baseTotal + $accommodationCost - $discount;
$userId = (int)$_SESSION["user_id"];

$bookingStmt = $conn->prepare(
    "INSERT INTO bookings
        (user_id, destination, travelers, travel_date_start, travel_date_end, accommodation_type, total_amount)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

if (!$bookingStmt) {
    die("Booking query preparation failed: " . $conn->error);
}

$bookingStmt->bind_param(
    "isisssd",
    $userId,
    $destination,
    $travelers,
    $travelDateStart,
    $travelDateEnd,
    $accommodationType,
    $amount
);

if (!$bookingStmt->execute()) {
    $error = $bookingStmt->error;
    $bookingStmt->close();
    die("Booking save failed: " . $error);
}

$bookingId = $bookingStmt->insert_id;
$bookingStmt->close();

$paymentMethodLabel = $methodLabels[$paymentMethod];
$transactionId = "TXN" . rand(10000, 99999);
$paymentStatus = "SUCCESS";
$paymentStmt = $conn->prepare(
    "INSERT INTO payments (booking_id, user_id, payment_method, amount, status, transaction_id)
     VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$paymentStmt) {
    die("Payment query preparation failed: " . $conn->error);
}

$paymentStmt->bind_param(
    "iisdss",
    $bookingId,
    $userId,
    $paymentMethodLabel,
    $amount,
    $paymentStatus,
    $transactionId
);

if (!$paymentStmt->execute()) {
    $error = $paymentStmt->error;
    $paymentStmt->close();
    die("Payment save failed: " . $error);
}

$paymentStmt->close();
$conn->close();

unset($_SESSION["destination"], $_SESSION["price"], $_SESSION["travelers"], $_SESSION["travel_date_start"], $_SESSION["travel_date_end"], $_SESSION["accommodation_type"]);

header("Location: payment_success.php?booking_id=" . urlencode((string)$bookingId), true, 303);
exit;
?>
