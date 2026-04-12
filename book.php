<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: booking.html", true, 303);
    exit;
}

require_once "auth.php";

requireLogin($conn);

$user = $_SESSION['user_email'];
$destination = trim($_POST['destination'] ?? '');
$date = $_POST['date'] ?? '';
$endDate = $_POST['endDate'] ?? '';
$days = (int)($_POST['days'] ?? 0);
$people = (int)($_POST['people'] ?? 0);
$hotel = trim($_POST['hotel'] ?? 'agency');
$preferences = isset($_POST['pref']) && is_array($_POST['pref']) ? implode(", ", $_POST['pref']) : "";

$allowedDestinations = ["Andaman", "Rajasthan", "Kashmir", "Kerala", "Uttar Pradesh"];
$allowedHotels = ["agency", "self"];
$today = date("Y-m-d");

if (
    !in_array($destination, $allowedDestinations, true) ||
    $date === '' ||
    $date < $today ||
    $days < 1 ||
    $days > 30 ||
    $people < 1 ||
    $people > 250 ||
    !in_array($hotel, $allowedHotels, true)
) {
    echo "<script>alert('Please enter valid booking details'); window.location.href='booking.html';</script>";
    exit;
}

try {
    $startDate = new DateTime($date);
    $calculatedEndDate = clone $startDate;
    $calculatedEndDate->modify("+{$days} days");
    $calculatedEndDate = $calculatedEndDate->format("Y-m-d");
} catch (Exception $e) {
    echo "<script>alert('Invalid date selected'); window.location.href='booking.html';</script>";
    exit;
}

if ($endDate !== '' && $endDate !== $calculatedEndDate) {
    $endDate = $calculatedEndDate;
} else {
    $endDate = $calculatedEndDate;
}

$stmt = $conn->prepare(
    "INSERT INTO bookings (user_email, destination, travel_date, end_date, days, people, hotel_type, preferences)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("ssssiiss", $user, $destination, $date, $endDate, $days, $people, $hotel, $preferences);

if ($stmt->execute()) {
    echo "<script>alert('Booking Successful!'); window.location.href='myaccount.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
