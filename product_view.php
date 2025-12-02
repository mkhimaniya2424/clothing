<?php
ob_start();
$title_page = "Product Details";

// Sample product data
$productId = $_GET['id'] ?? 1;
$product = [
    "id"=>$productId,
    "name"=>"Stylish Dress $productId",
    "price"=>"1499",
    "img"=>"https://source.unsplash.com/500x500/?dress,$productId",
    "desc"=>"This is a beautiful, trendy outfit perfect for any occasion."
];
?>

<section class="container py-5">
    <div class="row">
        <div class="col-md-6 text-center">
            <img src="<?= $product['img'] ?>" class="img-fluid rounded shadow-sm" alt="<?= $product['name'] ?>">
        </div>
        <div class="col-md-6">
            <h3 class="fw-bold"><?= $product['name'] ?></h3>
            <p class="fw-bold text-danger">₹<?= $product['price'] ?></p>
            <p><?= $product['desc'] ?></p>

            <form method="POST" action="cart.php">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="mb-3">
                    <label>Quantity:</label>
                    <input type="number" name="qty" class="form-control w-25" value="1" min="1">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                    <a href="wishlist.php?id=<?= $product['id'] ?>" class="btn btn-outline-danger"><i class="fa fa-heart"></i></a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
