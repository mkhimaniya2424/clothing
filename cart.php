<?php
session_start();
ob_start();
$title_page = "My Cart";

// Sample cart in session
// $_SESSION['cart'] = [
//     ["id"=>1, "name"=>"Stylish Dress 1", "price"=>1499, "qty"=>2, "img"=>"https://source.unsplash.com/400x400/?dress,1"],
//     ["id"=>3, "name"=>"Casual Shirt", "price"=>999, "qty"=>1, "img"=>"https://source.unsplash.com/400x400/?shirt,1"]
// ];

$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach($cart as $item) $total += $item['price'] * $item['qty'];
?>

<section class="container py-5">
    <h2 class="fw-bold mb-4">Shopping Cart</h2>

    <?php if(empty($cart)): ?>
        <p>Your cart is empty. <a href="shop.php">Shop Now</a></p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cart as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= $item['img'] ?>" width="60" class="rounded" alt="<?= $item['name'] ?>">
                                    <span><?= $item['name'] ?></span>
                                </div>
                            </td>
                            <td>₹<?= $item['price'] ?></td>
                            <td>
                                <form method="POST" action="cart_update.php" class="d-flex align-items-center gap-1">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <input type="number" name="qty" class="form-control w-50" value="<?= $item['qty'] ?>" min="1">
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                            <td>₹<?= $item['price'] * $item['qty'] ?></td>
                            <td>
                                <a href="cart_remove.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-danger">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Total</td>
                        <td>₹<?= $total ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            <a href="checkout.php" class="btn btn-primary btn-lg">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
