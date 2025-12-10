<?php
session_start();

$user = null;
if (isset($_SESSION['user_id'])) {
    $user = [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'is_admin' => $_SESSION['is_admin']
    ];
}

// Listing ID
$listingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// DB Connection
$pdo = new PDO(
    "mysql:host=localhost;dbname=collectable_peddlers;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// ------------------------
// SAVE CHANGES
// ------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_mode']) && $user) {

    // Confirm ownership
    $stmtCheck = $pdo->prepare("SELECT user_id FROM listing WHERE listing_id = ?");
    $stmtCheck->execute([$listingId]);
    $owner = $stmtCheck->fetchColumn();

    if ($owner == $user['user_id']) {

        $title       = trim($_POST['title']);
        $price       = floatval($_POST['price']);
        $condition   = trim($_POST['condition']);
        $description = trim($_POST['description']);
        $imageUrl    = trim($_POST['image_url']);
        $status      = trim($_POST['status']);

        if ($price <= 0) {
            die("Price must be greater than zero.");
        }

        $updateStmt = $pdo->prepare("
            UPDATE listing
            SET title = ?, 
                price = ?, 
                `condition` = ?, 
                description = ?, 
                image_url = ?, 
                status = ?
            WHERE listing_id = ?
        ");

        $updateStmt->execute([
            $title,
            $price,
            $condition,
            $description,
            $imageUrl,
            $status,
            $listingId
        ]);

        header("Location: listingDetail.php?id=" . $listingId);
        exit;
    }
}

$listingStmt = $pdo->prepare("SELECT * FROM listing WHERE listing_id = ?");
$listingStmt->execute([$listingId]);
$listing = $listingStmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    echo "Listing not found.";
    exit;
}

function format_price($amount) {
    return '$' . number_format($amount, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($listing['title']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .listing { max-width: 600px; margin: auto; text-align: center; }
        .main-image { width: 100%; max-width: 300px; border-radius: 10px; }
        .edit-form { margin-top: 20px; text-align: left; }
        .form-group { margin-bottom: 12px; }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ccc;
        }
        .btn-small { padding: 7px 14px; font-size: 0.9em; }
    </style>
</head>

<body>
<div class="container">

<header>
    <a href="/" class="brand">
        <div class="logo">MX</div>
        <div>
            <div class="brand-name">Collectable Peddlers</div>
            <div class="brand-tag">Buy • Sell • Trade</div>
        </div>
    </a>

    <nav>
        <a href="index.php">Browse</a>
        <a href="new_listing.php">Sell</a>
        <a href="userDash.php">Collections</a>
    </nav>

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

        <img src="<?= htmlspecialchars("assets/database/" . $listing['image_url']) ?>"
             class="main-image">

        <h2><?= htmlspecialchars($listing['title']) ?></h2>
        <p><strong>Condition:</strong> <?= htmlspecialchars($listing['condition']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($listing['status']) ?></p>

        <p class="price"><?= format_price($listing['price']) ?></p>

        <?php if ($listing['sold_at']): ?>
            <button class="buy-btn" disabled>Sold</button>
        <?php else: ?>
            <a class="btn btn-primary buy-btn"
               href="assets/php/addToCart.php?item=<?= $listing['listing_id'] ?>">
                Add to Cart
            </a>
        <?php endif; ?>

        <!-- OWNER CAN EDIT -->
        <?php if ($user && $user['user_id'] == $listing['user_id']): ?>
            <button onclick="document.getElementById('editForm').style.display='block'"
                    class="btn btn-outline btn-small" style="margin-top: 12px;">
                Edit Listing
            </button>

            <form id="editForm" method="POST" class="edit-form" style="display:none;">
                <input type="hidden" name="edit_mode" value="1">

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($listing['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price"
                           value="<?= htmlspecialchars($listing['price']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Condition</label>
                    <select name="condition">
                        <option <?= $listing['condition']=="New" ? "selected":"" ?>>New</option>
                        <option <?= $listing['condition']=="Like New" ? "selected":"" ?>>Like New</option>
                        <option <?= $listing['condition']=="Used" ? "selected":"" ?>>Used</option>
                        <option <?= $listing['condition']=="Damaged" ? "selected":"" ?>>Damaged</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option <?= $listing['status']=="active" ? "selected":"" ?>>active</option>
                        <option <?= $listing['status']=="sold" ? "selected":"" ?>>sold</option>
                        <option <?= $listing['status']=="archived" ? "selected":"" ?>>archived</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Image Filename</label>
                    <input type="text" name="image_url" value="<?= htmlspecialchars($listing['image_url']) ?>">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required><?= htmlspecialchars($listing['description']) ?></textarea>
                </div>

                <button class="btn btn-primary" type="submit">Save Changes</button>
            </form>
        <?php endif; ?>

        <p style="margin-top:20px;"><?= nl2br(htmlspecialchars($listing['description'])) ?></p>

    </div>
</main>

</div>
</body>
</html>
