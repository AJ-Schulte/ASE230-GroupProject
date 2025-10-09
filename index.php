<?php

function format_price($cents) {
    return '$' . number_format($cents / 100, 2);
}
function truncate($text, $len = 120) {
    return mb_strlen($text) <= $len ? $text : mb_substr($text, 0, $len - 1) . '…';
}

$featured = [
    ['id'=>101,'title'=>'Vintage Trading Card: Blue Dragon','price_cents'=>12500,'seller'=>'card_sam','image'=>'assets/images/placeholder-card.jpg','category'=>'Trading Cards','condition'=>'Near Mint','description'=>'Limited edition Blue Dragon card with near-mint corners and original sleeve.'],
    ['id'=>102,'title'=>'Signed Collectible Poster — Retro Game','price_cents'=>4999,'seller'=>'retro_mike','image'=>'assets/images/placeholder-poster.jpg','category'=>'Collectibles','condition'=>'Good','description'=>'Autographed poster from a classic indie game developer.'],
    ['id'=>103,'title'=>'Rare Promo Card — Set 3','price_cents'=>7500,'seller'=>'collector_zen','image'=>'assets/images/placeholder-card2.jpg','category'=>'Promo Cards','condition'=>'Excellent','description'=>'Hard-to-find promo card from set 3. Stored in protective case.']
];

session_start();
$user = $_SESSION['user'] ?? null;
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
                <aside>
                    <div class="featured">
                        <h3>Featured listings</h3>
                        <?php foreach ($featured as $item): ?>
                            <article class="featured-item">
                                <div class="featured-thumb">
                                    <img src="<?=htmlspecialchars($item['image'])?>" alt="<?=htmlspecialchars($item['title'])?>">
                                </div>
                                <div class="featured-info">
                                    <div class="featured-title"><?=htmlspecialchars($item['title'])?></div>
                                    <div class="meta"><?=htmlspecialchars($item['seller'])?> · <?=htmlspecialchars($item['condition'])?></div>
                                </div>
                                <div class="featured-price">
                                    <div class="price"><?=format_price($item['price_cents'])?></div>
                                    <a href="/item.php?id=<?=urlencode($item['id'])?>" class="btn btn-outline">View</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </aside>
            </section>

            <section class="explore">
                <h2>Explore listings</h2>
                <div class="grid">
                    <?php foreach ($featured as $item): ?>
                        <article class="card">
                            <div class="thumb"><img src="<?=htmlspecialchars($item['image'])?>" alt="<?=htmlspecialchars($item['title'])?>"></div>
                            <div>
                                <div class="card-title"><?=htmlspecialchars($item['title'])?></div>
                                <div class="meta"><?=htmlspecialchars($item['category'])?> • <?=htmlspecialchars($item['condition'])?></div>
                                <p class="desc"><?=htmlspecialchars(truncate($item['description'], 140))?></p>
                            </div>
                            <div class="card-footer">
                                <div class="price"><?=format_price($item['price_cents'])?></div>
                                <div>
                                    <a class="btn btn-outline" href="/item.php?id=<?=urlencode($item['id'])?>">Details</a>
                                    <a class="btn btn-primary" href="/checkout.php?item=<?=urlencode($item['id'])?>">Buy</a>
                                </div>
                            </div>
                        </article>
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
