<?php
// Load and decode the JSON file
$products = json_decode(file_get_contents('assets/database/listing.json'), true);



// Get listing ID from the URL (default 103 if not provided)
$id = isset($_GET['listingID']) ? intval($_GET['listingID']) : 103;

// Initialize product variable
$product = null;

// Search for matching listing
foreach ($products as $item) {
    if (intval($item['listingID']) === $id) {
        $product = $item;
        break;
    }
}
    //if it isnt found show error
    if(!$product){
        echo "product not found";
            
    }
    //check if user is logged in
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page description here">
    <title>ItemDetails or something</title>
</head>
<body>

<div class="product">
    <img id="mainImage" src="<?= htmlspecialchars($product['photoID']) ?>" alt="Product image" class="main-image">

    <h2><?= htmlspecialchars($product['listingName']) ?></h2>
    <p class="price">$<?= number_format($product['price'], 2) ?></p>

    <?php if ($product['sold']): ?>
        <button class="buy-btn" disabled>Sold</button>
    <?php else: ?>
        <button class="buy-btn">Buy Now</button>
    <?php endif; ?>


    <div class="tags">
        <span><?= htmlspecialchars($product['tags']) ?></span>
    </div>

    <p><?= htmlspecialchars($product['desc']) ?></p>


</div>

</body>
</html>
<?php
    //big image display (product[image][id])

    //smaller thumbnail displays (foreach (product[image][id]), style css to make smaller/downsize whatever

    //buy button: if (product[sold][id] == true) strikeout else show

    //item name (echo '$product[name][id]')

    //price (echo '$produce[price][id])

    //id tags: foreach (product[idTag][id]){
        //show tag
        //if tag == searched tag{ highlight yellow with css}
    //}

    //description: echo '$product[description][id]


?>