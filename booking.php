<?php
require_once "auth.php";

requireLogin($conn);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

$priceMap = [
    "Kashmir" => 50000,
    "Kerala" => 45000,
    "Andaman" => 40000,
    "Rajasthan" => 35000,
    "UttarPradesh" => 30000,
];

$destination = trim($_GET["tour"] ?? $_SESSION["destination"] ?? "");

if ($destination === "Uttar Pradesh") {
    $destination = "UttarPradesh";
}

if ($destination !== "") {
    $_SESSION["destination"] = $destination;
    $_SESSION["travel_date_start"] = $_GET["travel_date_start"] ?? $_SESSION["travel_date_start"] ?? null;
    $_SESSION["travelers"] = $_GET["travelers"] ?? $_SESSION["travelers"] ?? 1;
    $_SESSION["price"] = isset($_GET["price"]) && (float)$_GET["price"] > 0
        ? (float)$_GET["price"]
        : ($priceMap[$destination] ?? 30000);
}

if (empty($_SESSION["destination"]) || empty($_SESSION["price"])) {
    mysqli_close($conn);
    authRedirect("index.html#tours");
}

$destination = $_SESSION["destination"];
$price = (float)$_SESSION["price"];
$today = date("Y-m-d");
$travelDateStart = $_SESSION["travel_date_start"] ?: $today;
$travelers = (int)($_SESSION["travelers"] ?? 1);
$defaultEndDate = date("Y-m-d", strtotime($travelDateStart . " +5 days"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo e($destination); ?> - Saffron Tourism</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="navbar">
        <div class="logo">
            <a href="index.html">
                <img src="Saffron_Logo.jpeg" alt="Saffron Tourism Logo">
            </a>
            <h2>Saffron Tourism</h2>
        </div>

        <nav>
            <a href="index.html">Home</a>
            <a href="myaccount.php">My Account</a>
            <a href="Contact.html">Contact Us</a>
        </nav>
    </header>

    <section class="auth-container">
        <div class="auth-box booking-box">
            <h2>Book <?php echo e($destination); ?></h2>
            <p class="auth-subtitle">Package amount: Rs. <?php echo e(number_format($price, 2)); ?></p>

            <form method="POST" action="payment.php" id="booking-form">
                <label for="travelers">Travelers</label>
                <input type="number" id="travelers" name="travelers" min="1" max="20" value="<?php echo e((string)$travelers); ?>" required>

                <label for="travel_date_start">Start Date</label>
                <input type="date" id="travel_date_start" name="travel_date_start" min="<?php echo e($today); ?>" value="<?php echo e($travelDateStart); ?>" required>

                <label for="travel_date_end">End Date</label>
                <input type="date" id="travel_date_end" name="travel_date_end" min="<?php echo e($today); ?>" value="<?php echo e($defaultEndDate); ?>" required>

                <label for="accommodation_type">Accommodation</label>
                <select id="accommodation_type" name="accommodation_type" required>
                    <option value="Agency">Agency Arranged</option>
                    <option value="Self">Self Arrange</option>
                </select>

                <div class="booking-price-preview">
                    <h3>Total Price: <span id="totalPrice">Rs. 0.00</span></h3>
                    <p id="discountMsg">Book 3 months early to get 5% discount</p>
                </div>

                <input type="hidden" name="final_amount" id="finalAmount">

                <button type="submit" class="auth-btn">Confirm Booking</button>
            </form>
        </div>
    </section>

    <script>
    const bookingForm = document.getElementById("booking-form");
    const travelersInput = document.getElementById("travelers");
    const startInput = document.getElementById("travel_date_start");
    const endInput = document.getElementById("travel_date_end");
    const accommodationInput = document.getElementById("accommodation_type");
    const totalPrice = document.getElementById("totalPrice");
    const discountMsg = document.getElementById("discountMsg");
    const finalAmount = document.getElementById("finalAmount");

    function calculatePrice() {
        const basePrice = <?php echo json_encode($price); ?>;
        const travelers = parseInt(travelersInput.value, 10) || 1;
        const accommodation = accommodationInput.value;
        const travelDateInput = startInput.value;
        const baseTotal = basePrice * travelers;
        const accommodationCost = accommodation === "Agency" ? 2000 * travelers : 0;
        let discount = 0;

        if (travelDateInput) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const travelDate = new Date(travelDateInput);
            travelDate.setHours(0, 0, 0, 0);

            const diffDays = Math.floor((travelDate - today) / (1000 * 60 * 60 * 24));

            if (diffDays >= 90) {
                discount = 0.05 * baseTotal;
                discountMsg.innerText = "5% Early Booking Discount Applied";
            } else {
                discountMsg.innerText = "Book 3 months early to get 5% discount";
            }
        }

        const total = baseTotal + accommodationCost - discount;
        totalPrice.innerText = "Rs. " + total.toFixed(2);
        finalAmount.value = total.toFixed(2);
    }

    startInput.addEventListener("change", () => {
        endInput.min = startInput.value;
        if (startInput.value) {
            const date = new Date(startInput.value);
            date.setDate(date.getDate() + 5);
            endInput.value = date.toISOString().split("T")[0];
        }
        calculatePrice();
    });

    [travelersInput, startInput, endInput, accommodationInput].forEach((input) => {
        input.addEventListener("input", calculatePrice);
        input.addEventListener("change", calculatePrice);
    });

    bookingForm.addEventListener("submit", (event) => {
        const travelers = parseInt(travelersInput.value, 10);

        if (Number.isNaN(travelers) || travelers < 1 || travelers > 20) {
            alert("Please enter 1 to 20 travelers.");
            event.preventDefault();
            return;
        }

        if (!startInput.value || !endInput.value || endInput.value < startInput.value) {
            alert("Please choose valid travel dates.");
            event.preventDefault();
        }
    });

    calculatePrice();
    </script>
</body>
</html>
