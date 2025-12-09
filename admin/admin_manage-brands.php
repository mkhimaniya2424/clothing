<?php
ob_start();
require_once 'admin_auth.php';
require_once '../db_connect.php';

// Fetch all brands
$brands = $con->query("SELECT * FROM brands ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$msg = $errors = '';

// HANDLE ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // --- ADD BRAND ---
    if ($_POST['action'] === 'add_brand') {
        $name = trim($_POST['name'] ?? '');
        $logoPath = '';

        if ($name === '') $errors = 'Brand name is required.';

        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['logo']['tmp_name'];
            $fileName = $_FILES['logo']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];

            if (!in_array($fileExt, $allowed)) $errors = 'Only JPG, JPEG, PNG, GIF, WEBP allowed for logo.';
            else {
                $newFileName = uniqid("logo_") . '.' . $fileExt;
                $uploadDir = __DIR__ . "/../uploads/brands/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $dest = $uploadDir . $newFileName;
                if (!move_uploaded_file($fileTmpPath, $dest)) $errors = 'Error uploading logo.';
                else $logoPath = "uploads/brands/" . $newFileName;
            }
        }

        if (!$errors) {
            $stmt = $con->prepare("INSERT INTO brands (name, logo) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $logoPath);
            if ($stmt->execute()) $msg = 'Brand added successfully.';
            else $errors = 'DB Error: ' . $con->error;
            $stmt->close();
        }
    }

    // --- EDIT BRAND ---
    if ($_POST['action'] === 'edit_brand') {
        $id = intval($_POST['brand_id']);
        $name = trim($_POST['name'] ?? '');
        
        if ($name === '') $errors = 'Brand name is required.';
        
        // Get current logo
        $curr = $con->query("SELECT logo FROM brands WHERE id=$id")->fetch_assoc();
        $logoPath = $curr['logo'];

        // Handle new logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['logo']['tmp_name'];
            $fileName = $_FILES['logo']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];

            if (!in_array($fileExt, $allowed)) $errors = 'Only JPG, JPEG, PNG, GIF, WEBP allowed for logo.';
            else {
                $newFileName = uniqid("logo_") . '.' . $fileExt;
                $uploadDir = __DIR__ . "/../uploads/brands/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $dest = $uploadDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest)) {
                    $logoPath = "uploads/brands/" . $newFileName;
                    // Optional: Delete old logo if it exists and is not empty
                    if(!empty($curr['logo']) && file_exists(__DIR__ . "/../" . $curr['logo'])){
                        unlink(__DIR__ . "/../" . $curr['logo']);
                    }
                } else {
                    $errors = 'Error uploading logo.';
                }
            }
        }

        if (!$errors) {
            $stmt = $con->prepare("UPDATE brands SET name=?, logo=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $logoPath, $id);
            if ($stmt->execute()) $msg = 'Brand updated successfully.';
            else $errors = 'DB Error: ' . $con->error;
            $stmt->close();
        }
    }
    
    // Refresh list
    $brands = $con->query("SELECT * FROM brands ORDER BY name")->fetch_all(MYSQLI_ASSOC);
}

// --- DELETE BRAND ---
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    // Get logo to delete file
    $curr = $con->query("SELECT logo FROM brands WHERE id=$id")->fetch_assoc();
    if($curr && !empty($curr['logo']) && file_exists(__DIR__ . "/../" . $curr['logo'])){
        unlink(__DIR__ . "/../" . $curr['logo']);
    }
    
    $con->query("DELETE FROM brands WHERE id=$id");
    header("Location: admin_manage-brands.php?msg=Brand deleted");
    exit;
}

if(isset($_GET['msg'])) $msg = $_GET['msg'];
?>

<div class="container mt-4">
    <h3>Manage Brands</h3>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors) ?></div>
    <?php endif; ?>

    <!-- Add Brand Button -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addBrandModal">Add Brand</button>

    <!-- Brands Table -->
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Brand Name</th>
                <th>Logo</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($brands as $b): ?>
                <tr>
                    <td><?= $b['id'] ?></td>
                    <td><?= htmlspecialchars($b['name']) ?></td>
                    <td>
                        <?php if ($b['logo']): ?>
                            <img src="../<?= htmlspecialchars($b['logo']) ?>" alt="Logo" height="50">
                        <?php else: ?>
                            <em class="text-muted">No logo</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary me-2" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editBrandModal" 
                                onclick="openEditModal(<?= $b['id'] ?>, '<?= addslashes($b['name']) ?>')">
                            <i class="fa fa-edit"></i> Edit
                        </button>
                        <a href="?delete_id=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this brand?');">
                            <i class="fa fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Brand Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_brand">
        <div class="modal-header">
            <h5 class="modal-title">Add New Brand</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Brand Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Brand Logo</label>
                <input type="file" name="logo" class="form-control" accept="image/*">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Brand Modal -->
<div class="modal fade" id="editBrandModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_brand">
        <input type="hidden" name="brand_id" id="edit_brand_id">
        <div class="modal-header">
            <h5 class="modal-title">Edit Brand</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Brand Name</label>
                <input type="text" name="name" id="edit_brand_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Change Logo (Optional)</label>
                <input type="file" name="logo" class="form-control" accept="image/*">
                <small class="text-muted">Leave empty to keep current logo</small>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary">Update</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openEditModal(id, name) {
    document.getElementById('edit_brand_id').value = id;
    document.getElementById('edit_brand_name').value = name;
}
</script>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
