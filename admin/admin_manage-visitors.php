<?php
ob_start();
require_once 'admin_auth.php';
require_once '../db_connect.php';

// Fetch Visitors
$sql = "SELECT * FROM visitors ORDER BY id DESC LIMIT 100";
$res = $con->query($sql);

// Count unique visitors today
$today = date('Y-m-d');
$todayCount = $con->query("SELECT COUNT(DISTINCT ip_address) as cnt FROM visitors WHERE visit_date='$today'")->fetch_assoc()['cnt'];

// Count total visits
$totalCount = $con->query("SELECT COUNT(*) as cnt FROM visitors")->fetch_assoc()['cnt'];
?>

<div class="container mt-4">
    <h3>Manage Visitors</h3>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Unique Visitors Today</h5>
                    <h2 class="fw-bold"><?= $todayCount ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-secondary text-white shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Logged Visits</h5>
                    <h2 class="fw-bold"><?= $totalCount ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Recent Visitors (Last 100)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>IP Address</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Page</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res && $res->num_rows > 0): ?>
                            <?php while($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['ip_address']) ?></td>
                                <td><?= $row['visit_date'] ?></td>
                                <td><?= $row['visit_time'] ?></td>
                                <td class="text-truncate" style="max-width: 300px;"><?= htmlspecialchars($row['page_url']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No visitors logged yet.</td></tr>
                        <?php endif; ?>
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
