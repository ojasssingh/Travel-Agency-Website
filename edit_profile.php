<?php
require_once "auth.php";

requireLogin($conn);

$email = $_SESSION['user_email'];

$stmt = mysqli_prepare($conn, "SELECT username, name, email, phone, city FROM users WHERE email = ?");
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $email);
if (!mysqli_stmt_execute($stmt)) {
    die("Profile query failed: " . mysqli_stmt_error($stmt));
}

mysqli_stmt_bind_result($stmt, $username, $name, $userEmail, $phone, $city);
if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    logoutUser($conn);
    mysqli_close($conn);
    authAlertRedirect('Your account could not be found. Please log in again.', 'login.html');
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box account-box edit-profile-box">
            <p class="auth-kicker">Manage your details</p>
            <h2>Edit Profile</h2>
            <p class="auth-subtitle">Keep your account information up to date for quicker bookings and a smoother trip planning experience.</p>

            <form action="update_profile.php" method="POST" class="profile-form">
                <div class="field-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        readonly
                    >
                </div>

                <div class="field-group">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?php echo htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        pattern="[A-Za-z ]{2,50}"
                        title="Full name should contain only letters and spaces"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="field-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($userEmail ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="field-group">
                    <label for="phone">Phone Number</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="<?php echo htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        inputmode="numeric"
                        pattern="[0-9]{10}"
                        maxlength="10"
                        title="Phone number should contain exactly 10 digits"
                        autocomplete="tel"
                    >
                </div>

                <div class="field-group">
                    <label for="city">City</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="<?php echo htmlspecialchars($city ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        pattern="[A-Za-z ]{2,50}"
                        title="City should contain only letters and spaces"
                        autocomplete="address-level2"
                    >
                </div>

                <div class="profile-actions">
                    <button type="submit" class="auth-btn">Save Changes</button>
                    <a href="myaccount.php" class="auth-btn secondary-btn profile-back-btn">Back to Account</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
