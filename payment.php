<?php
require_once "auth.php";

requireLogin($conn);

$destination = trim($_SESSION["destination"] ?? "");
$amount = (float)($_SESSION["price"] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $travelers = (int)($_POST["travelers"] ?? 0);
    $travelDateStart = $_POST["travel_date_start"] ?? "";
    $travelDateEnd = $_POST["travel_date_end"] ?? "";
    $accommodationType = trim($_POST["accommodation_type"] ?? "");
    $allowedAccommodation = ["Agency", "Self"];

    if (
        $travelers < 1 ||
        $travelers > 20 ||
        $travelDateStart === "" ||
        $travelDateEnd === "" ||
        $travelDateEnd < $travelDateStart ||
        !in_array($accommodationType, $allowedAccommodation, true)
    ) {
        echo "<script>alert('Please enter valid booking details.'); window.location.href='booking.php';</script>";
        exit;
    }

    $_SESSION["travelers"] = $travelers;
    $_SESSION["travel_date_start"] = $travelDateStart;
    $_SESSION["travel_date_end"] = $travelDateEnd;
    $_SESSION["accommodation_type"] = $accommodationType;
}

$travelers = (int)($_SESSION["travelers"] ?? 0);
$travelDateStart = $_SESSION["travel_date_start"] ?? "";
$travelDateEnd = $_SESSION["travel_date_end"] ?? "";
$accommodationType = $_SESSION["accommodation_type"] ?? "";

if ($destination === "" || $amount <= 0 || $travelers < 1 || $travelDateStart === "" || $travelDateEnd === "" || $accommodationType === "") {
    mysqli_close($conn);
    authRedirect("index.html#tours");
}

$userName = $_SESSION["user_name"] ?? "";
$userEmail = $_SESSION["user_email"] ?? "";
$basePrice = $amount;
$baseTotal = $basePrice * $travelers;
$accommodationCost = $accommodationType === "Agency" ? 2000 * $travelers : 0;
$discount = 0;

try {
    $today = new DateTime("today");
    $travelDate = new DateTime($travelDateStart);
    if ($travelDate >= $today && (int)$today->diff($travelDate)->days >= 90) {
        $discount = 0.05 * $baseTotal;
    }
} catch (Exception $e) {
    $discount = 0;
}

$totalAmount = $baseTotal + $accommodationCost - $discount;

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

