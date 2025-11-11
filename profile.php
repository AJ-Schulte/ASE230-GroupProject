<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];

// ============================
// Database Connection
// ============================
$host = 'localhost';
$dbname = 'collectable_peddlers';
$dbuser = 'root';
$dbpass = ''; // XAMPP default

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ============================
// Load current user info
// ============================
$stmt = $conn->prepare("SELECT user_id, username, name, phone_num, password FROM User WHERE username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$currentUser = $result->fetch_assoc();
$stmt->close();

if (!$currentUser) {
    die("⚠️ User not found in database.");
}

$message = "";

// ============================
// Handle form submissions
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---- Update Username ----
    if (!empty($_POST['new_username'])) {
        $newUsername = trim($_POST['new_username']);

        $checkStmt = $conn->prepare("SELECT user_id FROM User WHERE username = ?");
        $checkStmt->bind_param("s", $newUsername);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = "⚠️ Username already taken.";
        } else {
            $updateStmt = $conn->prepare("UPDATE User SET username = ? WHERE user_id = ?");
            $updateStmt->bind_param("si", $newUsername, $currentUser['user_id']);
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['user'] = $newUsername;
            $user = $newUsername;
            $message = "✅ Username updated successfully.";
        }
        $checkStmt->close();
    }

    // ---- Update Password ----
    if (!empty($_POST['old_password']) && !empty($_POST['new_password'])) {
        $oldPass = trim($_POST['old_password']);
        $newPass = trim($_POST['new_password']);

        if ($oldPass === $currentUser['password']) {
            $updateStmt = $conn->prepare("UPDATE User SET password = ? WHERE user_id = ?");
            $updateStmt->bind_param("si", $newPass, $currentUser['user_id']);
            $updateStmt->execute();
            $updateStmt->close();
            $message = "✅ Password changed successfully.";
        } else {
            $message = "❌ Incorrect old password.";
        }
    }

    // ---- Update Name & Phone ----
    if (isset($_POST['name']) || isset($_POST['phone'])) {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);

        // Handle nulls
        $name = $name === "" ? null : $name;
        $phone = $phone === "" ? null : $phone;

        // Validate phone format if entered
        if ($phone !== null && !preg_match('/^\d{3}-\d{3}-\d{4}$/', $phone)) {
            $message = "❌ Invalid phone format. Use 111-111-1111.";
        } else {
            $updateStmt = $conn->prepare("UPDATE User SET name = ?, phone_num = ? WHERE user_id = ?");
            $updateStmt->bind_param("ssi", $name, $phone, $currentUser['user_id']);
            $updateStmt->execute();
            $updateStmt->close();
            $message = "✅ Profile details updated successfully.";
        }
    }

    // Refresh user info after changes
    $stmt = $conn->prepare("SELECT user_id, username, name, phone_num, password FROM User WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentUser = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your Account — Collectable Peddlers</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <header>
        <a href="/" class="brand">
            <div class="logo">MX</div>
            <div>
                <div class="brand-name">Collectable Peddlers</div>
                <div class="brand-tag">Buy • Sell • Trade — Cards &amp; Collectibles</div>
            </div>
        </a>
        <nav>
            <a href="search.php">Browse</a>
            <a href="new_listing.php">Sell</a>
            <a href="userDash.php">Collections</a>
        </nav>
        <div class="auth">
            <a class="btn btn-outline" href="cart.php">Cart</a>
            <a class="btn btn-outline" href="assets/php/logout.php">Sign out</a>
        </div>
    </header>

    <main>
        <section class="account">
            <h1>Account Settings</h1>

            <?php if ($message): ?>
                <div class="alert"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="card">
                <h2>Change Username</h2>
                <form method="post">
                    <label>Current Username:</label>
                    <input type="text" value="<?= htmlspecialchars($currentUser['username']) ?>" disabled>

                    <label for="new_username">New Username:</label>
                    <input type="text" id="new_username" name="new_username" placeholder="Enter new username">

                    <button type="submit" class="btn btn-primary">Update Username</button>
                </form>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <h2>Change Password</h2>
                <form method="post">
                    <label for="old_password">Current Password:</label>
                    <input type="password" id="old_password" name="old_password" placeholder="Enter current password" required>

                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>

                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <h2>Update Profile Details</h2>
                <form method="post">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" placeholder="Enter your name (optional)">

                    <label for="phone">Phone Number (format: 111-111-1111):</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($currentUser['phone_num'] ?? '') ?>" placeholder="Enter phone number (optional)">

                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </form>
            </div>

            <div class="card danger" style="margin-top: 2rem;">
                <h2>Delete Account</h2>
                <p><strong>Warning:</strong> This action cannot be undone. All your account data will be permanently removed.</p>

                <form action="assets/php/delete_user.php" method="post" 
                    onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                    <button type="submit" class="btn btn-danger">Delete My Account</button>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <div>© <?= date('Y') ?> Collectable Peddlers — Built with PHP</div>
    </footer>
</div>
</body>
</html>
