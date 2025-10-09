<?php
session_start();

$usersFile = __DIR__ . '/assets/database/user.json';

if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
}

$users = json_decode(file_get_contents($usersFile), true) ?? [];

$message = '';
$mode = $_GET['mode'] ?? 'login'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($mode === 'signup') {
        $exists = false;
        foreach ($users as $u) {
            if ($u['username'] === $username) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $message = 'Username already exists. Please choose another.';
        } else {
            $users[] = ['username' => $username, 'password' => $password, 'collectionBought' => [], 'createdListings' => [], 'cart' => []];
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

            $_SESSION['user'] = $username;

            header('Location: index.php');
            exit;
        }
    }

    elseif ($mode === 'login') {
        $found = false;
        foreach ($users as $u) {
            if ($u['username'] === $username && $u['password'] === $password) {
                $found = true;
                $_SESSION['user'] = $username;
                break;
            }
        }

        if ($found) {
            header('Location: index.php');
            exit;
        } else {
            $message = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collectable Peddlers | <?php echo ucfirst($mode); ?></title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h1>Collectable Peddlers</h1>
        <h2><?php echo ucfirst($mode); ?></h2>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">

            <label for="username">Username:</label>
            <input type="text" name="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit"><?php echo ucfirst($mode); ?></button>
        </form>

        <?php if ($mode === 'login'): ?>
            <p>Don't have an account? <a href="login.php?mode=signup">Sign up here</a></p>
        <?php else: ?>
            <p>Already have an account? <a href="login.php?mode=login">Log in here</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
