<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];
$usersFile = __DIR__ . '/assets/database/user.json';

if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
}

$users = json_decode(file_get_contents($usersFile), true);
if (!is_array($users)) {
    $users = [];
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $foundIndex = null;
    foreach ($users as $index => $u) {
        if ($u['username'] === $user) {
            $foundIndex = $index;
            break;
        }
    }

    if ($foundIndex === null) {
        $message = "⚠️ User not found in database.";
    } else {
        if (!empty($_POST['new_username'])) {
            $newUsername = trim($_POST['new_username']);
            $exists = false;
            foreach ($users as $u) {
                if ($u['username'] === $newUsername) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                $message = "⚠️ Username already taken.";
            } else {
                $users[$foundIndex]['username'] = $newUsername;
                $_SESSION['user'] = $newUsername;
                $user = $newUsername;
                $message = "✅ Username updated successfully.";
            }
        }

        if (!empty($_POST['old_password']) && !empty($_POST['new_password'])) {
            if ($users[$foundIndex]['password'] === $_POST['old_password']) {
                $users[$foundIndex]['password'] = $_POST['new_password'];
                $message = "✅ Password changed successfully.";
            } else {
                $message = "❌ Incorrect old password.";
            }
        }

        if (isset($_POST['action']) && $_POST['action'] === 'delete_account') {
            unset($users[$foundIndex]);
            $users = array_values($users); 
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

            session_unset();
            session_destroy();

            header('Location: ../ASE230-GroupProject/index.php');
            exit;
        }
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Your Account — Collectable Peddlers</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <header>
            <a href="/" class="brand"><div class="logo">MX</div><div><div class="brand-name">Collectable Peddlers</div><div class="brand-tag">Buy • Sell • Trade — Cards &amp; Collectibles</div></div></a>
            <nav>
                <a href="../ASE230-GroupProject/search.php">Browse</a>
                <a href="../ASE230-GroupProject/new_listing.php">Sell</a>
                <a href="../ASE230-GroupProject/userDash.php">Collections</a>
            </nav>
            <div class="auth">
                <a class="btn btn-outline" href="../ASE230-GroupProject/cart.php">Cart</a>
                <a class="btn btn-outline" href="../ASE230-GroupProject/assets/php/logout.php">Sign out</a>
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
                    <input type="text" value="<?= htmlspecialchars($user) ?>" disabled>

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

             <div class="card danger" style="margin-top: 2rem;">
                <h2>Delete Account</h2>
                <p><strong>Warning:</strong> This action cannot be undone. All your account data will be permanently removed.</p>
                <form method="post" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                    <input type="hidden" name="action" value="delete_account">
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
