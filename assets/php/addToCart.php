<?php
session_start();

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    // redirect to login with return URL
    header("Location: ../../login.php?redirect=" . urlencode($_SERVER['HTTP_REFERER']));
    exit;
}

$user_id = $_SESSION['user_id'];
$listing_id = isset($_GET['item']) ? intval($_GET['item']) : 0;

if ($listing_id <= 0) {
    die("Invalid listing.");
}

// DB connect
$mysqli = new mysqli("localhost", "root", "", "collectable_peddlers");

if ($mysqli->connect_error) {
    die("Database error: " . $mysqli->connect_error);
}

// OPTIONAL: prevent duplicates in cart
$check = $mysqli->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND listing_id = ?");
$check->bind_param("ii", $user_id, $listing_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    // Add to cart
    $insert = $mysqli->prepare("INSERT INTO cart (user_id, listing_id) VALUES (?, ?)");
    $insert->bind_param("ii", $user_id, $listing_id);
    $insert->execute();
}

$check->close();
$mysqli->close();

// Redirect back to page user was on
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "../../cart.php";
header("Location: " . $redirect);
exit;
?>
