<?php
require_once "db.php";

function isValidUsername(string $username): bool
{
    return (bool)preg_match('/^[a-z]+(?: [a-z]+)*$/', $username);
}

function isValidEmailAddress(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        && (bool)preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email);
}

function isValidDob(string $dob): bool
{
    $parsedDob = DateTime::createFromFormat('Y-m-d', $dob);

    if (!$parsedDob || $parsedDob->format('Y-m-d') !== $dob) {
        return false;
    }

    $today = new DateTime('today');
    if ($parsedDob >= $today) {
        return false;
    }

    return $parsedDob->diff($today)->y >= 16;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit;
}

$username = strtolower(trim(preg_replace('/\s+/', ' ', $_POST['username'] ?? '')));
$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$gender = $_POST['gender'] ?? '';
$dob = $_POST['dob'] ?? '';
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');

if ($username === '' || strlen($username) < 3 || strlen($username) > 30 || !isValidUsername($username)) {
    echo "<script>alert('Username must be 3 to 30 characters, use only lowercase letters, and may contain spaces between words'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

if ($name === '' || !preg_match('/^[A-Za-z ]{2,50}$/', $name)) {
    echo "<script>alert('Full name should contain only letters and spaces'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

if (!isValidEmailAddress($email)) {
    echo "<script>alert('Please enter a valid email address with @ and a proper domain like .com'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

if (strlen($password) < 6 || strlen($password) > 50) {
    echo "<script>alert('Password must be between 6 and 50 characters'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    echo "<script>alert('Phone number should contain exactly 10 digits'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

if (!in_array($gender, ['Male', 'Female'], true)) {
    echo "<script>alert('Please select a valid gender'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

if (!isValidDob($dob)) {
    echo "<script>alert('Date of birth must be a past date and age must be at least 16 years'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

if ($address === '') {
    echo "<script>alert('Address is required'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

if (!preg_match('/^[A-Za-z ]{2,50}$/', $city)) {
    echo "<script>alert('City should contain only letters and spaces'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

if (!preg_match('/^[A-Za-z ]{2,50}$/', $state)) {
    echo "<script>alert('State should contain only letters and spaces'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");

if (!$checkStmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($checkStmt, "ss", $email, $username);
mysqli_stmt_execute($checkStmt);
mysqli_stmt_store_result($checkStmt);

if (mysqli_stmt_num_rows($checkStmt) > 0) {
    mysqli_stmt_close($checkStmt);
    mysqli_close($conn);
    echo "<script>alert('Email or username is already registered'); window.location.href='register.html';</script>";
    exit;
}

mysqli_stmt_close($checkStmt);
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users (username, name, email, password, phone, gender, dob, address, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "ssssssssss",
    $username,
    $name,
    $email,
    $passwordHash,
    $phone,
    $gender,
    $dob,
    $address,
    $city,
    $state
);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Account created successfully'); window.location.href='login.html';</script>";
} elseif (mysqli_errno($conn) === 1062) {
    echo "<script>alert('Email or username is already registered'); window.location.href='register.html';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
