<?php
ob_start();
require_once 'admin_auth.php';
require_once '../db_connect.php';

// Total Revenue (Delivered Orders)
$totalRev = $con->query("SELECT SUM(final_amount) as total FROM orders WHERE order_status='delivered'")->fetch_assoc()['total'] ?? 0;

// Pending Revenue (Processing/Shipped)
$pendingRev = $con->query("SELECT SUM(final_amount) as total FROM orders WHERE order_status IN ('processing', 'shipped')")->fetch_assoc()['total'] ?? 0;

// Monthly Revenue
$monthlyRev = [];
$mRes = $con->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(final_amount) as total, COUNT(id) as count FROM orders WHERE order_status='delivered' GROUP BY month ORDER BY month DESC LIMIT 12");
while($r = $mRes->fetch_assoc()) $monthlyRev[] = $r;
?>

<div class="container mt-4">
    <h3>Revenue Report</h3>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue (Delivered)</h5>
                    <h2 class="fw-bold">₹<?= number_format($totalRev, 2) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-warning text-dark shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Pending Revenue</h5>
                    <h2 class="fw-bold">₹<?= number_format($pendingRev, 2) ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Monthly Breakdown (Last 12 Months)</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Orders (Delivered)</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($monthlyRev)): ?>
                        <?php foreach($monthlyRev as $m): ?>
                        <tr>
                            <td><?= date('F Y', strtotime($m['month'])) ?></td>
                            <td><?= $m['count'] ?></td>
                            <td>₹<?= number_format($m['total'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center">No data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
