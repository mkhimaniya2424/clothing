<?php
ob_start();
require_once 'admin_auth.php';
include_once("../db_connect.php");

// SAFE COUNT FUNCTION (returns 0 if table does not exist)
function safeCount($con, $table, $condition = '') {
    $sql = "SHOW TABLES LIKE '$table'";
    $check = $con->query($sql);

    if ($check->num_rows == 0) {
        return 0; 
    }

    $query = "SELECT COUNT(*) AS total FROM $table" . ($condition ? " WHERE $condition" : "");
    $count = $con->query($query)->fetch_assoc();
    return $count['total'] ?? 0;
}

// Fetch counts
$products = safeCount($con, "products");
$orders = safeCount($con, "orders");
$users = safeCount($con, "users");
$returns = safeCount($con, "return_requests");
$categories = safeCount($con, "categories");
$brands = safeCount($con, "brands");

// Order statistics
$pendingOrders = safeCount($con, "orders", "order_status='pending'");
$deliveredOrders = safeCount($con, "orders", "order_status='delivered'");

// Revenue calculation
$totalRevenue = 0;
$monthlyRevenue = 0;

$sql = "SHOW TABLES LIKE 'orders'";
$checkOrdersTable = $con->query($sql);

if($checkOrdersTable->num_rows > 0) {
    // Total revenue
    $revResult = $con->query("SELECT SUM(final_amount) as total FROM orders WHERE payment_status='paid'");
    $totalRevenue = $revResult->fetch_assoc()['total'] ?? 0;
    
    // This month's revenue
    $monthStart = date('Y-m-01');
    $monthRevResult = $con->query("SELECT SUM(final_amount) as total FROM orders WHERE payment_status='paid' AND created_at >= '$monthStart'");
    $monthlyRevenue = $monthRevResult->fetch_assoc()['total'] ?? 0;
}

// Fetch Monthly Revenue for chart
$monthlyRevenueData = [];
$monthlyOrdersData = [];
$months = [];

if($checkOrdersTable->num_rows > 0) {
    $revQuery = $con->query("
        SELECT DATE_FORMAT(created_at, '%b') AS month, 
               SUM(final_amount) AS revenue, 
               COUNT(*) AS order_count
        FROM orders
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY created_at ASC
    ");

    while($row = $revQuery->fetch_assoc()) {
        $monthlyRevenueData[] = $row['revenue'];
        $monthlyOrdersData[] = $row['order_count'];
        $months[] = $row['month'];
    }
} else {
    $monthlyRevenueData = [0,0,0,0,0,0];
    $monthlyOrdersData = [0,0,0,0,0,0];
    $months = ["Jan","Feb","Mar","Apr","May","Jun"];
}
?>

<style>
.stat-card {
    border-left: 4px solid;
    transition: all 0.3s ease;
    background: white;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}
.chart-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
}
</style>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1"><i class="fa fa-dashboard me-2"></i>Dashboard</h2>
        <p class="text-muted mb-0">Welcome back! Here's what's happening with your store.</p>
    </div>

    <!-- Statistics Cards Row 1 -->
    <div class="row g-3 mb-4">
        <!-- Total Products -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #667eea;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <i class="fa fa-box"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Total Products</p>
                            <h3 class="mb-0"><?= $products ?></h3>
                        </div>
                    </div>
                    <a href="admin_manage-products.php" class="btn btn-sm btn-outline-primary mt-3 w-100">
                        <i class="fa fa-arrow-right me-1"></i>Manage
                    </a>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #28a745;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                            <i class="fa fa-shopping-cart"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Total Orders</p>
                            <h3 class="mb-0"><?= $orders ?></h3>
                            <small class="text-success"><i class="fa fa-clock me-1"></i><?= $pendingOrders ?> Pending</small>
                        </div>
                    </div>
                    <a href="admin_manage-orders.php" class="btn btn-sm btn-outline-success mt-3 w-100">
                        <i class="fa fa-arrow-right me-1"></i>View Orders
                    </a>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #ffc107;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white;">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Registered Users</p>
                            <h3 class="mb-0"><?= $users ?></h3>
                        </div>
                    </div>
                    <a href="admin_manage-users.php" class="btn btn-sm btn-outline-warning mt-3 w-100">
                        <i class="fa fa-arrow-right me-1"></i>Manage Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Return Requests -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm" style="border-left-color: #dc3545;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
                            <i class="fa fa-undo"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Return Requests</p>
                            <h3 class="mb-0"><?= $returns ?></h3>
                        </div>
                    </div>
                    <a href="admin_manage-returns.php" class="btn btn-sm btn-outline-danger mt-3 w-100">
                        <i class="fa fa-arrow-right me-1"></i>View Returns
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row 2 -->
    <div class="row g-3 mb-4">
        <!-- Total Revenue -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75">Total Revenue</p>
                            <h2 class="mb-0">₹<?= number_format($totalRevenue, 2) ?></h2>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="fa fa-rupee-sign"></i>
                        </div>
                    </div>
                    <small class="opacity-75"><i class="fa fa-calendar me-1"></i>All time earnings</small>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75">This Month</p>
                            <h2 class="mb-0">₹<?= number_format($monthlyRevenue, 2) ?></h2>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="fa fa-chart-line"></i>
                        </div>
                    </div>
                    <small class="opacity-75"><i class="fa fa-calendar-alt me-1"></i><?= date('F Y') ?></small>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-xl-4 col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3"><i class="fa fa-info-circle me-2"></i>Quick Stats</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Categories</span>
                        <strong><?= $categories ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Brands</span>
                        <strong><?= $brands ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Delivered Orders</span>
                        <strong class="text-success"><?= $deliveredOrders ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-3">
        <!-- Revenue Chart -->
        <div class="col-xl-8">
            <div class="card chart-card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fa fa-chart-area me-2 text-primary"></i>Revenue Overview</h5>
                    <small class="text-muted">Last 6 months revenue trend</small>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Orders Chart -->
        <div class="col-xl-4">
            <div class="card chart-card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fa fa-chart-bar me-2 text-success"></i>Orders</h5>
                    <small class="text-muted">Monthly orders</small>
                </div>
                <div class="card-body">
                    <canvas id="ordersChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- CHART JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
var months = <?php echo json_encode($months); ?>;
var revenue = <?php echo json_encode($monthlyRevenueData); ?>;
var orders = <?php echo json_encode($monthlyOrdersData); ?>;

// Revenue Chart
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Revenue (₹)',
            data: revenue,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            fill: true,
            tension: 0.4,
            borderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Orders Chart
new Chart(document.getElementById('ordersChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Orders',
            data: orders,
            backgroundColor: 'rgba(40, 167, 69, 0.8)',
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
