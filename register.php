<?php

$conn = mysqli_connect("localhost", "root", "", "tourism", 3307);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit;
}

$name = $_POST['name'] ?? '';
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone = $_POST['phone'] ?? '';
$gender = $_POST['gender'] ?? '';
$dob = $_POST['dob'] ?? '';
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');

$name = trim($name);
$phone = trim($phone);

if ($name === '' || !preg_match('/^[A-Za-z ]{2,50}$/', $name)) {
    echo "<script>alert('Full name should contain only letters and spaces'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Please enter a valid email address'); window.location.href='register.html';</script>";
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

$parsedDob = DateTime::createFromFormat('Y-m-d', $dob);

if (!$parsedDob || $parsedDob->format('Y-m-d') !== $dob || $dob > date('Y-m-d')) {
    echo "<script>alert('Please select a valid date of birth'); window.location.href='register.html';</script>";
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

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users (name, email, password, phone, gender, dob, address, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "sssssssss", $name, $email, $password, $phone, $gender, $dob, $address, $city, $state);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Account created successfully'); window.location.href='login.html';</script>";
} elseif (mysqli_errno($conn) === 1062) {
    echo "<script>alert('Email already registered'); window.location.href='register.html';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
