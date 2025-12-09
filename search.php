<?php
    session_start();
    $user = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? null;
    $pdo = new PDO('mysql:host=localhost;dbname=collectable_peddlers;charset=utf8mb4','root', '');
        $mainKey = $_POST['searchKey'];
        //if ($_GET['q'] != null && $mainKey == null)
        //    $mainKey = $_GET['q'];
        

        if ($mainKey != null)
        {
            $stmt = $pdo->prepare("
            SELECT * FROM listing
            WHERE title LIKE '%$mainKey%'
            ORDER BY created_at DESC
            ");
            $stmt->execute();
            $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
    /* Helper */
    function format_price($amount) {
        return "$" . number_format($amount, 2);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Search Listing</title>
        <link rel="stylesheet" href="assets/css/style.css">
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
                        <span>Signed in as <strong><?=htmlspecialchars($username)?></strong></span>
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
    <div style="text-align: center;">
        <h4>Search</h4>
        <form method="POST">
            <input type="text" name="searchKey" class="search-box"></input>
            <input type="submit" class="btn btn-primary"></input><br>
        </form>
        <br>

        <?php if (empty($listings)):?> 
            <h4>No results. Start your search!</h4>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($listings as $item): ?>
                    <article class="card">
                        <div class="thumb">
                            <img src="<?=htmlspecialchars("assets/database/" . $item['image_url'])?>" 
                                    alt="<?=htmlspecialchars($item['title'])?>">
                        </div>

                        <div class="card-title"><?=htmlspecialchars($item['title'])?></div>

                        <p class="desc">
                            <?= htmlspecialchars(mb_substr($item['description'], 0, 100)) ?>…

                            <div class="price"><?= format_price($item['price']) ?></div>
                            <a class="btn btn-outline" 
                                href="listingDetail.php?id=<?=$item['listing_id']?>">
                                View
                            </a>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    </body>

        <footer>
        <div>© <?=date('Y')?> Collectable Peddlers — Built with PHP</div>
    </footer>
</html>