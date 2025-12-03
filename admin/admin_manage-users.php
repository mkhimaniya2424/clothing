<?php
ob_start();
require_once 'admin_auth.php';
require_once '../db_connect.php';

$msg = '';

// Delete User
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $con->query("DELETE FROM users WHERE id=$id");
    $msg = "User deleted.";
}

// Toggle Status
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $con->query("UPDATE users SET status = IF(status='active','inactive','active') WHERE id=$id");
    $msg = "User status updated.";
}

// Fetch Users
$users = $con->query("SELECT * FROM users ORDER BY id DESC");
?>

<div class="container mt-4">
    <h3>Manage Users</h3>
    
    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Joined At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td>
                    <span class="badge <?= $row['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="?toggle=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white">Toggle Status</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete user?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
