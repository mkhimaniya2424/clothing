<?php
ob_start();
require_once 'db_connect.php';

// Mark message as read/unread
if (isset($_GET['toggle_id'])) {
    $toggleId = intval($_GET['toggle_id']);

    $stmt = $con->prepare("SELECT status FROM messages WHERE id=?");
    $stmt->bind_param("i", $toggleId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $newStatus = ($row['status'] === 'unread') ? 'read' : 'unread';
        $updateStmt = $con->prepare("UPDATE messages SET status=? WHERE id=?");
        $updateStmt->bind_param("si", $newStatus, $toggleId);
        $updateStmt->execute();
        $updateStmt->close();
    }

    $stmt->close();
    header("Location: admin_message.php");
    exit;
}

// Fetch all messages
$result = $con->query("SELECT * FROM messages ORDER BY created_at DESC");
$messages = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-4">
    <h2>Messages</h2>

    <?php if (!empty($messages)): ?>
        <div class="list-group mt-3">
            <?php foreach ($messages as $m): ?>
                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center
                    <?= $m['status'] === 'unread' ? 'list-group-item-warning' : '' ?>">
                    <div>
                        <strong><?= htmlspecialchars($m['from_user']) ?></strong> 
                        (<?= htmlspecialchars($m['email']) ?>)<br>
                        <strong>Subject:</strong> <?= htmlspecialchars($m['subject']) ?><br>
                        <small><?= nl2br(htmlspecialchars($m['message'])) ?></small>
                    </div>
                    <div class="text-end">
                        <span class="badge <?= $m['status'] === 'unread' ? 'bg-danger' : 'bg-success' ?>">
                            <?= $m['status'] ?>
                        </span><br>
                        <a href="?toggle_id=<?= $m['id'] ?>" class="btn btn-sm btn-primary mt-1">
                            Mark as <?= $m['status'] === 'unread' ? 'Read' : 'Unread' ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info mt-3">No messages found!</div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
