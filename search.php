<?php
    session_start();
    $user = $_SESSION['user'] ?? null;
    //$db = new PDO('mysql:host=localhost;dbname=collectable_peddlers;charset=utf8mb4','root', '');
    $hasSearched = false;
    function SearchListings()
    {
        $mainKey = $_POST['searchKey'];

        for ($i=0;$i<1;$i++)
        {
            echo "name";
        }
    }

    function pullSomethingFromDatabase()
    {
        //$stmt = $pdo->query('SELECT name FROM users');
        /*while ($row = $stmt->fetch())
        {
            echo $row['name'] . "\n";
        }*/
        
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
        <h4>Search</h4>
        <a href="index.php">Return to home</a>
        <form method="POST">
            <input type="text" name="searchKey"></input>
            <input type="submit"><?= pullSomethingFromDatabase() ?></input><br>
        </form>
        <br>
    </body>
</html>