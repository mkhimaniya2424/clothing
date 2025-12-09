<?php
ob_start();
require_once 'admin_auth.php';
require_once '../db_connect.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = intval($_POST['return_id']);
    $status = $_POST['status'];
    
    $stmt = $con->prepare("UPDATE return_requests SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: admin_manage-returns.php?msg=Status updated successfully");
    exit;
}

// Check if return_requests table exists, create if not
$tableCheck = $con->query("SHOW TABLES LIKE 'return_requests'");
if ($tableCheck->num_rows == 0) {
    // Create the table automatically
    $createTable = "CREATE TABLE return_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        user_id INT NOT NULL,
        reason VARCHAR(100) NOT NULL,
        comments TEXT,
        status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_order_id (order_id),
        INDEX idx_user_id (user_id),
        INDEX idx_status (status)
    )";
    $con->query($createTable);
}

// Fetch all return requests with order and user details
$statusFilter = $_GET['status_filter'] ?? 'all';

$query = "SELECT rr.*, o.final_amount, o.order_status, u.username, u.email 
          FROM return_requests rr
          LEFT JOIN orders o ON rr.order_id = o.id
          LEFT JOIN users u ON rr.user_id = u.id";

if ($statusFilter !== 'all') {
    $query .= " WHERE rr.status = '" . $con->real_escape_string($statusFilter) . "'";
}
$query .= " ORDER BY rr.created_at DESC";

$result = $con->query($query);

// Get statistics
$stats = [
    'total' => $con->query("SELECT COUNT(*) as count FROM return_requests")->fetch_assoc()['count'],
    'pending' => $con->query("SELECT COUNT(*) as count FROM return_requests WHERE status='pending'")->fetch_assoc()['count'],
    'approved' => $con->query("SELECT COUNT(*) as count FROM return_requests WHERE status='approved'")->fetch_assoc()['count'],
    'rejected' => $con->query("SELECT COUNT(*) as count FROM return_requests WHERE status='rejected'")->fetch_assoc()['count'],
    'completed' => $con->query("SELECT COUNT(*) as count FROM return_requests WHERE status='completed'")->fetch_assoc()['count']
];

$msg = $_GET['msg'] ?? '';
?>

<style>
.stat-card {
    border-left: 4px solid;
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.return-card {
    transition: all 0.3s ease;
    border-left: 4px solid #e2e8f0;
}
.return-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-left-color: #667eea;
}
</style>

