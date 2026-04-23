<?php
require_once "auth.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["start_booking"])) {
    header("Location: booking.php?tour=Kerala&price=25000", true, 303);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kerala Delight | Saffron Tourism</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root { --star-blue: #005a9c; --star-red: #c0392b; --star-green: #2e7d32; --text-dark: #333; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Poppins', sans-serif; background: #eaddcc; color: var(--text-dark); line-height: 1.6; }
.navbar { position: fixed; top: 0; width: 100%; z-index: 1000; display: flex; justify-content: space-between; align-items: center; padding: 18px 70px; background: #9fb2c3; }
.logo { display: flex; align-items: center; gap: 10px; }
.logo img { width: 70px; border-radius: 10px; }
.logo h2 { font-family: 'Playfair Display', serif; font-size: 28px; color: #1c2733; }
.navbar nav a { margin-left: 35px; text-decoration: none; color: #1c2733; font-weight: 500; }
.navbar nav a:hover { color: var(--star-blue); }
.hero { margin-top: 80px; height: 100vh; background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?q=80&w=1920&auto=format&fit=crop'); background-size: cover; background-position: center; position: relative; color: white; }
.hero-content { position: absolute; bottom: 15%; left: 60px; }
.hero-content h1 { font-size: 85px; line-height: 1; }
.hero-content p { font-size: 28px; margin-top: 14px; }
.hero-book-btn { display: inline-block; margin-top: 25px; padding: 14px 32px; border-radius: 50px; background: linear-gradient(135deg, #ffffff, #e6f2ff); color: var(--star-blue); border: none; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; text-decoration: none; }
.content-wrapper { max-width: 1200px; margin: 0 auto; padding: 60px 20px; }
.tab-menu { display: flex; border-bottom: 2px solid #eee; margin-bottom: 40px; position: sticky; top: 0; background: white; z-index: 5; }
.tab-link { padding: 20px 40px; border: none; background: none; font-weight: 700; cursor: pointer; color: #888; }
.tab-link.active { color: var(--star-blue); border-bottom: 4px solid var(--star-blue); }
.tab-panel { display: none; background: rgba(255,255,255,0.72); padding: 26px; border-radius: 8px; }
.tab-panel.active { display: block; }
h2 { color: var(--star-blue); margin-bottom: 25px; font-size: 32px; }
.itinerary-step { border-left: 3px solid var(--star-red); padding-left: 20px; margin-bottom: 30px; }
.itinerary-step h4 { color: var(--star-red); }
.price-table { width: 100%; border-collapse: collapse; background: white; }
.price-table th, .price-table td { padding: 15px; border: 1px solid #ddd; }
.price-table th { background: #f8f8f8; }
.offer-box { background: #fdf2f2; padding: 30px; border-radius: 8px; margin-bottom: 20px; }
.highlight-badge { display: inline-block; background: #e1f5fe; padding: 5px 12px; border-radius: 4px; font-weight: 700; margin-bottom: 14px; }
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
        <h1>Kerala <br>Delight</h1>
        <p>06 Days Tour</p>
        <a class="hero-book-btn" href="booking.php?tour=Kerala&price=45000">Book Now</a>
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
        <h2>Experience God's Own Country</h2>
        <p>Explore Kochi, Munnar, Thekkady, and Alleppey with a 5 Night / 6 Day journey through hills, spice gardens, and backwaters.</p>
        <br>
        <div class="highlight-badge">TOUR HIGHLIGHTS</div>
        <ul style="margin-left: 20px;">
            <li><strong>Kochi:</strong> Fort Kochi, Chinese fishing nets, and local markets.</li>
            <li><strong>Munnar:</strong> Tea estates, Eravikulam National Park, and hill views.</li>
            <li><strong>Thekkady:</strong> Spice plantation visit and cultural show.</li>
            <li><strong>Alleppey:</strong> Backwater boat ride and relaxed resort stay.</li>
        </ul>
    </div>

    <div id="itinerary" class="tab-panel">
        <h2>6 Days Itinerary</h2>
        <div class="itinerary-step"><h4>Day 1: Arrival in Kochi</h4><p>Check in and explore the waterfront market area.</p></div>
        <div class="itinerary-step"><h4>Day 2: Kochi to Munnar</h4><p>Visit Fort Kochi highlights, then drive to Munnar via waterfalls.</p></div>
        <div class="itinerary-step"><h4>Day 3: Munnar Tour</h4><p>Tea Museum, Eravikulam National Park, Mattupetty Dam, and Echo Point.</p></div>
        <div class="itinerary-step"><h4>Day 4: Munnar to Thekkady</h4><p>Spice plantation, optional elephant experience, and evening show.</p></div>
        <div class="itinerary-step"><h4>Day 5: Thekkady to Alleppey</h4><p>Relax with a serene backwater boat ride.</p></div>
        <div class="itinerary-step"><h4>Day 6: Departure</h4><p>Transfer to Kochi Airport with breakfast included.</p></div>
    </div>

    <div id="prices" class="tab-panel">
        <h2>Kerala Package Pricing</h2>
        <table class="price-table">
            <tr><th>Package Type</th><th>Starting Price</th><th>Inclusions</th></tr>
            <tr><td><strong>Premium Experience</strong></td><td>Rs. 34,900</td><td>Hotels, breakfast and dinner, transfers, and support</td></tr>
            <tr><td><strong>Private Upgrade</strong></td><td>Rs. 42,500</td><td>Private sightseeing, upgraded resorts, and backwater extras</td></tr>
        </table>
    </div>

    <div id="offers" class="tab-panel">
        <h2>Special Saffron Benefits</h2>
        <div class="offer-box"><h3 style="color: var(--star-green);">Exclusive Kerala Perks</h3><p>Traditional Kerala Sadya, tea tasting session, and curated spice market stop.</p></div>
        <div class="offer-box" style="background: #e1f5fe;"><h3 style="color: #0288d1;">Best Price</h3><p>Package includes taxes and end-to-end travel coordination.</p></div>
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
