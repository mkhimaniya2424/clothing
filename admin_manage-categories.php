<?php
ob_start();
require_once __DIR__ . 'db_connect.php';

/* -------------------------
   AUTO-CREATE Main Categories
--------------------------*/
$mainCategories = ['Men', 'Women', 'Children'];
foreach ($mainCategories as $catName) {
    $stmt = $con->prepare("SELECT id FROM categories WHERE parent_id IS NULL AND name=?");
    $stmt->bind_param("s", $catName);
    $stmt->execute(); 
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add_category') {
    $name = trim($_POST['name']);
    $parent_id = ($_POST['parent_id'] !== '') ? intval($_POST['parent_id']) : NULL;

    $stmt = $con->prepare("INSERT INTO categories (name, parent_id, status) VALUES (?, ?, 1)");
    $stmt->bind_param("si", $name, $parent_id);

    $msg = $stmt->execute() ? "Category added successfully!" : "Error: ".$stmt->error;
    $stmt->close();
}

/* -------------------------
   ENABLE / DISABLE
--------------------------*/
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $con->query("UPDATE categories SET status = 1 - status WHERE id=$id");
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
while ($r = $res->fetch_assoc()) $cats[] = $r;

$mainCats = array_filter($cats, fn($c) => $c['parent_id'] === NULL);
?>

<div class="container mt-4">
    <h3 class="mb-3">Manage Categories</h3>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        + Add Category
    </button>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th width="20%">Main Category</th>
                <th width="45%">Subcategories</th>
                <th width="15%">Status</th>
                <th width="20%">Actions</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($mainCats as $mainCat): ?>
            <?php $subs = array_filter($cats, fn($c) => $c['parent_id'] == $mainCat['id']); ?>

            <tr>
                <td><strong><?= htmlspecialchars($mainCat['name']) ?></strong></td>

                <td>
                    <?php if ($subs): ?>
                        <?php foreach ($subs as $s): ?>
                            <span class="badge bg-primary mb-1"><?= htmlspecialchars($s['name']) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <em>No subcategories</em>
                    <?php endif; ?>
                </td>

                <td>
                    <?= ($mainCat['status'])
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Disabled</span>' ?>
                </td>

                <td>
                    <a href="?toggle=<?= $mainCat['id'] ?>" class="btn btn-sm btn-info">Enable/Disable</a>

                    <a href="?delete=<?= $mainCat['id'] ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this category?')">
                        Delete
                    </a>
                </td>
            </tr>

            <!-- SUBCATEGORY ROWS -->
            <?php foreach ($subs as $s): ?>
                <tr>
                    <td>— <?= htmlspecialchars($s['name']) ?></td>
                    <td><em>Subcategory</em></td>
                    <td>
                        <?= ($s['status'])
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-secondary">Disabled</span>' ?>
                    </td>
                    <td>
                        <a href="?toggle=<?= $s['id'] ?>" class="btn btn-sm btn-info">Enable/Disable</a>

                        <a href="?delete=<?= $s['id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete this subcategory?')">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ADD CATEGORY MODAL -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="action" value="add_category">

        <div class="modal-header">
          <h5 class="modal-title">Add Category / Subcategory</h5>
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
include_once("layout1.php");
?>
