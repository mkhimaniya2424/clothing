<?php
ob_start();
require_once 'db_connect.php';

/* -------------------------
   AUTO-CREATE Main Categories (If not exist)
--------------------------*/
$mainCategories = ['Men', 'Women', 'Children'];
foreach ($mainCategories as $catName) {
    $stmt = $con->prepare("SELECT id FROM categories WHERE parent_id IS NULL AND name=?");
    $stmt->bind_param("s", $catName);
    $stmt->execute(); 
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Status 1 = Active
        $stmt2 = $con->prepare("INSERT INTO categories (name, parent_id, status) VALUES (?, NULL, 1)");
        $stmt2->bind_param("s", $catName);
        $stmt2->execute();
        $stmt2->close();
    }
    $stmt->close();
}

$msg = '';

/* -------------------------
   ADD CATEGORY
--------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = trim($_POST['name']);
    $parent_id = ($_POST['parent_id'] !== '') ? intval($_POST['parent_id']) : NULL;
    
    // Default status 1 (Active)
    $stmt = $con->prepare("INSERT INTO categories (name, parent_id, status, created_at) VALUES (?, ?, 1, NOW())");
    $stmt->bind_param("si", $name, $parent_id);

    if ($stmt->execute()) {
        $msg = "Category added successfully!";
    } else {
        $msg = "Error: " . $stmt->error;
    }
    $stmt->close();
}

/* -------------------------
   ENABLE / DISABLE (Toggle Status)
--------------------------*/
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    // Toggle between 1 and 0
    $con->query("UPDATE categories SET status = IF(status=1, 0, 1) WHERE id=$id");
    $msg = "Category status updated.";
}

/* -------------------------
   DELETE CATEGORY
--------------------------*/
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Check subcategories
    $check = $con->prepare("SELECT id FROM categories WHERE parent_id=?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $msg = "Cannot delete this category. It contains subcategories.";
    } else {
        $con->query("DELETE FROM categories WHERE id=$id");
        $msg = "Category deleted.";
    }
    $check->close();
}

/* -------------------------
   FETCH CATEGORIES
--------------------------*/
$cats = [];
$res = $con->query("SELECT * FROM categories ORDER BY parent_id, name");
if($res) {
    while ($r = $res->fetch_assoc()) $cats[] = $r;
}

$mainCats = array_filter($cats, fn($c) => empty($c['parent_id']));
?>

<div class="container mt-4">
    <h3 class="mb-3">Manage Categories</h3>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        + Add Category
    </button> -->

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th width="50%">Category Name</th>
                <th width="20%">Status</th>
                <th width="30%">Actions</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($mainCats as $mainCat): ?>
            <?php $subs = array_filter($cats, fn($c) => $c['parent_id'] == $mainCat['id']); ?>

            <!-- Main Category Row -->
            <tr class="table-secondary">
                <td class="fw-bold fs-5">
                    <i class="fa fa-folder me-2"></i><?= htmlspecialchars($mainCat['name']) ?>
                </td>
                <td>
                    <?= ($mainCat['status'] == 1)
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>' ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSubModal<?= $mainCat['id'] ?>">
                        <i class="fa fa-plus"></i> Sub
                    </button>
                    <a href="?toggle=<?= $mainCat['id'] ?>" class="btn btn-sm btn-info text-white" title="Toggle Status"><i class="fa fa-power-off"></i></a>
                    <a href="?delete=<?= $mainCat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')" title="Delete"><i class="fa fa-trash"></i></a>
                </td>
            </tr>

            <!-- Subcategory Rows -->
            <?php foreach ($subs as $s): ?>
                <tr>
                    <td class="ps-5">
                        <i class="fa fa-level-up-alt fa-rotate-90 me-2 text-muted"></i><?= htmlspecialchars($s['name']) ?>
                    </td>
                    <td>
                        <?= ($s['status'] == 1)
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-secondary">Inactive</span>' ?>
                    </td>
                    <td>
                        <a href="?toggle=<?= $s['id'] ?>" class="btn btn-sm btn-info text-white" title="Toggle Status"><i class="fa fa-power-off"></i></a>
                        <a href="?delete=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this subcategory?')" title="Delete"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- Modal for Adding Subcategory specifically to this Main Cat -->
            <div class="modal fade" id="addSubModal<?= $mainCat['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post">
                            <input type="hidden" name="action" value="add_category">
                            <input type="hidden" name="parent_id" value="<?= $mainCat['id'] ?>">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Subcategory to <?= htmlspecialchars($mainCat['name']) ?></h5>
                                <button class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">Subcategory Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ADD CATEGORY MODAL (General) -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="action" value="add_category">

        <div class="modal-header">
          <h5 class="modal-title">Add Category</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <label class="form-label">Category Name</label>
          <input type="text" name="name" class="form-control" required>

          <label class="form-label mt-3">Parent Category (Optional)</label>
          <select name="parent_id" class="form-control">
            <option value="">— Main Category —</option>
            <?php foreach ($mainCats as $mc): ?>
                <option value="<?= $mc['id'] ?>"><?= htmlspecialchars($mc['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="modal-footer">
          <button class="btn btn-primary">Save</button>
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>

      </form>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
