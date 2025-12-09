<?php
session_start();

// Require login
if (!isset($_SESSION['user'])) {
    die("You must be logged in to create a listing.");
}

$user = $_SESSION['user']; // contains user_id, username, etc.

// When form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect form values
    $title = trim($_POST['listName']);
    $price = floatval($_POST['listPrice']);
    $desc = trim($_POST['listDesc']);
    $condition = trim($_POST['listCondition']);
    $imageUrl = trim($_POST['imageUrl']);

    // Connect to DB
    $pdo = new PDO("mysql:host=localhost;dbname=collectable_peddlers;charset=utf8mb4", "root", "");

    // Insert query
    $stmt = $pdo->prepare("
        INSERT INTO listing (user_id, title, description, price, condition, image_url, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
    ");

    $stmt->execute([
        $user['user_id'],
        $title,
        $desc,
        $price,
        $condition,
        $imageUrl
    ]);

    // Redirect to view the item
    $newId = $pdo->lastInsertId();
    header("Location: listing.php?id=" . $newId);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create New Listing</title>
</head>

<body>

<h2>Create a New Listing</h2>
<a href="index.php">Return to home</a>

<form method="POST">

    <label>Listing Name:</label><br>
    <input type="text" name="listName" required><br><br>

    <label>Listing Price:</label><br>
    <input type="number" step="0.01" name="listPrice" required><br><br>

    <label>Condition:</label><br>
    <select name="listCondition" required>
        <option value="New">New</option>
        <option value="Like New">Like New</option>
        <option value="Used">Used</option>
    </select>
    <br><br>

    <label>Image URL:</label><br>
    <input type="text" name="imageUrl" placeholder="images/item.jpg" required><br><br>

    <label>Description:</label><br>
    <textarea name="listDesc" required></textarea><br><br>

    <button type="submit">Create Listing</button>

</form>

</body>
</html>