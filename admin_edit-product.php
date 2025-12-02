<?php
ob_start();
require_once 'db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: admin_manage-products.php");
    exit;
}

$id = intval($_GET['id']);
$res = $con->query("SELECT * FROM products WHERE id=$id");
if ($res->num_rows === 0) {
    header("Location: admin_manage-products.php");
    exit;
}
$product = $res->fetch_assoc();

// Fetch Stock
$stockRes = $con->query("SELECT stock FROM product_stock WHERE product_id=$id");
$stock = ($stockRes->num_rows > 0) ? $stockRes->fetch_assoc()['stock'] : 0;

/* ---------------- LOAD BRANDS & CATEGORIES ---------------- */
$brands = $con->query("SELECT name FROM brands ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$mainCats = $con->query("SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name")->fetch_all(MYSQLI_ASSOC);

/* --- Fetch all categories for JS mapping --- */
$catResult = $con->query("SELECT id, name, parent_id FROM categories ORDER BY parent_id, name");
$categories = [];
while ($c = $catResult->fetch_assoc()) $categories[] = $c;

/* --- Build JS map for main → sub categories --- */
$catMap = [];
foreach ($categories as $c) {
    if ($c['parent_id']) {
        $catMap[$c['parent_id']][] = $c['name'];
    }
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = floatval($_POST['price']);
    $category_main = trim($_POST['category_main']);
    $category_sub = trim($_POST['category_sub']);
    $category_type = trim($_POST['category_type']);
    $category_brand = trim($_POST['category_brand']);
    $sizes = trim($_POST['sizes']);
    $fabric = trim($_POST['fabric']);
    $highlight = trim($_POST['highlight']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $newStock = intval($_POST['stock']);

    if ($title === '') $msg = "Title is required.";
    else {
        $stmt = $con->prepare("UPDATE products SET title=?, price=?, category_main=?, category_sub=?, category_type=?, category_brand=?, sizes=?, fabric=?, highlight=?, description=?, status=? WHERE id=?");
        $stmt->bind_param("sdsssssssssi", $title, $price, $category_main, $category_sub, $category_type, $category_brand, $sizes, $fabric, $highlight, $description, $status, $id);
        
        if ($stmt->execute()) {
            // Update Stock
            $con->query("INSERT INTO product_stock (product_id, stock) VALUES ($id, $newStock) ON DUPLICATE KEY UPDATE stock=$newStock");
            $msg = "Product updated successfully!";
            // Refresh data
            $product = $con->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
            $stock = $newStock;
        } else {
            $msg = "Error updating product: " . $con->error;
        }
    }
}
?>

<div class="container mt-4">
    <h3>Edit Product</h3>
    
    <?php if($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Title</label>
                <input name="title" class="form-control" value="<?= htmlspecialchars($product['title']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>">
            </div>
        </div>

        <script> var catMap = <?= json_encode($catMap) ?>; 
        function loadSubs(mainId){ 
            let sub=document.getElementById("subcat"); 
            sub.innerHTML="<option value=''>-- Select --</option>"; 
            if(catMap[mainId]) catMap[mainId].forEach(v=>{
                let sel = (v === "<?= $product['category_sub'] ?>") ? "selected" : "";
                sub.innerHTML+=`<option value="${v}" ${sel}>${v}</option>`;
            });
        }
        </script>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Brand</label>
                <select name="category_brand" class="form-control">
                    <option value="">-- Select --</option>
                    <?php foreach($brands as $b): ?>
                        <option <?= ($b['name'] == $product['category_brand']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Main Category</label>
                <select name="category_main" class="form-control" onchange="loadSubs(this.value)">
                    <option value="">-- Select --</option>
                    <?php foreach($mainCats as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= ($m['name'] == $product['category_main'] || $m['id'] == $product['category_main']) ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Subcategory</label>
                <select name="category_sub" id="subcat" class="form-control">
                    <option value="<?= htmlspecialchars($product['category_sub']) ?>"><?= htmlspecialchars($product['category_sub']) ?></option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Type</label>
                <input name="category_type" class="form-control" value="<?= htmlspecialchars($product['category_type']) ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Sizes</label>
                <input name="sizes" class="form-control" value="<?= htmlspecialchars($product['sizes']) ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Fabric</label>
                <input name="fabric" class="form-control" value="<?= htmlspecialchars($product['fabric']) ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Stock</label>
                <input name="stock" type="number" class="form-control" value="<?= $stock ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Highlight</label>
            <textarea name="highlight" class="form-control"><?= htmlspecialchars($product['highlight']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="active" <?= ($product['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                <option value="disabled" <?= ($product['status'] == 'disabled') ? 'selected' : '' ?>>Disabled</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <a href="admin_manage-products.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Product</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
