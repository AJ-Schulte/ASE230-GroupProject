<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

$user = $_SESSION['user'];


$host = 'localhost';
$dbname = 'collectable_peddlers';
$dbuser = 'root';
$dbpass = ''; 

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("DELETE FROM User WHERE username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->close();

$conn->close();

unset($_SESSION['user']);
session_destroy();

header('Location: ../../index.php');
exit;
?>
