<?php
session_start();
$user = $_SESSION['user'] ?? null;

function format_price($cents) {
    return '$' . number_format($cents / 100, 2);
}
function truncate($text, $len = 120) {
    return mb_strlen($text) <= $len ? $text : mb_substr($text, 0, $len - 1) . '…';
}

$listingFile = __DIR__.'/assets/database/listing.json';

$listings = json_decode(file_get_contents($listingFile), true) ?? [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Collectable Peddlers — Buy &amp; Sell Cards &amp; Collectibles</title>
    <meta name="description" content="A lightweight PHP marketplace focused on trading cards, collectibles and niche goods.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <a href="/" class="brand"><div class="logo">MX</div><div><div class="brand-name">Collectable Peddlers</div><div class="brand-tag">Buy • Sell • Trade — Cards &amp; Collectibles</div></div></a>
            <nav>
                <a href="../ASE230-GroupProject/search.php">Browse</a>
                <a href="../ASE230-GroupProject/new_listing.php">Sell</a>
                <a href="../ASE230-GroupProject/userDash.php">Collections</a>
            </nav>
            <div class="auth">
                <?php if ($user): ?>
                    <span>Signed in as <strong><?=htmlspecialchars($user)?></strong></span>
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
            <section class="hero">
                <div>
                    <h1>A lightweight, PHP-first marketplace for collectors</h1>
                    <p>Server-side rendered, accessible, and fast. List items, manage collections, and trade securely — without client-side JavaScript.</p>
                    <form class="search-box" method="get" action="/browse.php">
                        <div class="search-row">
                            <input type="text" name="q" placeholder="Search cards, posters, collectibles...">
                            <select name="category">
                                <option value="">All categories</option>
                                <option>Trading Cards</option>
                                <option>Collectibles</option>
                                <option>Posters</option>
                                <option>Promo Cards</option>
                            </select>
                            <button type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="explore">
                <h2>Explore listings</h2>
                <div class="grid">
                    <?php foreach ($listings as $item): ?>
                        <?php if (!$item['deleted'] && !$item['sold']): ?>
                            <article class="card">
                                <div class="thumb">
                                    <img src="<?=htmlspecialchars($item['photos'][0])?>" 
                                        alt="<?=htmlspecialchars($item['listingName'])?>">
                                </div>
                                <div>
                                    <div class="card-title"><?=htmlspecialchars($item['listingName'])?></div>
                                    <?php 
                                    $tags = is_array($item['tags']) ? implode(', ', $item['tags']) : $item['tags']; 
                                    ?>
                                    <div class="meta"><?=htmlspecialchars($tags)?></div>
                                    <p class="desc"><?=htmlspecialchars(truncate($item['desc'], 140))?></p>
                                </div>
                                <div class="card-footer">
                                    <div class="price"><?=format_price($item['price'])?></div>
                                    <div>
                                        <a class="btn btn-outline" 
                                        href="../ASE230-GroupProject/listingDetail.php?id=<?=urlencode($item['id'])?>">
                                        Details
                                        </a>
                                        <a class="btn btn-primary" 
                                        href="../ASE230-GroupProject/assets/php/addToCart.php?item=<?=urlencode($item['id'])?>">
                                        Buy
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>

        <footer>
            <div>© <?=date('Y')?> Collectable Peddlers — Built with PHP</div>
        </footer>
    </div>
</body>
</html>
