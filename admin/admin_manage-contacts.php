<?php
ob_start();
require_once '../db_connect.php';
require_once '../email_helper.php';
require_once 'admin_auth.php';

$title_page = "Manage Contact Messages";
$replySuccess = '';
$replyError = '';

// Handle reply submission
if (isset($_POST['send_reply'])) {
    $messageId = $_POST['message_id'];
    $replyMessage = trim($_POST['reply_message']);
    
    // Get message details
    $stmt = $con->prepare("SELECT name, email, subject FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $messageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $message = $result->fetch_assoc();
    $stmt->close();
    
    if ($message && !empty($replyMessage)) {
        // Send email reply
        $subject = "Re: " . $message['subject'];
        $body = "<p>Dear " . htmlspecialchars($message['name']) . ",</p>";
        $body .= "<p>" . nl2br(htmlspecialchars($replyMessage)) . "</p>";
        $body .= "<hr>";
        $body .= "<p><small>This is a reply to your message regarding: <strong>" . htmlspecialchars($message['subject']) . "</strong></small></p>";
        $body .= "<p><small>Best regards,<br>Clothing Brand Support Team</small></p>";
        
        if (sendEmail($message['email'], $subject, $body)) {
            // Update status to replied
            $stmt = $con->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
            $stmt->bind_param("i", $messageId);
            $stmt->execute();
            $stmt->close();
            
            $replySuccess = "Reply sent successfully to " . htmlspecialchars($message['email']);
        } else {
            $replyError = "Failed to send email. Please try again.";
        }
    }
}

// Handle status update
if (isset($_POST['update_status'])) {
    $messageId = $_POST['message_id'];
    $newStatus = $_POST['status'];
    
    $stmt = $con->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $messageId);
    $stmt->execute();
    $stmt->close();
    
    header("Location: admin_manage-contacts.php");
    exit();
}

// Handle delete
if (isset($_POST['delete_message'])) {
    $messageId = $_POST['message_id'];
    
    $stmt = $con->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $messageId);
    $stmt->execute();
    $stmt->close();
    
    header("Location: admin_manage-contacts.php");
    exit();
}

// Handle search and filters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT * FROM contact_messages WHERE 1=1";

if ($statusFilter !== 'all') {
    $query .= " AND status = '" . $con->real_escape_string($statusFilter) . "'";
}

if (!empty($searchTerm)) {
    $searchEscaped = $con->real_escape_string($searchTerm);
    $query .= " AND (name LIKE '%$searchEscaped%' OR email LIKE '%$searchEscaped%' OR subject LIKE '%$searchEscaped%' OR message LIKE '%$searchEscaped%')";
}

$query .= " ORDER BY created_at DESC";

$messages = mysqli_query($con, $query);

// Get statistics
$stats = [
    'total' => mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM contact_messages"))['count'],
    'pending' => mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'pending'"))['count'],
    'read' => mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'read'"))['count'],
    'replied' => mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'replied'"))['count']
];
?>

<style>
.stats-card {
    border-radius: 15px;
    padding: 25px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}
.filter-btn {
    border-radius: 25px;
    padding: 8px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}
.filter-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}
.message-card {
    border: none;
    border-radius: 12px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
    background: white;
}
.message-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.message-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.message-body {
    padding: 20px;
}
.status-badge {
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}
.status-pending {
    background: #fef3c7;
    color: #92400e;
}
.status-read {
    background: #dbeafe;
    color: #1e40af;
}
.status-replied {
    background: #d1fae5;
    color: #065f46;
}
.search-box {
    border-radius: 25px;
    padding: 10px 20px;
    border: 2px solid #e2e8f0;
}
.search-box:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
</style>

