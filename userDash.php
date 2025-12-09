<?php
session_start();

// Redirect if user is NOT logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// SESSION VARIABLES (consistent with login.php)
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$is_admin = $_SESSION['is_admin'] ?? 0;

// ------------------------
// DATABASE CONNECTION
// ------------------------
$pdo = new PDO(
    "mysql:host=localhost;dbname=collectable_peddlers;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// ------------------------
// FETCH USER COLLECTIONS
// ------------------------
$collectionsStmt = $pdo->prepare("
    SELECT *
    FROM collection
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$collectionsStmt->execute([$userId]);
$collections = $collectionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Price Formatter
function format_price($amount) {
    return "$" . number_format($amount, 2);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Your Collections — Collectable Peddlers</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
<div class="container">

    <!-- HEADER -->
    <header>
        <a href="index.php" class="brand">
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
            <?php if ($userId): ?>
                <span>Signed in as <strong><?= htmlspecialchars($username) ?></strong></span>
                <a class="btn btn-outline" href="cart.php">Cart</a>
                <a class="btn btn-outline" href="profile.php">Profile</a>

                <?php if ($is_admin == 1): ?>
                    <a class="btn btn-primary" href="admin/admin.php">Admin Panel</a>
                <?php endif; ?>

                <a class="btn btn-outline" href="assets/php/logout.php">Sign out</a>

            <?php else: ?>
                <a class="btn btn-outline" href="login.php">Log in</a>
                <a class="btn btn-primary" href="login.php?mode=signup">Sign up</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- MAIN -->
    <main>
        <h1>Your Collections</h1>

        <?php if (empty($collections)): ?>
            <p>You don’t have any collections yet.</p>
        <?php endif; ?>

        <?php foreach ($collections as $collection): ?>
            <section class="featured" style="margin-top: 24px;">
                <h2><?= htmlspecialchars($collection['name']) ?></h2>

                <?php
                // Fetch listings in the collection
                $listingsStmt = $pdo->prepare("
                    SELECT l.*
                    FROM listing l
                    JOIN collection_listing cl ON l.listing_id = cl.listing_id
                    WHERE cl.collection_id = ?
                ");
                $listingsStmt->execute([$collection['collection_id']]);
                $listings = $listingsStmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if (empty($listings)): ?>
                    <p class="muted">No items in this collection yet.</p>

                <?php else: ?>
                    <div class="grid">

                        <?php foreach ($listings as $item): ?>
                            <article class="card">
                                <div class="thumb">
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>"
                                         alt="<?= htmlspecialchars($item['title']) ?>">
                                </div>

                                <div class="card-title"><?= htmlspecialchars($item['title']) ?></div>

                                <p class="desc">
                                    <?= htmlspecialchars(mb_substr($item['description'], 0, 100)) ?>…
                                </p>

                                <div class="card-footer">
                                    <div class="price"><?= format_price($item['price']) ?></div>
                                    <a class="btn btn-outline"
                                       href="listingDetail.php?id=<?= $item['listing_id'] ?>">
                                        View
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>

                    </div>
                <?php endif; ?>

            </section>
        <?php endforeach; ?>

    </main>

    <footer>
        <div>© <?= date('Y') ?> Collectable Peddlers — Built with PHP</div>
    </footer>
</div>
</body>
</html>
