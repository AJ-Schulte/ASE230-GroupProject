<?php
    session_start();
    $user = $_SESSION['user'] ?? null;
    $db = new PDO('mysql:host=localhost;dbname=collectable_peddlers;charset=utf8mb4','root', '');
    function CreateListing()
    {
        $listingFile = __DIR__.'/assets/database/listing.json';
        $listings = json_decode(file_get_contents($listingFile), true);

        //find highest id for setting next id num
        $maxId = 0;
        foreach ($listings as $item) {
            if (isset($item['listingID'])) {
                $id = (int)$item['listingID'];
                if ($id > $maxId) {
                    $maxId = $id;
                }
            }
        }

        $name = trim($_POST["listName"]);
        $price = trim($_POST["listPrice"]);
        $desc = trim($_POST["listDesc"]);
        $newId = $maxId + 1;

        $listings[] = ["listingID" => (string)$newId, "listingName" => $name, "price" => $price, "desc" => $desc, "tags" => [], "photos" => [], "sold" => false, "deleted" => false];

        file_put_contents($listingFile, json_encode($listings, JSON_PRETTY_PRINT));
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>New Listing</title>
    </head>

    <body>
        <h4>Create a New Listing</h4>
        <a href="index.php">Return to home</a>
        <form method="POST">
            Listing Name: <input type="text" name="listName" required/><br>
            Listing Price: $<input type="number" step="0.01" name="listPrice" required/><br>
            Listing Description:<input type="textarea" name="listDesc" required/><br>
            <input type="submit"><?= CreateListing() ?></input>
        </form>
    </body>
</html>