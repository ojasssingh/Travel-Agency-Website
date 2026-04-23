<?php
require_once "auth.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["start_booking"])) {
    header("Location: booking.php?tour=Kashmir&price=35000", true, 303);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exotic Kashmir with Gulmarg Stay | Saffron Tourism</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root { --star-blue: #005a9c; --star-red: #c0392b; --text-dark: #333; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Poppins', sans-serif; background: #cfddee; color: var(--text-dark); line-height: 1.6; }
.navbar { position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; display: flex; justify-content: space-between; align-items: center; padding: 18px 70px; background: #9fb2c3; }
.logo { display: flex; align-items: center; gap: 10px; }
.logo img { width: 70px; border-radius: 10px; }
.logo h2 { font-family: 'Playfair Display', serif; font-size: 28px; color: #1c2733; letter-spacing: -0.03em; }
.navbar nav a { margin-left: 35px; text-decoration: none; color: #1c2733; font-size: 15px; font-weight: 500; }
.navbar nav a:hover { color: var(--star-blue); }
.hero { margin-top: 80px; height: 100vh; background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1595815771614-ade9d652a65d?auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; position: relative; color: white; }
.hero-content { position: absolute; bottom: 15%; left: 60px; }
.hero-content h1 { font-size: 85px; font-weight: 700; line-height: 1; }
.hero-content p { font-size: 28px; margin-top: 14px; }
.hero-book-btn { display: inline-block; margin-top: 25px; padding: 14px 32px; border-radius: 50px; background: linear-gradient(135deg, #ffffff, #e6f2ff); color: var(--star-blue); border: none; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; text-decoration: none; }
.content-wrapper { max-width: 1200px; margin: 0 auto; padding: 60px 20px; }
.tab-menu { display: flex; border-bottom: 2px solid #eee; margin-bottom: 40px; position: sticky; top: 0; background: white; z-index: 5; }
.tab-link { padding: 20px 40px; border: none; background: none; font-weight: 700; cursor: pointer; color: #888; }
.tab-link.active { color: var(--star-blue); border-bottom: 4px solid var(--star-blue); }
.tab-panel { display: none; background: rgba(255,255,255,0.72); padding: 26px; border-radius: 8px; }
.tab-panel.active { display: block; }
h2 { font-size: 32px; color: var(--star-blue); margin-bottom: 25px; }
.itinerary-step { margin-bottom: 30px; border-left: 3px solid var(--star-red); padding-left: 20px; }
.itinerary-step h4 { color: var(--star-red); }
.price-table { width: 100%; border-collapse: collapse; background: white; }
.price-table th, .price-table td { padding: 15px; border: 1px solid #ddd; }
.price-table th { background: #f8f8f8; }
.offer-box { background: #fdf2f2; padding: 30px; border-radius: 8px; margin-bottom: 20px; }
.highlight-badge { display: inline-block; background: #e1f5fe; padding: 5px 12px; border-radius: 4px; font-size: 12px; font-weight: 700; margin-bottom: 14px; }
@media (max-width: 768px) { .navbar { padding: 16px 24px; flex-direction: column; gap: 12px; } .hero-content { left: 28px; right: 28px; } .hero-content h1 { font-size: 54px; } .tab-menu { overflow-x: auto; } .tab-link { min-width: max-content; } }
</style>
</head>
<body>
<header class="navbar">
    <div class="logo">
        <img src="Saffron_Logo.jpeg" alt="Saffron Tourism Logo">
        <h2>Saffron Tourism</h2>
    </div>
    <nav>
        <a href="index.html">Home</a>
        <a href="myaccount.php">My Account</a>
        <a href="Contact.html">Contact Us</a>
    </nav>
</header>

<div class="hero">
    <div class="hero-content">
        <h1>Exotic <br>Kashmir</h1>
        <p>07 Days Tour</p>
        <a class="hero-book-btn" href="booking.php?tour=Kashmir&price=50000">Book Now</a>
    </div>
</div>

<div class="content-wrapper">
    <div class="tab-menu">
        <button class="tab-link active" onclick="openSection(event, 'overview')">TOUR OVERVIEW</button>
        <button class="tab-link" onclick="openSection(event, 'itinerary')">TOUR ITINERARY</button>
        <button class="tab-link" onclick="openSection(event, 'prices')">DATES & PRICES</button>
        <button class="tab-link" onclick="openSection(event, 'offers')">OFFERS</button>
    </div>

    <div id="overview" class="tab-panel active">
        <h2>Exotic Kashmir With Gulmarg Stay</h2>
        <p>Discover the valley with a 6 Night / 7 Day curated journey featuring a houseboat stay in Srinagar, the meadows of Pahalgam, and an overnight stay in Gulmarg.</p>
        <br>
        <div class="highlight-badge">TOUR HIGHLIGHTS</div>
        <ul style="margin-left: 20px;">
            <li><strong>Srinagar:</strong> Luxury houseboat stay and Shikara ride on Dal Lake.</li>
            <li><strong>Pahalgam:</strong> Betaab Valley, Aru Valley, and Lidder River views.</li>
            <li><strong>Gulmarg:</strong> Gondola Phase 1 experience and meadow walks.</li>
            <li><strong>Heritage:</strong> Mughal Gardens and Martand Sun Temple ruins.</li>
        </ul>
    </div>

    <div id="itinerary" class="tab-panel">
        <h2>7 Days Detailed Itinerary</h2>
        <div class="itinerary-step"><h4>Day 1: Arrival in Srinagar</h4><p>Check in to a traditional houseboat and enjoy a sunset Shikara ride.</p></div>
        <div class="itinerary-step"><h4>Day 2: Srinagar to Pahalgam</h4><p>Visit Pampore saffron fields and Avantipur ruins on the drive to Pahalgam.</p></div>
        <div class="itinerary-step"><h4>Day 3: Pahalgam Sightseeing</h4><p>Explore Aru Valley, Betaab Valley, and Chandanwari.</p></div>
        <div class="itinerary-step"><h4>Day 4: Pahalgam to Gulmarg</h4><p>Drive to Gulmarg with heritage stops and leisure time in the meadows.</p></div>
        <div class="itinerary-step"><h4>Day 5: Gondola and Srinagar</h4><p>Ride the Gulmarg Gondola and return to Srinagar by evening.</p></div>
        <div class="itinerary-step"><h4>Day 6: Sonmarg Excursion</h4><p>Full-day Sonmarg visit with time near Thajiwas Glacier.</p></div>
        <div class="itinerary-step"><h4>Day 7: Departure</h4><p>Visit Shankaracharya Temple and transfer to Srinagar airport.</p></div>
    </div>

    <div id="prices" class="tab-panel">
        <h2>Saffron Package Pricing</h2>
        <table class="price-table">
            <tr><th>Package Category</th><th>Starting Price</th><th>Key Inclusions</th></tr>
            <tr><td><strong>Premium Couple Package</strong></td><td>Rs. 50,400</td><td>Hotels, houseboat, breakfast, transfers, and taxes</td></tr>
            <tr><td><strong>Luxury Upgrade</strong></td><td>Rs. 58,500</td><td>Premium resorts, all meals, private vehicle, Gondola tickets</td></tr>
        </table>
    </div>

    <div id="offers" class="tab-panel">
        <h2>Special Saffron Add-ons</h2>
        <div class="offer-box"><h3 style="color: var(--star-red);">Complimentary Experiences</h3><p>Kashmiri Kahwa welcome drink, traditional attire photography, and guided garden visit.</p></div>
        <div class="offer-box" style="background: #e1f5fe;"><h3 style="color: var(--star-blue);">Best Price</h3><p>This package includes taxes and booking support from Saffron Tourism.</p></div>
    </div>
</div>

<script>
function openSection(evt, sectionName) {
    document.querySelectorAll(".tab-panel").forEach((panel) => panel.classList.remove("active"));
    document.querySelectorAll(".tab-link").forEach((link) => link.classList.remove("active"));
    document.getElementById(sectionName).classList.add("active");
    evt.currentTarget.classList.add("active");
}
</script>
</body>
</html>
