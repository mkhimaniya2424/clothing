<?php
ob_start();
require_once 'admin_auth.php';
require_once 'db_connect.php';

$msg = '';

// Add Offer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_offer') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $discount = floatval($_POST['discount']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $con->prepare("INSERT INTO offers (title, description, discount_percentage, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("ssdss", $title, $description, $discount, $start_date, $end_date);
    
    if ($stmt->execute()) {
        $msg = "Offer added successfully!";
    } else {
        $msg = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Delete Offer
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $con->query("DELETE FROM offers WHERE id=$id");
    $msg = "Offer deleted.";
}

// Toggle Status
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $con->query("UPDATE offers SET status = IF(status='active','disabled','active') WHERE id=$id");
    $msg = "Offer status updated.";
}

// Fetch Offers
$offers = $con->query("SELECT * FROM offers ORDER BY id DESC");
?>

<div class="container mt-4">
    <h3>Manage Offers</h3>
    
    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addOfferModal">
        + Add Offer
    </button>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Discount (%)</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $offers->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $row['discount_percentage'] ?>%</td>
                <td><?= $row['start_date'] ?></td>
                <td><?= $row['end_date'] ?></td>
                <td>
                    <span class="badge <?= $row['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </td>
                <td>
                    <a href="?toggle=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white">Toggle</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete offer?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Offer Modal -->
<div class="modal fade" id="addOfferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="add_offer">
                <div class="modal-header">
                    <h5 class="modal-title">Add Offer</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Discount Percentage</label>
                        <input type="number" step="0.01" name="discount" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
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
