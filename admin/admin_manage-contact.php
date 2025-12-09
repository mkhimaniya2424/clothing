<?php
ob_start();
require_once 'admin_auth.php';
require_once '../db_connect.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = intval($_POST['message_id']);
    $status = $_POST['status'];
    
    $stmt = $con->prepare("UPDATE contact_messages SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: admin_manage-contact.php?msg=Status updated successfully");
    exit;
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $con->query("DELETE FROM contact_messages WHERE id=$id");
    header("Location: admin_manage-contact.php?msg=Message deleted");
    exit;
}

// Check if contact_messages table exists
$tableCheck = $con->query("SHOW TABLES LIKE 'contact_messages'");
if ($tableCheck->num_rows == 0) {
    // Table doesn't exist, show message
    ob_start();
    ?>
    <div class="container mt-5">
        <div class="alert alert-warning">
            <h4><i class="fa fa-exclamation-triangle me-2"></i>Table Not Found</h4>
            <p>The contact_messages table doesn't exist in the database.</p>
            <a href="create_contact_table.php" class="btn btn-primary">
                <i class="fa fa-database me-2"></i>Create Table Now
            </a>
        </div>
    </div>
    <?php
    $content = ob_get_clean();
    include_once("admin_layout.php");
    exit;
}

// Fetch all contact messages
$statusFilter = $_GET['status_filter'] ?? 'all';

$query = "SELECT * FROM contact_messages";
if ($statusFilter !== 'all') {
    $query .= " WHERE status = '" . $con->real_escape_string($statusFilter) . "'";
}
$query .= " ORDER BY created_at DESC";

$result = $con->query($query);

// Get statistics
$stats = [
    'total' => $con->query("SELECT COUNT(*) as count FROM contact_messages")->fetch_assoc()['count'],
    'pending' => $con->query("SELECT COUNT(*) as count FROM contact_messages WHERE status='pending'")->fetch_assoc()['count'],
    'replied' => $con->query("SELECT COUNT(*) as count FROM contact_messages WHERE status='replied'")->fetch_assoc()['count'],
    'resolved' => $con->query("SELECT COUNT(*) as count FROM contact_messages WHERE status='resolved'")->fetch_assoc()['count']
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
.message-card {
    transition: all 0.3s ease;
    border-left: 4px solid #e2e8f0;
}
.message-card:hover {
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
            <h2 class="mb-1"><i class="fa fa-envelope me-2"></i>Customer Care Messages</h2>
            <p class="text-muted mb-0">Manage customer inquiries and support requests</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #6c757d;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Messages</p>
                            <h3 class="mb-0"><?= $stats['total'] ?></h3>
                        </div>
                        <div class="text-secondary">
                            <i class="fa fa-envelope fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
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
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #17a2b8;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Replied</p>
                            <h3 class="mb-0 text-info"><?= $stats['replied'] ?></h3>
                        </div>
                        <div class="text-info">
                            <i class="fa fa-reply fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #28a745;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Resolved</p>
                            <h3 class="mb-0 text-success"><?= $stats['resolved'] ?></h3>
                        </div>
                        <div class="text-success">
                            <i class="fa fa-check-circle fa-2x"></i>
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
                    <i class="fa fa-list me-1"></i>All Messages
                </a>
                <a href="?status_filter=pending" class="btn <?= $statusFilter === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">
                    <i class="fa fa-clock me-1"></i>Pending
                </a>
                <a href="?status_filter=replied" class="btn <?= $statusFilter === 'replied' ? 'btn-info' : 'btn-outline-info' ?>">
                    <i class="fa fa-reply me-1"></i>Replied
                </a>
                <a href="?status_filter=resolved" class="btn <?= $statusFilter === 'resolved' ? 'btn-success' : 'btn-outline-success' ?>">
                    <i class="fa fa-check-circle me-1"></i>Resolved
                </a>
            </div>
        </div>
    </div>

    <!-- Messages List -->
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($message = $result->fetch_assoc()): 
            $statusClass = 'secondary';
            $statusIcon = 'fa-question';
            switch($message['status']) {
                case 'pending': 
                    $statusClass = 'warning'; 
                    $statusIcon = 'fa-clock';
                    break;
                case 'replied': 
                    $statusClass = 'info'; 
                    $statusIcon = 'fa-reply';
                    break;
                case 'resolved': 
                    $statusClass = 'success'; 
                    $statusIcon = 'fa-check-circle';
                    break;
            }
        ?>
        <div class="card message-card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-2">
                                <i class="fa fa-user text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($message['name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($message['email']) ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Message</small>
                        <div class="text-truncate"><?= htmlspecialchars(substr($message['message'], 0, 100)) ?>...</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <small class="text-muted d-block">Date</small>
                        <div><?= date('M d, Y', strtotime($message['created_at'])) ?></div>
                    </div>
                    <div class="col-md-2 text-center">
                        <span class="badge bg-<?= $statusClass ?>">
                            <i class="fa <?= $statusIcon ?> me-1"></i><?= ucfirst($message['status']) ?>
                        </span>
                    </div>
                    <div class="col-md-1 text-end">
                        <button class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#messageModal<?= $message['id'] ?>" title="View Details">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Details Modal -->
        <div class="modal fade" id="messageModal<?= $message['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <div>
                            <h5 class="modal-title mb-1">Message from <?= htmlspecialchars($message['name']) ?></h5>
                            <small class="text-muted">Received on <?= date('F j, Y \a\t g:i A', strtotime($message['created_at'])) ?></small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Name:</strong> <?= htmlspecialchars($message['name']) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($message['email']) ?>"><?= htmlspecialchars($message['email']) ?></a>
                            </div>
                        </div>
                        
                        <?php if (!empty($message['phone'])): ?>
                        <div class="mb-3">
                            <strong>Phone:</strong> <a href="tel:<?= htmlspecialchars($message['phone']) ?>"><?= htmlspecialchars($message['phone']) ?></a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <strong>Subject:</strong> <?= htmlspecialchars($message['subject'] ?? 'No Subject') ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Message:</strong>
                            <div class="bg-light p-3 rounded mt-2">
                                <?= nl2br(htmlspecialchars($message['message'])) ?>
                            </div>
                        </div>

                        <form method="POST" class="border-top pt-3">
                            <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label">Update Status</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" <?= $message['status'] == 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                        <option value="replied" <?= $message['status'] == 'replied' ? 'selected' : '' ?>>💬 Replied</option>
                                        <option value="resolved" <?= $message['status'] == 'resolved' ? 'selected' : '' ?>>✅ Resolved</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                                        <i class="fa fa-save me-2"></i>Update
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <a href="?delete_id=<?= $message['id'] ?>" class="btn btn-danger w-100" onclick="return confirm('Delete this message?');">
                                        <i class="fa fa-trash me-2"></i>Delete
                                    </a>
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
                <h5 class="text-muted">No messages found</h5>
                <p class="text-muted">There are no customer care messages matching your filter.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
