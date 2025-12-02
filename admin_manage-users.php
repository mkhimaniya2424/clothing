<?php
$title_page = "Manage Users";
ob_start();
include_once("db_connect.php");

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($con, "DELETE FROM users WHERE id=$id");
    header("Location: admin_manage-users.php");
    exit;
}

// Handle Status Change
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'] == 'active' ? 'active' : 'inactive';
    mysqli_query($con, "UPDATE users SET status='$status' WHERE id=$id");
    header("Location: admin_manage-users.php");
    exit;
}

$users = mysqli_query($con, "SELECT * FROM users ORDER BY created_at DESC");
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Users</h2>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-2 me-2">
                                        <i class="fa fa-user"></i>
                                    </div>
                                    <?= htmlspecialchars($row['username']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td>
                                <?php if($row['status'] == 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?php if($row['status'] == 'active'): ?>
                                    <a href="?id=<?= $row['id'] ?>&status=inactive" class="btn btn-sm btn-warning" title="Deactivate"><i class="fa fa-ban"></i></a>
                                <?php else: ?>
                                    <a href="?id=<?= $row['id'] ?>&status=active" class="btn btn-sm btn-success" title="Activate"><i class="fa fa-check"></i></a>
                                <?php endif; ?>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
