<?php
session_start();
ob_start();
$title_page = "Wishlist";

// Sample wishlist products in session
// $_SESSION['wishlist'] = [
//     ["id"=>1, "name"=>"Stylish Dress 1", "price"=>1499, "img"=>"https://source.unsplash.com/400x400/?dress,1"],
//     ["id"=>3, "name"=>"Casual Shirt", "price"=>999, "img"=>"https://source.unsplash.com/400x400/?shirt,1"]
// ];

$wishlist = $_SESSION['wishlist'] ?? [];

?>

<section class="container py-5">
    <h2 class="fw-bold mb-4">My Wishlist</h2>

    <?php if(empty($wishlist)): ?>
        <p>Your wishlist is empty. <a href="shop.php">Shop Now</a></p>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach($wishlist as $item): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="<?= $item['img'] ?>" class="card-img-top rounded-top" alt="<?= $item['name'] ?>">
                        <div class="card-body text-center">
                            <h6 class="card-title"><?= $item['name'] ?></h6>
                            <p class="fw-bold text-dark">₹<?= $item['price'] ?></p>
                        </div>
                        <div class="card-footer bg-white d-flex gap-1">
                            <a href="cart_add.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-primary w-50">Add to Cart</a>
                            <a href="wishlist_remove.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-danger w-50">Remove</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
