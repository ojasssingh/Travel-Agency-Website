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
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$phone = $_POST['phone'] ?? '';
$gender = $_POST['gender'] ?? '';
$dob = $_POST['dob'] ?? '';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$state = $_POST['state'] ?? '';

$name = trim($name);
$phone = trim($phone);

if ($name === '' || !preg_match('/^[A-Za-z ]+$/', $name)) {
    echo "<script>alert('Full name should contain only letters and spaces'); window.location.href='register.html';</script>";
    mysqli_close($conn);
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    echo "<script>alert('Phone number should contain exactly 10 digits'); window.location.href='register.html';</script>";
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
