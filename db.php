<?php
require_once "config.php";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tourism";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8mb4")) {
    die("Failed to set character set: " . $conn->error);
}
?>
