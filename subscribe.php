<?php
include "db.php";

function getRedirectTarget(): string
{
    $fallback = "index.html";

    if (!isset($_SERVER["HTTP_REFERER"])) {
        return $fallback;
    }

    $path = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH);

    if (!is_string($path) || $path === "") {
        return $fallback;
    }

    $target = basename($path);

    if ($target === "" || $target === "subscribe.php") {
        return $fallback;
    }

    return $target;
}

function alertAndRedirect(string $message, string $target): void
{
    echo "<script>alert(" . json_encode($message) . "); window.location.href = " . json_encode($target) . ";</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit();
}

$redirectTarget = getRedirectTarget();
$email = trim($_POST["email"] ?? "");

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    alertAndRedirect("Please enter a valid email address.", $redirectTarget);
}

$stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");

if (!$stmt) {
    alertAndRedirect("Unable to process your subscription right now.", $redirectTarget);
}

$stmt->bind_param("s", $email);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    alertAndRedirect("Subscribed successfully! You will receive our special offers.", $redirectTarget);
}

if ($conn->errno === 1062) {
    $stmt->close();
    $conn->close();
    alertAndRedirect("This email is already subscribed.", $redirectTarget);
}

$stmt->close();
$conn->close();
alertAndRedirect("Subscription failed. Please try again.", $redirectTarget);
