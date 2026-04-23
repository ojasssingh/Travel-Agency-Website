<?php
$prices = [
    "Andaman" => 40000,
    "Rajasthan" => 35000,
    "Kashmir" => 50000,
    "Kerala" => 45000,
    "UttarPradesh" => 30000,
    "Uttar Pradesh" => 30000,
];

$destination = trim($_POST["destination"] ?? $_GET["tour"] ?? "");

if ($destination === "Uttar Pradesh") {
    $destination = "UttarPradesh";
}

if (!isset($prices[$destination])) {
    header("Location: index.html#tours", true, 303);
    exit;
}

header(
    "Location: booking.php?tour=" . rawurlencode($destination) . "&price=" . rawurlencode((string)$prices[$destination]),
    true,
    303
);
exit;
?>
