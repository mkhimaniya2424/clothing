<?php
ob_start();
require_once __DIR__ . '/../db/db_connect.php';
$msg = '';

// Handle AJAX insert (if form submitted via modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modal_submit'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $discount = floatval($_POST['discount'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $status = $_POST['status'] ?? 'active';

    if ($title !== '' && $start_date !== '' && $end_date !== '') {
        $stmt = $con->prepare("INSERT INTO offers (title, description, discount_percentage, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsss", $title, $description, $discount, $start_date, $end_date, $status);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch offers
$res = $con->query("SELECT * FROM offers ORDER BY created_at DESC");
$offers = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="container mt-4">
    <h3>Manage Offers</h3>
    <!-- Add Offer Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addOfferModal">Add New Offer</button>

    <!-- Offers Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Discount (%)</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($offers as $o): ?>
            <tr>
                <td><?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['title']) ?></td>
                <td><?= $o['discount_percentage'] ?></td>
                <td><?= $o['start_date'] ?></td>
                <td><?= $o['end_date'] ?></td>
                <td><?= $o['status'] ?></td>
                <td>
                    <button class="btn btn-sm btn-warning editOfferBtn" data-id="<?= $o['id'] ?>" data-title="<?= htmlspecialchars($o['title']) ?>" data-desc="<?= htmlspecialchars($o['description']) ?>" data-discount="<?= $o['discount_percentage'] ?>" data-start="<?= $o['start_date'] ?>" data-end="<?= $o['end_date'] ?>" data-status="<?= $o['status'] ?>">Edit</button>
                    <a href="delete-offer.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this offer?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Offer Modal -->
<div class="modal fade" id="addOfferModal" tabindex="-1" aria-labelledby="addOfferLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
        <input type="hidden" name="modal_submit" value="1">
        <div class="modal-header">
            <h5 class="modal-title" id="addOfferLabel">Add New Offer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3"><label>Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-3"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
            <div class="mb-3"><label>Discount (%)</label><input type="number" step="0.01" name="discount" class="form-control" required></div>
            <div class="mb-3"><label>Start Date</label><input type="date" name="start_date" class="form-control" required></div>
            <div class="mb-3"><label>End Date</label><input type="date" name="end_date" class="form-control" required></div>
            <div class="mb-3"><label>Status</label>
                <select name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save Offer</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
  </div>
</div>

<!-- Edit Offer Modal -->
<div class="modal fade" id="editOfferModal" tabindex="-1" aria-labelledby="editOfferLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editOfferForm" method="post" class="modal-content" action="edit-offer.php">
        <input type="hidden" name="id" id="editOfferId">
        <div class="modal-header">
            <h5 class="modal-title" id="editOfferLabel">Edit Offer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3"><label>Title</label><input type="text" name="title" id="editTitle" class="form-control" required></div>
            <div class="mb-3"><label>Description</label><textarea name="description" id="editDesc" class="form-control"></textarea></div>
            <div class="mb-3"><label>Discount (%)</label><input type="number" step="0.01" name="discount" id="editDiscount" class="form-control" required></div>
            <div class="mb-3"><label>Start Date</label><input type="date" name="start_date" id="editStart" class="form-control" required></div>
            <div class="mb-3"><label>End Date</label><input type="date" name="end_date" id="editEnd" class="form-control" required></div>
            <div class="mb-3"><label>Status</label>
                <select name="status" id="editStatus" class="form-control">
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Update Offer</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('.editOfferBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editOfferId').value = this.dataset.id;
        document.getElementById('editTitle').value = this.dataset.title;
        document.getElementById('editDesc').value = this.dataset.desc;
        document.getElementById('editDiscount').value = this.dataset.discount;
        document.getElementById('editStart').value = this.dataset.start;
        document.getElementById('editEnd').value = this.dataset.end;
        document.getElementById('editStatus').value = this.dataset.status;
        new bootstrap.Modal(document.getElementById('editOfferModal')).show();
    });
});
</script>

<?php
$content = ob_get_clean();
include_once("layout1.php");
?>