<div class="container-fluid mt-4">
    <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle me-2"></i><?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fa fa-undo me-2"></i>Return Requests</h2>
            <p class="text-muted mb-0">Manage customer return and exchange requests</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #6c757d;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Requests</p>
                            <h3 class="mb-0"><?= $stats['total'] ?></h3>
                        </div>
                        <div class="text-secondary">
                            <i class="fa fa-undo fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #ffc107;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Pending</p>
                            <h3 class="mb-0 text-warning"><?= $stats['pending'] ?></h3>
                        </div>
                        <div class="text-warning">
                            <i class="fa fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #17a2b8;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Approved</p>
                            <h3 class="mb-0 text-info"><?= $stats['approved'] ?></h3>
                        </div>
                        <div class="text-info">
                            <i class="fa fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #dc3545;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Rejected</p>
                            <h3 class="mb-0 text-danger"><?= $stats['rejected'] ?></h3>
                        </div>
                        <div class="text-danger">
                            <i class="fa fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #28a745;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Completed</p>
                            <h3 class="mb-0 text-success"><?= $stats['completed'] ?></h3>
                        </div>
                        <div class="text-success">
                            <i class="fa fa-check-double fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="?status_filter=all" class="btn <?= $statusFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <i class="fa fa-list me-1"></i>All Requests
                </a>
                <a href="?status_filter=pending" class="btn <?= $statusFilter === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">
                    <i class="fa fa-clock me-1"></i>Pending
                </a>
                <a href="?status_filter=approved" class="btn <?= $statusFilter === 'approved' ? 'btn-info' : 'btn-outline-info' ?>">
                    <i class="fa fa-check-circle me-1"></i>Approved
                </a>
                <a href="?status_filter=rejected" class="btn <?= $statusFilter === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">
                    <i class="fa fa-times-circle me-1"></i>Rejected
                </a>
                <a href="?status_filter=completed" class="btn <?= $statusFilter === 'completed' ? 'btn-success' : 'btn-outline-success' ?>">
                    <i class="fa fa-check-double me-1"></i>Completed
                </a>
            </div>
        </div>
    </div>

    <!-- Return Requests List -->
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($return = $result->fetch_assoc()): 
            $statusClass = 'secondary';
            $statusIcon = 'fa-question';
            switch($return['status']) {
                case 'pending': 
                    $statusClass = 'warning'; 
                    $statusIcon = 'fa-clock';
                    break;
                case 'approved': 
                    $statusClass = 'info'; 
                    $statusIcon = 'fa-check-circle';
                    break;
                case 'rejected': 
                    $statusClass = 'danger'; 
                    $statusIcon = 'fa-times-circle';
                    break;
                case 'completed': 
                    $statusClass = 'success'; 
                    $statusIcon = 'fa-check-double';
                    break;
            }
        ?>
        <div class="card return-card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <small class="text-muted d-block">Order ID</small>
                        <div class="fw-bold">#<?= str_pad($return['order_id'], 6, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-2">
                                <i class="fa fa-user text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-bold small"><?= htmlspecialchars($return['username']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($return['email']) ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Reason</small>
                        <div><?= ucfirst(str_replace('_', ' ', $return['reason'])) ?></div>
                    </div>
                    <div class="col-md-2 text-center">
                        <small class="text-muted d-block">Amount</small>
                        <div class="fw-bold text-success">₹<?= number_format($return['final_amount'], 2) ?></div>
                    </div>
                    <div class="col-md-2 text-center">
                        <small class="text-muted d-block">Date</small>
                        <div><?= date('M d, Y', strtotime($return['created_at'])) ?></div>
                    </div>
                    <div class="col-md-1 text-center">
                        <span class="badge bg-<?= $statusClass ?>">
                            <i class="fa <?= $statusIcon ?> me-1"></i><?= ucfirst($return['status']) ?>
                        </span>
                    </div>
                    <div class="col-md-1 text-end">
                        <button class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#returnModal<?= $return['id'] ?>" title="View Details">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Details Modal -->
        <div class="modal fade" id="returnModal<?= $return['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <div>
                            <h5 class="modal-title mb-1">Return Request #<?= $return['id'] ?></h5>
                            <small class="text-muted">Submitted on <?= date('F j, Y \a\t g:i A', strtotime($return['created_at'])) ?></small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Customer:</strong> <?= htmlspecialchars($return['username']) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($return['email']) ?>"><?= htmlspecialchars($return['email']) ?></a>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Order ID:</strong> #<?= str_pad($return['order_id'], 6, '0', STR_PAD_LEFT) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Order Amount:</strong> ₹<?= number_format($return['final_amount'], 2) ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Return Reason:</strong> <?= ucfirst(str_replace('_', ' ', $return['reason'])) ?>
                        </div>
                        
                        <?php if (!empty($return['comments'])): ?>
                        <div class="mb-3">
                            <strong>Additional Comments:</strong>
                            <div class="bg-light p-3 rounded mt-2">
                                <?= nl2br(htmlspecialchars($return['comments'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <form method="POST" class="border-top pt-3">
                            <input type="hidden" name="return_id" value="<?= $return['id'] ?>">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label">Update Status</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" <?= $return['status'] == 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                        <option value="approved" <?= $return['status'] == 'approved' ? 'selected' : '' ?>>✅ Approved</option>
                                        <option value="rejected" <?= $return['status'] == 'rejected' ? 'selected' : '' ?>>❌ Rejected</option>
                                        <option value="completed" <?= $return['status'] == 'completed' ? 'selected' : '' ?>>✔️ Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                                        <i class="fa fa-save me-2"></i>Update Status
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="fa fa-inbox fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No return requests found</h5>
                <p class="text-muted">There are no return requests matching your filter.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
