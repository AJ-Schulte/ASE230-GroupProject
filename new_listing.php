<?php
    function CreateListing()
    {
        $listing = new stdClass;

        $listing->name = "";
        $listing->price = 0;
        $listing->desc = "";

        $filename = "assets/database/listing.json";

        $listing->name = $_POST["listName"];
        $listing->price = $_POST["listPrice"];
        $listing->desc = $_POST["listDesc"];

        $listingWrite = json_encode($listing);

        file_put_contents($filename, $listingWrite, FILE_APPEND);
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
            Listing Price: $<input type="number" step="0.01" name="listPrice" required/> Dollars <br>
            Listing Description:<input type="textarea" name="listDesc" required/><br>
            <input type="submit"><?= CreateListing() ?></input>
        </form>
    </body>
</html>