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
    </head>

    <body>
        <h4>Search</h4>
        <a href="index.php">Return to home</a>
        <form method="POST">
            <input type="text" name="searchKey"></input>
            <input type="submit"><?= pullSomethingFromDatabase() ?></input><br>
        </form>
        <br>
    </body>
</html>