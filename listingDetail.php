<?php
echo "hello";
session_start();
$listingId = isset($_GET['id']) ? intval($_GET['id']) : 0;
// DB Connection
$pdo = new PDO("mysql:host=localhost;dbname=collectable_peddlers;charset=utf8mb4", "root", "");

// Get all collections owned by this user
$listingStmt = $pdo->prepare("
    SELECT * FROM listing
    WHERE listing_id = ?
");
$listingStmt->execute([$listingId]);
$listing = $listingStmt->fetch(PDO::FETCH_ASSOC);

// Search for matching listing

    //if it isnt found show error
    if(!$listing){
        echo "listing not found";
            
    }
    foreach ($listing as $thing){
        echo $thing + "";
    }
    //check if user is logged in

    function format_price($cents) {
    return '$' . number_format($cents / 100, 2);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page description here">
    <title>Itemtest or something</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .listing { max-width: 600px; margin: auto; text-align: center; }
        .main-image { width: 100%; max-width: 300px; border-radius: 10px; }
        .buy-btn { padding: 10px 20px; margin-top: 10px; cursor: pointer; }
        .tags span { background: #eee; padding: 5px 10px; border-radius: 8px; margin: 2px; display: inline-block; }
        .thumbnails img { width: 60px; margin: 5px; cursor: pointer; border-radius: 6px; border: 2px solid #ddd; transition: border 0.2s; }
        .thumbnails img:hover { border-color: #007bff; }
    </style>
</head>
<body>
<div class="container">
    <header>
                <a href="/" class="brand"><div class="logo">MX</div><div><div class="brand-name">Collectable Peddlers</div><div class="brand-tag">Buy • Sell • Trade — Cards &amp; Collectibles</div></div></a>
                <nav>
                    <a href="../ASE230-GroupProject/index.php">Browse</a>
                    <a href="../ASE230-GroupProject/new_listing.php">Sell</a>
                    <a href="../ASE230-GroupProject/userDash.php">Collections</a>
                </nav>

                <div class="auth">
                    <?php if ($user): ?>
                        <span>Signed in as <strong><?=htmlspecialchars($user['username'])?></strong></span>
                        <a class="btn btn-outline" href="../ASE230-GroupProject/profile.php">Profile</a>
                        <a class="btn btn-outline" href="../ASE230-GroupProject/assets/php/logout.php">Sign out</a>
                    <?php else: ?>
                        <a class="btn btn-outline" href="../ASE230-GroupProject/login.php">Log in</a>
                        <a class="btn btn-primary" href="../ASE230-GroupProject/login.php?mode=signup">Sign up</a>
                    <?php endif; ?>
                </div>
    <script>
    // Swap main image when a thumbnail is clicked
    function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
}
</script>
    </header>

    <main>
        <? $mainid = 0;?>
        <div class="listing">
            <img id="mainImage" src="<?= htmlspecialchars($listing['photos'][0]) ?>" alt="Product image" class="main-image">
            <div class="thumbnails">
                <?php foreach ($listing['photos'] as $img): ?>
                    <img src="<?= htmlspecialchars($img) ?>" 
                    alt="Thumbnail" 
                    onclick="changeMainImage('<?= htmlspecialchars($img) ?>')">
                <?php endforeach; ?>
            </div>

            <h2><?= htmlspecialchars($listing['listingName']) ?></h2>
            <p class="price"><?= format_price($listing['price'], 2) ?></p>

            <?php if ($listing['sold_at']): ?>
                <button class="buy-btn" disabled>Sold</button>
            <?php else: ?>
                <button class="buy-btn">Buy Now</button>
            <?php endif; ?>


            <div class="tags">
                <?php foreach ($listing['tags'] as $tag): ?>
                    <span class="tag"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </div>

            <p><?= htmlspecialchars($listing['desc']) ?></p>


        </div>
    </main>
</div>
</body>
</html>