<?php
ob_start();
require_once 'db_connect.php';

// Fetch Visitors
$visitors = $con->query("SELECT * FROM visitors ORDER BY id DESC LIMIT 100");
?>

<div class="container mt-4">
    <h3>Recent Visitors</h3>
    
    <div class="alert alert-secondary">
        Showing last 100 visitors.
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>IP Address</th>
                <th>Visit Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($visitors && $visitors->num_rows > 0): ?>
                <?php while($row = $visitors->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['ip_address']) ?></td>
                    <td><?= $row['visit_date'] ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" class="text-center">No visitors recorded yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
