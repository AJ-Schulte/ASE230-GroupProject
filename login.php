<?php
session_start();

// ============================
// Database Connection (XAMPP)
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

        // ------------------------
        // SIGNUP
        // ------------------------
        if ($mode === 'signup') {

            $stmt = $conn->prepare("SELECT user_id FROM User WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message = "Username already exists.";
            } else {

                // Create user
                $email = $username . "@example.com";
                $is_admin = 0;

                $stmt = $conn->prepare("INSERT INTO User (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $username, $email, $password, $is_admin);

                if ($stmt->execute()) {

                    // Fetch new ID
                    $new_id = $stmt->insert_id;

                    // Set session
                    $_SESSION['user_id'] = $new_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['is_admin'] = 0;

                    header("Location: index.php");
                    exit;
                } else {
                    $message = "Error creating account.";
                }
            }
            $stmt->close();
        }

        // ------------------------
        // LOGIN (user OR admin)
        // ------------------------
        else {

            $stmt = $conn->prepare("SELECT user_id, username, password, is_admin FROM User WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $row = $result->fetch_assoc()) {

                if ($password === $row['password']) {

                    // Store session data
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['is_admin'] = $row['is_admin'];

                    // --- ADMIN LOGIN MODE ---
                    if ($mode === 'admin') {

                        if ($row['is_admin'] == 1) {
                            header("Location: admin/admin.php");
                            exit;
                        } else {
                            $message = "This account is not an admin.";
                        }
                    }

                    // --- NORMAL USER LOGIN ---
                    else {
                        header("Location: index.php");
                        exit;
                    }

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
        <h2><?php echo ($mode === 'admin') ? "Admin Login" : ucfirst($mode); ?></h2>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">

            <label for="username">Username:</label>
            <input type="text" name="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit"><?php echo ($mode === 'admin') ? "Login as Admin" : ucfirst($mode); ?></button>
        </form>

        <?php if ($mode === 'login'): ?>
            <p>Don't have an account? <a href="login.php?mode=signup">Sign up here</a></p>
            <p><strong><a href="login.php?mode=admin">Admin Login</a></strong></p>

        <?php elseif ($mode === 'signup'): ?>
            <p>Already have an account? <a href="login.php?mode=login">Log in here</a></p>

        <?php elseif ($mode === 'admin'): ?>
            <p><a href="login.php?mode=login">Back to User Login</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