<!-- Success/Error Messages -->
<?php if (!empty($replySuccess)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fa fa-check-circle me-2"></i>
        <?= htmlspecialchars($replySuccess) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($replyError)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fa fa-exclamation-circle me-2"></i>
        <?= htmlspecialchars($replyError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="fa fa-envelope"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold"><?= $stats['total'] ?></h3>
                    <p class="text-muted mb-0 small">Total Messages</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="fa fa-clock"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold"><?= $stats['pending'] ?></h3>
                    <p class="text-muted mb-0 small">Pending</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="fa fa-eye"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold"><?= $stats['read'] ?></h3>
                    <p class="text-muted mb-0 small">Read</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold"><?= $stats['replied'] ?></h3>
                    <p class="text-muted mb-0 small">Replied</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="btn-group" role="group">
                    <a href="?status=all<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" 
                       class="filter-btn btn btn-outline-primary <?= $statusFilter === 'all' ? 'active' : '' ?>">
                        All (<?= $stats['total'] ?>)
                    </a>
                    <a href="?status=pending<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" 
                       class="filter-btn btn btn-outline-warning <?= $statusFilter === 'pending' ? 'active' : '' ?>">
                        Pending (<?= $stats['pending'] ?>)
                    </a>
                    <a href="?status=read<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" 
                       class="filter-btn btn btn-outline-info <?= $statusFilter === 'read' ? 'active' : '' ?>">
                        Read (<?= $stats['read'] ?>)
                    </a>
                    <a href="?status=replied<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" 
                       class="filter-btn btn btn-outline-success <?= $statusFilter === 'replied' ? 'active' : '' ?>">
                        Replied (<?= $stats['replied'] ?>)
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <?php if ($statusFilter !== 'all'): ?>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="form-control search-box" 
                           placeholder="Search by name, email, subject, or message..." 
                           value="<?= htmlspecialchars($searchTerm) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i>
                    </button>
                    <?php if (!empty($searchTerm)): ?>
                        <a href="?status=<?= $statusFilter ?>" class="btn btn-outline-secondary">
                            <i class="fa fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <?php if (!empty($searchTerm)): ?>
            <div class="mt-3">
                <span class="badge bg-info">
                    <i class="fa fa-search me-1"></i>
                    Searching for: "<?= htmlspecialchars($searchTerm) ?>"
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Messages List -->
<?php if (mysqli_num_rows($messages) > 0): ?>
    <?php while ($msg = mysqli_fetch_assoc($messages)): ?>
        <div class="message-card card shadow-sm">
            <div class="message-header">
                <div>
                    <h5 class="mb-1 fw-bold">
                        <i class="fa fa-user-circle text-primary me-2"></i>
                        <?= htmlspecialchars($msg['name']) ?>
                    </h5>
                    <div class="text-muted small">
                        <i class="fa fa-envelope me-1"></i>
                        <?= htmlspecialchars($msg['email']) ?>
                        <?php if (!empty($msg['phone'])): ?>
                            <span class="ms-3">
                                <i class="fa fa-phone me-1"></i>
                                <?= htmlspecialchars($msg['phone']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted small mt-1">
                        <i class="fa fa-clock me-1"></i>
                        <?= date('M d, Y h:i A', strtotime($msg['created_at'])) ?>
                    </div>
                </div>
                <div>
                    <span class="status-badge status-<?= $msg['status'] ?>">
                        <?= ucfirst($msg['status']) ?>
                    </span>
                </div>
            </div>
            <div class="message-body">
                <h6 class="fw-bold mb-2">
                    <i class="fa fa-tag text-secondary me-2"></i>
                    <?= htmlspecialchars($msg['subject']) ?>
                </h6>
                <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                
                <div class="d-flex gap-2 flex-wrap">
                    <!-- Update Status -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                data-bs-toggle="dropdown">
                            <i class="fa fa-edit me-1"></i>Update Status
                        </button>
                        <ul class="dropdown-menu">
                            <?php foreach (['pending', 'read', 'replied'] as $status): ?>
                                <?php if ($status !== $msg['status']): ?>
                                    <li>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                            <input type="hidden" name="status" value="<?= $status ?>">
                                            <button type="submit" name="update_status" class="dropdown-item">
                                                <i class="fa fa-circle text-<?= $status === 'pending' ? 'warning' : ($status === 'read' ? 'info' : 'success') ?> me-2"></i>
                                                Mark as <?= ucfirst($status) ?>
                                            </button>
                                        </form>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <!-- Reply Button (Opens Modal) -->
                    <button type="button" class="btn btn-sm btn-success" 
                            data-bs-toggle="modal" data-bs-target="#replyModal<?= $msg['id'] ?>">
                        <i class="fa fa-reply me-1"></i>Reply
                    </button>
                    
                    <!-- Delete -->
                    <form method="POST" class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to delete this message?');">
                        <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                        <button type="submit" name="delete_message" class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-trash me-1"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Reply Modal -->
        <div class="modal fade" id="replyModal<?= $msg['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-reply me-2"></i>Reply to <?= htmlspecialchars($msg['name']) ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <strong><i class="fa fa-info-circle me-2"></i>Original Message:</strong>
                                <p class="mb-1 mt-2"><strong>Subject:</strong> <?= htmlspecialchars($msg['subject']) ?></p>
                                <p class="mb-0"><strong>From:</strong> <?= htmlspecialchars($msg['email']) ?></p>
                            </div>
                            
                            <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Your Reply <span class="text-danger">*</span></label>
                                <textarea name="reply_message" class="form-control" rows="8" 
                                          placeholder="Type your reply here..." required></textarea>
                                <small class="text-muted">
                                    This reply will be sent to: <?= htmlspecialchars($msg['email']) ?>
                                </small>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> After sending, the message status will automatically be updated to "Replied".
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fa fa-times me-1"></i>Cancel
                            </button>
                            <button type="submit" name="send_reply" class="btn btn-success">
                                <i class="fa fa-paper-plane me-1"></i>Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <?php endwhile; ?>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fa fa-inbox fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No messages found</h5>
            <p class="text-muted mb-0">
                <?php if (!empty($searchTerm)): ?>
                    Try adjusting your search terms or filters.
                <?php else: ?>
                    Contact messages will appear here when customers submit the contact form.
                <?php endif; ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