function money(float $value): string
{
    return "Rs. " . number_format($value, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Payment - Saffron Tourism</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="payment-shell">
    <main class="payment-frame">
        <header class="payment-topbar">
            <a href="index.html" class="payment-brand">
                <img src="Saffron_Logo.jpeg" alt="Saffron Tourism Logo">
                <span>Saffron Tourism</span>
            </a>
            <nav>
                <a href="myaccount.php">My Account</a>
                <a href="register.html">Register</a>
                <a href="Contact.html">Contact Us</a>
            </nav>
        </header>

        <div class="offer-timer">
            <span class="timer-icon">&#9711;</span>
            <span>Offer ends in <strong id="countdown">15:00</strong></span>
        </div>

        <form method="POST" action="process_payment.php" id="payment-form" class="payment-layout" novalidate>
            <section class="payment-main">
                <h1>Complete Your Payment</h1>

                <input type="hidden" name="payment_method" id="payment-method" value="card">

                <div class="payment-section">
                    <h2>Payment Methods</h2>
                    <div class="method-card">
                        <div class="method-tabs" role="tablist" aria-label="Payment methods">
                            <button type="button" class="method-tab active" data-method="card">Credit/debit card</button>
                            <button type="button" class="method-tab" data-method="upi">UPI</button>
                            <button type="button" class="method-tab" data-method="net_banking">Net Banking</button>
                            <button type="button" class="method-tab" data-method="pay_at_arrival">Pay at Arrival</button>
                        </div>

                        <div class="method-panel active" data-panel="card">
                            <div class="card-icons" aria-hidden="true"><span>VISA</span><span>MC</span><span>AMEX</span></div>
                            <div class="input-group">
                                <input type="text" name="card_number" inputmode="numeric" autocomplete="cc-number" placeholder="Card Number">
                                <div class="error-msg"></div>
                            </div>
                            <div class="input-group">
                                <input type="text" name="card_name" autocomplete="cc-name" placeholder="Card Holder Name">
                                <div class="error-msg"></div>
                            </div>
                            <div class="split-fields">
                                <div class="input-group">
                                    <input type="text" name="card_expiry" placeholder="Expiry Date (MM/YY)">
                                    <div class="error-msg"></div>
                                </div>
                                <div class="input-group">
                                    <input type="password" name="card_cvc" inputmode="numeric" placeholder="CVV/CVC">
                                    <div class="error-msg"></div>
                                </div>
                            </div>
                        </div>

                        <div class="method-panel" data-panel="upi">
                            <div class="input-group">
                                <input type="text" name="upi_id" placeholder="UPI ID (name@bank)">
                                <div class="error-msg"></div>
                            </div>
                            <p>Use any UPI app to approve this demo payment after submission.</p>
                        </div>

                        <div class="method-panel" data-panel="net_banking">
                            <div class="input-group">
                                <select name="bank_name">
                                    <option value="">Select Bank</option>
                                    <option value="State Bank of India">State Bank of India</option>
                                    <option value="HDFC Bank">HDFC Bank</option>
                                    <option value="ICICI Bank">ICICI Bank</option>
                                    <option value="Axis Bank">Axis Bank</option>
                                </select>
                                <div class="error-msg"></div>
                            </div>
                        </div>

                        <div class="method-panel" data-panel="pay_at_arrival">
                            <p>Your booking will be confirmed now and settled with our travel desk at arrival.</p>
                        </div>
                    </div>
                </div>

                <div class="payment-section">
                    <h2>Billing Details</h2>
                    <div class="billing-grid">
                        <div class="input-group">
                            <input type="text" name="billing_name" value="<?php echo e($userName); ?>" placeholder="Full Name" required>
                            <div class="error-msg"></div>
                        </div>
                        <div class="input-group">
                            <input type="email" name="billing_email" value="<?php echo e($userEmail); ?>" placeholder="Email Address" required>
                            <div class="error-msg"></div>
                        </div>
                        <div class="input-group">
                            <input type="tel" name="billing_phone" placeholder="Phone Number" required>
                            <div class="error-msg"></div>
                        </div>
                    </div>
                </div>

                <div class="payment-actions">
                    <button type="submit" id="pay-button" class="pay-button">Proceed to Pay</button>
                    <a href="booking.php?tour=<?php echo e(rawurlencode($destination)); ?>&price=<?php echo e((string)$amount); ?>" class="back-button">Back to Booking</a>
                </div>
            </section>

            <aside class="summary-card">
                <div class="summary-head">
                    <span class="plane-icon">&#9992;</span>
                    <h2>Booking Summary</h2>
                </div>
                <div class="summary-body">
                    <h3><?php echo e($destination); ?> Tour</h3>
                    <p>Dates: <?php echo e(date("d M Y", strtotime($travelDateStart))); ?> - <?php echo e(date("d M Y", strtotime($travelDateEnd))); ?></p>
                    <p>Travelers: <?php echo e((string)$travelers); ?> Adults</p>
                    <p>Stay: <?php echo e($accommodationType === "Agency" ? "Agency Arranged" : "Self Arrange"); ?></p>
                    <hr>
                    <h3>Price Breakdown</h3>
                    <div class="price-row"><span>Base Price:</span><strong><?php echo e(money($basePrice)); ?></strong></div>
                    <div class="price-row"><span>Base Total:</span><strong><?php echo e(money($baseTotal)); ?></strong></div>
                    <div class="price-row"><span>Accommodation:</span><strong><?php echo e(money($accommodationCost)); ?></strong></div>
                    <div class="price-row"><span>Early Discount:</span><strong>- <?php echo e(money($discount)); ?></strong></div>
                    <div class="total-row"><span>Total Price:</span><strong><?php echo e(money($totalAmount)); ?></strong></div>
                </div>
                <div class="guarantee-row">&#9679; Best Price Guarantee</div>
            </aside>
        </form>
    </main>

    <script src="validation.js"></script>
    <script>
    const countdown = document.getElementById("countdown");
    const payButton = document.getElementById("pay-button");
    let remaining = 15 * 60;

    const timer = setInterval(() => {
        remaining -= 1;
        const minutes = String(Math.floor(remaining / 60)).padStart(2, "0");
        const seconds = String(remaining % 60).padStart(2, "0");
        countdown.textContent = minutes + ":" + seconds;

        if (remaining <= 0) {
            clearInterval(timer);
            countdown.textContent = "00:00";
            payButton.disabled = true;
            payButton.textContent = "Offer Expired";
        }
    }, 1000);

    document.querySelectorAll(".method-tab").forEach((tab) => {
        tab.addEventListener("click", () => {
            const method = tab.dataset.method;
            document.getElementById("payment-method").value = method;

            document.querySelectorAll(".method-tab").forEach((item) => item.classList.remove("active"));
            document.querySelectorAll(".method-panel").forEach((panel) => panel.classList.remove("active"));

            tab.classList.add("active");
            document.querySelector('[data-panel="' + method + '"]').classList.add("active");
        });
    });

    document.getElementById("payment-form").addEventListener("submit", (event) => {
        const form = event.currentTarget;
        const phone = form.billing_phone.value.trim();
        const method = document.getElementById("payment-method").value;

        if (!form.billing_name.value.trim() || !form.billing_email.value.trim() || !/^[0-9]{10}$/.test(phone)) {
            alert("Please enter your billing name, email, and a 10 digit phone number.");
            event.preventDefault();
            return;
        }

        if (method === "card") {
            const cardNumber = form.card_number.value.replace(/\D/g, "");
            if (cardNumber.length < 12 || !form.card_name.value.trim() || !/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(form.card_expiry.value.trim()) || !/^[0-9]{3,4}$/.test(form.card_cvc.value.trim())) {
                alert("Please enter valid card details.");
                event.preventDefault();
            }
        } else if (method === "upi" && !/^[a-z0-9._-]{2,}@[a-z]{2,}$/i.test(form.upi_id.value.trim())) {
            alert("Please enter a valid UPI ID.");
            event.preventDefault();
        } else if (method === "net_banking" && !form.bank_name.value) {
            alert("Please select your bank.");
            event.preventDefault();
        }
    });
    </script>
</body>
</html>
