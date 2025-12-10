<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to create a listing.");
}

$user = [
    'user_id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'is_admin' => $_SESSION['is_admin']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['listName']);
    $price = floatval($_POST['listPrice']);
    $desc = trim($_POST['listDesc']);
    $condition = trim($_POST['listCondition']);
    $imageUrl = trim($_POST['imageUrl']);

    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=collectable_peddlers;charset=utf8mb4",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $pdo->prepare("
            INSERT INTO listing (user_id, title, description, price, `condition`, image_url, status, created_at)
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

        $newId = $pdo->lastInsertId();
        header("Location: listingDetail.php?id=" . $newId);
        exit;

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Listing</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="container">

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
                <a class="primary" href="new_listing.php">Sell</a>
                <a href="userDash.php">Collections</a>
            </nav>

            <div class="auth">
                <span>Signed in as <strong><?= htmlspecialchars($user['username']) ?></strong></span>

                <?php if ($user['is_admin'] == 1): ?>
                    <a class="btn btn-outline" href="admin/admin.php">Admin</a>
                <?php endif; ?>

                <a class="btn btn-outline" href="cart.php">Cart</a>
                <a class="btn btn-outline" href="profile.php">Profile</a>
                <a class="btn btn-outline" href="assets/php/logout.php">Sign out</a>
            </div>
        </header>

        <main>
            <a class="top-back" href="index.php">← Back to home</a>

            <h2 class="page-title">Create a New Listing</h2>

            <!-- Form wrapped as a card in a grid -->
            <div class="grid">
                <article class="card">
                    <form method="POST">

                        <label for="listName">Listing Name</label>
                        <input type="text" id="listName" name="listName" required>

                        <label for="listPrice">Listing Price</label>
                        <input type="number" step="0.01" id="listPrice" name="listPrice" required>

                        <label for="listCondition">Condition</label>
                        <select id="listCondition" name="listCondition" required>
                            <option value="New">New</option>
                            <option value="Like New">Like New</option>
                            <option value="Used">Used</option>
                        </select>

                        <label for="imageUrl">Image URL</label>
                        <input type="text" id="imageUrl" name="imageUrl" placeholder="images/item.jpg" required>

                        <label for="listDesc">Description</label>
                        <textarea id="listDesc" name="listDesc" required></textarea>

                        <div class="card-footer">
                            <a href="index.php" class="btn btn-outline">Cancel</a>
                            <button class="btn btn-primary" type="submit">Create Listing</button>
                        </div>

                    </form>
                </article>
            </div>
        </main>

        <footer>
            <div>© <?= date('Y') ?> Collectable Peddlers — Built with PHP</div>
        </footer>

    </div>
</body>
</html>
<?php