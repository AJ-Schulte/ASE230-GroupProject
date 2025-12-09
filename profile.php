<?php
session_start();

// ============================
// SESSION CHECK
// ============================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$is_admin = $_SESSION['is_admin'] ?? 0;

// ============================
// DATABASE CONNECTION
// ============================
$host = 'localhost';
$dbname = 'collectable_peddlers';
$dbuser = 'root';
$dbpass = '';

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ============================
// LOAD CURRENT USER INFO
// ============================
$stmt = $conn->prepare("SELECT user_id, username, name, phone_num, password FROM User WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$currentUser = $result->fetch_assoc();
$stmt->close();

if (!$currentUser) {
    die("⚠️ User not found.");
}

$message = "";

// ============================
// HANDLE FORM ACTIONS
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---- CHANGE USERNAME ----
    if (!empty($_POST['new_username'])) {

        $newUsername = trim($_POST['new_username']);

        // Check if taken
        $check = $conn->prepare("SELECT user_id FROM User WHERE username = ?");
        $check->bind_param("s", $newUsername);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "⚠️ Username already taken.";
        } else {
            $update = $conn->prepare("UPDATE User SET username = ? WHERE user_id = ?");
            $update->bind_param("si", $newUsername, $user_id);
            $update->execute();
            $update->close();

            // Update session
            $_SESSION['username'] = $newUsername;
            $username = $newUsername;

            $message = "✅ Username updated.";
        }
        $check->close();
    }

    // ---- CHANGE PASSWORD ----
    if (!empty($_POST['old_password']) && !empty($_POST['new_password'])) {

        if ($_POST['old_password'] === $currentUser['password']) {
            $newPass = trim($_POST['new_password']);
            $update = $conn->prepare("UPDATE User SET password = ? WHERE user_id = ?");
            $update->bind_param("si", $newPass, $user_id);
            $update->execute();
            $update->close();
            $message = "✅ Password updated.";
        } else {
            $message = "❌ Incorrect old password.";
        }
    }

    // ---- UPDATE NAME & PHONE ----
    if (isset($_POST['name']) || isset($_POST['phone'])) {

        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);

        if ($phone !== "" && !preg_match('/^\d{3}-\d{3}-\d{4}$/', $phone)) {
            $message = "❌ Invalid phone number format.";
        } else {
            $update = $conn->prepare("UPDATE User SET name = ?, phone_num = ? WHERE user_id = ?");
            $update->bind_param("ssi", $name, $phone, $user_id);
            $update->execute();
            $update->close();
            $message = "✅ Profile updated.";
        }
    }

    // Refresh data after changes
    $stmt = $conn->prepare("SELECT user_id, username, name, phone_num, password FROM User WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $currentUser = $stmt->get_result()->fetch_assoc();
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
        <a href="../ASE230-GroupProject" class="brand">
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

            <?php if (!empty($_SESSION['is_admin'])): ?>
                <a href="../ASE230-GroupProject/admin/admin.php" class="btn btn-primary" style="margin-bottom: 1rem; display: inline-block;">
                    Go to Admin Panel
                </a>
            <?php endif; ?>

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
