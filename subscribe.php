<?php
include "db.php";

function normalizeEmail(string $email): string
{
    return strtolower(trim($email));
}

function isValidSubscriptionEmail(string $email): bool
{
    if ($email === '' || strlen($email) > 150) {
        return false;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    return (bool)preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email);
}

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
$email = normalizeEmail($_POST["email"] ?? "");

if (!isValidSubscriptionEmail($email)) {
    alertAndRedirect("Please enter a valid email address.", $redirectTarget);
}

$createTableSql = <<<'SQL'
CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active','unsubscribed') DEFAULT 'active',
    source VARCHAR(50) DEFAULT 'website',
    CONSTRAINT chk_subscribers_email CHECK (email REGEXP '^[^[:space:]@]+@[^[:space:]@]+\\.[^[:space:]@]+$')
)
SQL;

if (!$conn->query($createTableSql)) {
    $dbError = $conn->error;
    $conn->close();
    alertAndRedirect("Subscription setup failed: " . $dbError, $redirectTarget);
}

$stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");

if (!$stmt) {
    $dbError = $conn->error;
    $conn->close();
    alertAndRedirect("Unable to process your subscription right now: " . $dbError, $redirectTarget);
}

$stmt->bind_param("s", $email);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    alertAndRedirect("Subscribed successfully! You will receive our special offers.", $redirectTarget);
}

if ($stmt->errno === 1062 || $conn->errno === 1062) {
    $stmt->close();
    $conn->close();
    alertAndRedirect("This email is already subscribed.", $redirectTarget);
}

$statementError = $stmt->error;
$stmt->close();
$conn->close();
alertAndRedirect("Subscription failed: " . $statementError, $redirectTarget);
