<?php

$conn = mysqli_connect("localhost", "root", "", "tourism", 3307);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$message = $_POST['message'];

$sql = "INSERT INTO contact_message (name, email, phone, message)
        VALUES ('$name', '$email', '$phone', '$message')";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Message sent successfully!'); window.location.href='Contact.html';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}

?>
