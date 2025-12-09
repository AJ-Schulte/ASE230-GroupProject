<?php
session_start();

// Build user object from login.php session values
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'is_admin' => $_SESSION['is_admin']
    ];
}

// Get listing ID
$listingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// DB Connection
$pdo = new PDO(
    "mysql:host=localhost;dbname=collectable_peddlers;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Get listing
$listingStmt = $pdo->prepare("SELECT * FROM listing WHERE listing_id = ?");
$listingStmt->execute([$listingId]);
$listing = $listingStmt->fetch(PDO::FETCH_ASSOC);

// If listing not found → stop
if (!$listing) {
    echo "Listing not found.";
    exit;
}

// Format price
function format_price($amount) {
    return '$' . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Listing details for marketplace item">
    <title><?= htmlspecialchars($listing['title']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .listing { max-width: 600px; margin: auto; text-align: center; }
        .main-image { width: 100%; max-width: 300px; border-radius: 10px; }
        .buy-btn { padding: 10px 20px; margin-top: 10px; cursor: pointer; }
    </style>
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
            <a href="index.php">Browse</a>
            <a href="new_listing.php">Sell</a>
            <a href="userDash.php">Collections</a>
        </nav>

        <!-- AUTH UI -->
        <div class="auth">
            <?php if ($user): ?>
                <span>Signed in as <strong><?= htmlspecialchars($user['username']) ?></strong></span>
                <a class="btn btn-outline" href="profile.php">Profile</a>
                <a class="btn btn-outline" href="assets/php/logout.php">Sign out</a>
            <?php else: ?>
                <a class="btn btn-outline" href="login.php">Log in</a>
                <a class="btn btn-primary" href="login.php?mode=signup">Sign up</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div class="listing">

            <!-- MAIN IMAGE -->
            <img id="mainImage"
                 src="<?= htmlspecialchars("assets/database/" . $listing['image_url'])?>"
                 alt="Product image"
                 class="main-image">

            <h2><?= htmlspecialchars($listing['title']) ?></h2>
            <p><strong>Condition:</strong> <?= htmlspecialchars($listing['condition']) ?></p>

            <p class="price"><?= format_price($listing['price']) ?></p>

            <?php if ($listing['sold_at']): ?>
                <button class="buy-btn" disabled>Sold</button>
            <?php else: ?>
                <a class="btn btn-primary buy-btn"
                href="assets/php/addToCart.php?item=<?= $listing['listing_id'] ?>">
                    Add to Cart
                </a>
            <?php endif; ?>

            <p><?= nl2br(htmlspecialchars($listing['description'])) ?></p>

        </div>
    </main>

</div>
</body>
</html>
