<?php
session_start();

$username = $_SESSION['username'] ?? null;
$is_admin = $_SESSION['is_admin'] ?? 0;
$user_id = $_SESSION['user_id'] ?? null;

function format_price($cents) {
    return '$' . number_format($cents / 100, 2);
}
function truncate($text, $len = 120) {
    return mb_strlen($text) <= $len ? $text : mb_substr($text, 0, $len - 1) . '…';
}

$host = 'localhost';
$dbname = 'collectable_peddlers';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

$categories = [];
$result = $conn->query("SELECT name FROM category ORDER BY name ASC");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['name'];
    }
}

$randomListings = [];
$sql = "
    SELECT listing_id, user_id, title, description, price, image_url
    FROM Listing
    WHERE status = 'active'
    ORDER BY RAND()
    LIMIT 8;
";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $randomListings[] = $row;
    }
}

$conn->close();
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Collectable Peddlers — Buy &amp; Sell Cards &amp; Collectibles</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <div class="container">

            <header>
                <a href="../ASE230-GroupProject" class="brand">
                    <div class="logo">MX</div>
                    <div>
                        <div class="brand-name">Collectable Peddlers</div>
                        <div class="brand-tag">Buy • Sell • Trade — Cards &amp; Collectibles</div>
                    </div>
                </a>

                <nav>
                    <a href="../ASE230-GroupProject/search.php">Browse</a>
                    <a href="../ASE230-GroupProject/new_listing.php">Sell</a>
                    <a href="../ASE230-GroupProject/userDash.php">Collections</a>
                </nav>

                <div class="auth">
                    <?php if ($username): ?>
                        <span>Signed in as <strong><?= htmlspecialchars($username) ?></strong></span>

                        <?php if ($is_admin == 1): ?>
                            <a class="btn btn-outline" href="../ASE230-GroupProject/admin/admin.php">Admin</a>
                        <?php endif; ?>

                        <a class="btn btn-outline" href="../ASE230-GroupProject/cart.php">Cart</a>
                        <a class="btn btn-outline" href="../ASE230-GroupProject/profile.php">Profile</a>
                        <a class="btn btn-outline" href="../ASE230-GroupProject/assets/php/logout.php">Sign out</a>

                    <?php else: ?>
                        <a class="btn btn-outline" href="../ASE230-GroupProject/login.php">Log in</a>
                        <a class="btn btn-primary" href="../ASE230-GroupProject/login.php?mode=signup">Sign up</a>
                    <?php endif; ?>
                </div>
            </header>

            <main>
                <!-- HERO SECTION -->
                <section class="hero">
                    <div>
                        <h1>A lightweight, PHP-first marketplace for collectors</h1>
                        <p>Search items, manage your collection, and trade securely.</p>

                        <form class="search-box" method="get" action="search.php">
                            <div class="search-row">
                                <input type="text" name="q" placeholder="Search cards, posters, collectibles...">

                                <button type="submit">Search</button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- EXPLORE SECTION -->
                <section class="explore">
                    <h2>Explore listings</h2>

                    <div class="grid">
                        <?php foreach ($randomListings as $item): ?>
                            <article class="card">
                                <div class="thumb">
                                    <img src="<?= htmlspecialchars("assets/database/" . $item['image_url'])?>"
                                        alt="<?= htmlspecialchars($item['title']) ?>">
                                </div>

                                <div>
                                    <div class="card-title"><?= htmlspecialchars($item['title']) ?></div>
                                    <p class="desc"><?= htmlspecialchars(truncate($item['description'], 140)) ?></p>
                                </div>

                                <div class="card-footer">
                                    <div class="price"><?= format_price($item['price']) ?></div>

                                    <div>
                                        <a class="btn btn-outline"
                                        href="listingDetail.php?id=<?= urlencode($item['listing_id']) ?>">
                                            Details
                                        </a>
                                        <a class="btn btn-primary"
                                        href="assets/php/addToCart.php?item=<?= urlencode($item['listing_id']) ?>">
                                            Buy
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </main>

            <footer>
                <div>© <?= date('Y') ?> Collectable Peddlers — Built with PHP</div>
            </footer>
        </div>
    </body>
</html>