<?php
ob_start();
require_once 'admin_auth.php';
require_once '../db_connect.php';

/* ---------------- FETCH PRODUCTS ---------------- */
// Handle search
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchQuery = '';
if (!empty($searchTerm)) {
    $searchTerm = $con->real_escape_string($searchTerm);
    $searchQuery = " WHERE p.title LIKE '%$searchTerm%' OR p.category_brand LIKE '%$searchTerm%' OR p.category_main LIKE '%$searchTerm%' OR p.category_sub LIKE '%$searchTerm%'";
}

$res = $con->query("
    SELECT p.id, p.title, p.price, p.category_brand, p.category_main, p.category_sub,
           p.status, p.stock, p.images
    FROM products p
    $searchQuery
    ORDER BY p.id DESC
");

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

/* ---------------- UPLOAD FOLDER ---------------- */
// Adjusted path to match project structure
$uploadDir = __DIR__ . '/../uploads/products/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

/* ---------------- ADD PRODUCT ---------------- */
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ADD PRODUCT
    if(isset($_POST['action']) && $_POST['action'] === 'add_product') {
        $title = trim($_POST['title']);
        $price = floatval($_POST['price']);
        $category_main_id = trim($_POST['category_main']);
        // Fetch Name
        $catRow = $con->query("SELECT name FROM categories WHERE id='$category_main_id'")->fetch_assoc();
        $category_main = $catRow ? $catRow['name'] : $category_main_id;
        $category_sub = trim($_POST['category_sub']);
        $category_type = trim($_POST['category_type']);
        $category_brand = trim($_POST['category_brand']);
        $sizes = trim($_POST['sizes']);
        $fabric = trim($_POST['fabric']);
        $highlight = trim($_POST['highlight']);
        $description = trim($_POST['description']);
        $status = $_POST['status'] ?? 'active';
        $stock = intval($_POST['stock']);

        if ($title === '') $msg = "Title is required.";
        else {
            // Upload images
            $savedImages = [];
            if(!empty($_FILES['images']['name'][0])){
                foreach($_FILES['images']['name'] as $i=>$name){
                    if($_FILES['images']['error'][$i]!==UPLOAD_ERR_OK) continue;
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $safe = time() . "_" . bin2hex(random_bytes(5)) . "." . strtolower($ext);
                    if(move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadDir.$safe)){
                        // Store relative path for DB
                        $savedImages[] = "uploads/products/".$safe;
                    } else {
                        $msg .= " Failed to upload " . $name . ". Error code: " . $_FILES['images']['error'][$i] . ". Path: " . $uploadDir.$safe;
                    }
                }
            }
            if (empty($savedImages) && !empty($_FILES['images']['name'][0])) {
                $msg .= " No images were saved. Check directory permissions for " . $uploadDir;
            }
            $images_json = json_encode($savedImages);

            $stmt = $con->prepare("
                INSERT INTO products 
                (title, price, category_main, category_sub, category_type, category_brand, sizes, fabric,
                highlight, description, images, status, stock, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param(
                "sdssssssssssi",
                $title, $price, $category_main, $category_sub, $category_type,
                $category_brand, $sizes, $fabric, $highlight, $description,
                $images_json, $status, $stock
            );

            if($stmt->execute()){
                $msg = "Product added successfully!";
            } else $msg = "Error: ".$stmt->error;
            $stmt->close();
        }
    }

    // UPDATE STOCK
    if(isset($_POST['update_stock'])){
        $updateId = intval($_POST['product_id']);
        $newStock = intval($_POST['stock']);
        $stmt = $con->prepare("UPDATE products SET stock=? WHERE id=?");
        $stmt->bind_param("ii",$newStock,$updateId);
        $stmt->execute();
        $stmt->close();
        $msg = "Stock updated!";
    }
}

/* ---------------- HANDLE ENABLE / DISABLE / DELETE ---------------- */
if(isset($_GET['toggle_id'])){
    $pid = intval($_GET['toggle_id']);
    $row = $con->query("SELECT status FROM products WHERE id=$pid")->fetch_assoc();
    if($row){
        $newStatus = ($row['status']==='active')?'disabled':'active';
        $con->query("UPDATE products SET status='$newStatus' WHERE id=$pid");
        header("Location: admin_manage-products.php");
        exit;
    }
}
if(isset($_GET['delete_id'])){
    $pid = intval($_GET['delete_id']);
    $con->query("DELETE FROM products WHERE id=$pid");
    header("Location: admin_manage-products.php");
    exit;
}
?>

<div class="container mt-4">
    <h3>Manage Products</h3>

    <?php 
    if(isset($_GET['msg'])) {
        echo '<div class="alert alert-success alert-dismissible fade show">' . htmlspecialchars($_GET['msg']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    if($msg): 
    ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-md-6">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fa fa-plus me-2"></i>Add Product
            </button>
        </div>
        <div class="col-md-6">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search products by name, brand, category..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Search
                </button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="admin_manage-products.php" class="btn btn-secondary ms-2">
                        <i class="fa fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if (!empty($searchTerm)): ?>
        <div class="alert alert-info">
            Showing results for: <strong><?= htmlspecialchars($searchTerm) ?></strong>
        </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Title</th><th>Brand</th><th>Main</th><th>Sub</th>
                <th>Price</th><th>Stock</th><th>Status</th><th>Images</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row=$res->fetch_assoc()): 
            $status = $row['status'] ?? 'inactive';
        ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['category_brand']) ?></td>
                <td><?= htmlspecialchars($row['category_main']) ?></td>
                <td><?= htmlspecialchars($row['category_sub']) ?></td>
                <td><?= number_format($row['price'],2) ?></td>
                <td>
                    <form method="POST" class="d-flex">
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <input type="number" name="stock" value="<?= $row['stock'] ?>" class="form-control form-control-sm me-2" style="width:70px;">
                        <button type="submit" name="update_stock" class="btn btn-sm btn-primary">Update</button>
                    </form>
                </td>
                <td>
                    <span class="badge <?= $status==='active'?'bg-success':'bg-secondary' ?>"><?= ucfirst($status) ?></span>
                </td>
                <td>
                    <?php
                    $imgs = json_decode($row['images'],true);
                    if($imgs){
                        foreach($imgs as $im){
                            echo "<img src='../$im' width='45' class='me-1 mb-1'>";
                        }
                    }
                    ?>
                </td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-dark dropdown-toggle" data-bs-toggle="dropdown">Manage</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['id'] ?>">👁 View</a></li>
                            <li><a class="dropdown-item" href="admin_edit-product.php?id=<?= $row['id'] ?>">✏ Edit</a></li>
                            <li><a class="dropdown-item text-danger" href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Delete product?');">🗑 Delete</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if($status==='active'): ?>
                                <li><a class="dropdown-item text-warning" href="?toggle_id=<?= $row['id'] ?>">🚫 Disable</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item text-success" href="?toggle_id=<?= $row['id'] ?>">✅ Enable</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- VIEW MODAL -->
                    <div class="modal fade" id="viewModal<?= $row['id'] ?>" tabindex="-1">
                      <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title"><?= htmlspecialchars($row['title']) ?></h5>
                            <button class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <p><strong>Brand:</strong> <?= htmlspecialchars($row['category_brand']) ?></p>
                            <p><strong>Main Category:</strong> <?= htmlspecialchars($row['category_main']) ?></p>
                            <p><strong>Sub Category:</strong> <?= htmlspecialchars($row['category_sub']) ?></p>
                            <p><strong>Price:</strong> ₹<?= number_format($row['price'],2) ?></p>
                            <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>
                            <hr>
                            <p><strong>Images:</strong></p>
                            <div class="d-flex flex-wrap">
                            <?php if($imgs): foreach($imgs as $im): ?>
                                <img src="../<?= $im ?>" width="100" class="me-2 mb-2 img-thumbnail">
                            <?php endforeach; endif; ?>
                          </div>
                          </div>
                          <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>

                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- ADD PRODUCT MODAL (unchanged from your code) -->
<div class="modal fade" id="addProductModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_product">
        <div class="modal-header">
          <h5>Add Product</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <script> var catMap = <?= json_encode($catMap) ?>; 
          function loadSubs(mainId){ let sub=document.getElementById("subcat"); sub.innerHTML="<option value=''>-- Select --</option>"; if(catMap[mainId]) catMap[mainId].forEach(v=>sub.innerHTML+=`<option value="${v}">${v}</option>`);}
          </script>
          <div class="row">
            <div class="col-md-6"><label>Title</label><input name="title" class="form-control" required></div>
            <div class="col-md-6"><label>Price</label><input type="number" step="0.01" name="price" class="form-control"></div>
          </div>
          <div class="row mt-2">
            <div class="col-md-3"><label>Brand</label>
              <select name="category_brand" class="form-control"><option value="">-- Select --</option>
                <?php foreach($brands as $b): ?><option><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3"><label>Main Category</label>
              <select name="category_main" class="form-control" onchange="loadSubs(this.value)"><option value="">-- Select --</option>
                <?php foreach($mainCats as $m): ?><option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3"><label>Subcategory</label><select name="category_sub" id="subcat" class="form-control"></select></div>
            <div class="col-md-3"><label>Type</label><input name="category_type" class="form-control"></div>
          </div>
          <div class="row mt-2">
            <div class="col-md-4"><label>Sizes</label><input name="sizes" class="form-control"></div>
            <div class="col-md-4"><label>Fabric</label><input name="fabric" class="form-control"></div>
            <div class="col-md-4"><label>Stock</label><input name="stock" type="number" value="0" class="form-control"></div>
          </div>
          <label class="mt-2">Highlight</label><textarea name="highlight" class="form-control"></textarea>
          <label class="mt-2">Description</label><textarea name="description" class="form-control"></textarea>
          <label class="mt-2">Images</label><input type="file" name="images[]" multiple class="form-control">
          <label class="mt-2">Status</label>
          <select name="status" class="form-control"><option value="active">Active</option><option value="disabled">Disabled</option></select>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Save</button><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
      </form>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
