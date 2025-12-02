<?php
ob_start();
require_once 'admin_auth.php';
require_once 'db_connect.php';

// Fetch all brands
$brands = $con->query("SELECT * FROM brands ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Handle Add Brand form submission
$msg = $errors = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_brand') {
    $name = trim($_POST['name'] ?? '');
    $logoPath = '';

    if ($name === '') $errors = 'Brand name is required.';

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['logo']['tmp_name'];
        $fileName = $_FILES['logo']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (!in_array($fileExt, $allowed)) $errors = 'Only JPG, JPEG, PNG, GIF allowed for logo.';
        else {
            $newFileName = uniqid("logo_") . '.' . $fileExt;
            $uploadDir = __DIR__ . "/uploads/brands/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $dest = $uploadDir . $newFileName;
            if (!move_uploaded_file($fileTmpPath, $dest)) $errors = 'Error uploading logo.';
            else $logoPath = "uploads/brands/" . $newFileName;
        }
    }

    // Insert into DB
    if (!$errors) {
        $stmt = $con->prepare("INSERT INTO brands (name, logo) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $logoPath);
        if ($stmt->execute()) $msg = 'Brand added successfully.';
        else $errors = 'DB Error: ' . $con->error;
        $stmt->close();

        // Refresh brands list
        $brands = $con->query("SELECT * FROM brands ORDER BY name")->fetch_all(MYSQLI_ASSOC);
    }
}
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
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Brand Name</th>
                <th>Logo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($brands as $b): ?>
                <tr>
                    <td><?= $b['id'] ?></td>
                    <td><?= htmlspecialchars($b['name']) ?></td>
                    <td>
                        <?php if ($b['logo']): ?>
                            <img src="<?= htmlspecialchars($b['logo']) ?>" alt="Logo" height="50">
                        <?php else: ?>
                            <em>No logo</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Brand Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_brand">
        <div class="modal-header">
          <h5 class="modal-title" id="addBrandModalLabel">Add New Brand</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
