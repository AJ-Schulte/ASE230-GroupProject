<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$transactionId = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Purchase Complete</title>
</head>
<body>
<h1>Thank you for your purchase!</h1>
<p>Your transaction ID is <strong><?= htmlspecialchars($transactionId) ?></strong>.</p>

<a href="index.php">Return Home</a>
</body>
</html>
