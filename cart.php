<?php
session_start();

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? null;

// DB Connection
$pdo = new PDO(
    "mysql:host=localhost;dbname=collectable_peddlers;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Formatting helpers (DB stores prices as DECIMAL(10,2) in dollars)
function format_price($amount) {
    return '$' . number_format((float)$amount, 2);
}
function truncate($text, $len = 120) {
    return mb_strlen($text) <= $len ? $text : mb_substr($text, 0, $len - 1) . '…';
}

// -------------------------------------------------
// FETCH USER CART ITEMS (includes seller user_id)
// -------------------------------------------------
$stmt = $pdo->prepare("
    SELECT 
        c.cart_id,
        l.listing_id,
        l.user_id AS seller_id,
        l.title,
        l.description,
        l.price,
        l.image_url,
        l.status
    FROM cart c
    JOIN listing l ON c.listing_id = l.listing_id
    WHERE c.user_id = ?
    ORDER BY c.added_at ASC
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate grand total (sum of active item prices)
$grandTotal = 0.00;
foreach ($cartItems as $item) {
    if ($item["status"] === "active") {
        $grandTotal += (float)$item["price"];
    }
}

// -------------------------------------------------
// HANDLE REMOVE FROM CART
// -------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["remove_cart_id"])) {
    $removeId = (int)$_POST["remove_cart_id"];

    // Make sure user can only delete their own cart rows
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$removeId, $userId]);

    header("Location: cart.php");
    exit;
}

// -------------------------------------------------
// HANDLE PURCHASE -> group by seller and create a transaction per seller
// -------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["purchase"])) {

    if (empty($cartItems)) {
        $error = "Your cart is empty.";
    } else {
        // Group items by seller_id
        $groups = [];
        foreach ($cartItems as $it) {
            // only include active items
            if ($it['status'] !== 'active') continue;
            $seller = (int)$it['seller_id'];
            if (!isset($groups[$seller])) $groups[$seller] = [];
            $groups[$seller][] = $it;
        }

        if (empty($groups)) {
            $error = "No available items to purchase.";
        } else {
            try {
                $pdo->beginTransaction();

                $createdTransactionIds = [];

                // Prepare statements we'll reuse
                $insertTrans = $pdo->prepare("
                    INSERT INTO `transaction` (buyer_id, seller_id, transaction_date, total_price, status)
                    VALUES (?, ?, NOW(), ?, 'completed')
                ");

                $insertTransListing = $pdo->prepare("
                    INSERT INTO transaction_listing (transaction_id, listing_id, quantity, price_at_sale)
                    VALUES (?, ?, ?, ?)
                ");

                $updateListing = $pdo->prepare("
                    UPDATE listing SET status = 'sold', sold_at = NOW() WHERE listing_id = ?
                ");

                $removeCart = $pdo->prepare("
                    DELETE FROM cart WHERE cart_id = ?
                ");

                foreach ($groups as $sellerId => $items) {
                    // compute seller total
                    $sellerTotal = 0.00;
                    foreach ($items as $it) $sellerTotal += (float)$it['price'];

                    // create transaction for this seller
                    $insertTrans->execute([$userId, $sellerId, $sellerTotal]);
                    $transactionId = $pdo->lastInsertId();
                    $createdTransactionIds[] = $transactionId;

                    // insert transaction_listing rows, update listings and remove from cart
                    foreach ($items as $it) {
                        $insertTransListing->execute([
                            $transactionId,
                            $it['listing_id'],
                            1, // quantity
                            $it['price']
                        ]);

                        $updateListing->execute([$it['listing_id']]);

                        $removeCart->execute([$it['cart_id']]);
                    }
                }

                $pdo->commit();

                // Redirect to purchase_success for first created transaction (could be adjusted)
                $firstTid = $createdTransactionIds[0] ?? null;
                header("Location: purchase_success.php?id=" . urlencode($firstTid));
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Purchase failed: " . $e->getMessage();
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your Cart — Collectable Peddlers</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
<div class="container">

<header>
    <a href="index.php" class="brand">
        <div class="logo">MX</div>
        <div>
            <div class="brand-name">Collectable Peddlers</div>
            <div class="brand-tag">Buy • Sell • Trade — Cards & Collectibles</div>
        </div>
    </a>

    <nav>
        <a href="search.php">Browse</a>
        <a href="new_listing.php">Sell</a>
        <a href="userDash.php">Collections</a>
    </nav>

    <div class="auth">
        <span>Signed in as <strong><?= htmlspecialchars($username) ?></strong></span>
        <a class="btn btn-outline" href="cart.php">Cart</a>
        <a class="btn btn-outline" href="profile.php">Profile</a>
        <a class="btn btn-outline" href="assets/php/logout.php">Sign out</a>
    </div>
</header>


<main>
    <h1>Your Cart</h1>

    <?php if (isset($error)): ?>
        <div class="message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>

        <p>Your cart is empty.</p>

    <?php else: ?>

        <div class="grid">

            <?php foreach ($cartItems as $item): ?>
                <article class="card">
                    <div class="thumb">
                        <img src="<?= htmlspecialchars("assets/database/" . $item['image_url']) ?>"
                            alt="<?= htmlspecialchars($item['title']) ?>">
                    </div>

                    <div>
                        <div class="card-title"><?= htmlspecialchars($item['title']) ?></div>
                        <p class="desc"><?= htmlspecialchars(truncate($item['description'], 140)) ?></p>
                    </div>

                    <div class="card-footer">
                        <div class="price"><?= format_price($item['price']) ?></div>

                        <?php if ($item["status"] !== "active"): ?>
                            <span class="muted">Unavailable</span>
                        <?php endif; ?>

                        <!-- REMOVE BUTTON -->
                        <form method="POST" style="margin-top: 8px;">
                            <input type="hidden" name="remove_cart_id" value="<?= $item['cart_id'] ?>">
                            <button class="btn btn-outline" style="background:#ffdddd;">Remove</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>

        </div>

        <h2>Grand total: <?= format_price($grandTotal) ?></h2>

        <?php if ($grandTotal > 0): ?>
            <form method="POST">
                <button class="btn btn-primary" name="purchase">Complete Purchase</button>
            </form>
        <?php endif; ?>

    <?php endif; ?>

</main>

<footer>
    <div>© <?= date('Y') ?> Collectable Peddlers — Built with PHP</div>
</footer>

</div>
</body>
</html>
