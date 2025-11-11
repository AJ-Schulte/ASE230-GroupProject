<?php
session_start();

// ============================
// Database Connection (XAMPP)
// ============================
$host = 'localhost';
$dbname = 'collectable_peddlers'; // change if needed
$user = 'root';
$pass = ''; // default for XAMPP is empty

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ============================
// Main Logic
// ============================
$message = '';
$mode = $_GET['mode'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $message = "Please enter both username and password.";
    } else {
        if ($mode === 'signup') {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT user_id FROM User WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message = "Username already exists. Please choose another.";
            } else {
                // Insert new user (plain password)
                $stmt = $conn->prepare("INSERT INTO User (username, email, password) VALUES (?, ?, ?)");
                $email = $username . "@example.com"; // placeholder email
                $stmt->bind_param("sss", $username, $email, $password);

                if ($stmt->execute()) {
                    $_SESSION['user'] = $username;
                    header("Location: index.php");
                    exit;
                } else {
                    $message = "Error creating account. Please try again.";
                }
            }
            $stmt->close();
        }

        elseif ($mode === 'login') {
            // Verify credentials
            $stmt = $conn->prepare("SELECT user_id, password FROM User WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $row = $result->fetch_assoc()) {
                if ($password === $row['password']) {
                    $_SESSION['user'] = $username;
                    header("Location: index.php");
                    exit;
                } else {
                    $message = "Invalid username or password.";
                }
            } else {
                $message = "Invalid username or password.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
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
