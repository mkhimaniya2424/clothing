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
    // Total revenue - includes paid online orders and all COD orders (since COD is paid on delivery)
    $revResult = $con->query("SELECT SUM(final_amount) as total FROM orders WHERE payment_status='paid' OR payment_method='cod'");
    $totalRevenue = $revResult->fetch_assoc()['total'] ?? 0;
    
    // This month's revenue
    $monthStart = date('Y-m-01');
    $monthRevResult = $con->query("SELECT SUM(final_amount) as total FROM orders WHERE (payment_status='paid' OR payment_method='cod') AND created_at >= '$monthStart'");
    $monthlyRevenue = $monthRevResult->fetch_assoc()['total'] ?? 0;
}

// Fetch Order Status Distribution for Pie Chart
$orderStatusLabels = [];
$orderStatusData = [];
$orderStatusColors = [
    'pending' => '#ffc107',
    'confirmed' => '#17a2b8',
    'processing' => '#6f42c1',
    'packed' => '#fd7e14',
    'shipped' => '#007bff',
    'delivered' => '#28a745',
    'cancelled' => '#dc3545'
];

if($checkOrdersTable->num_rows > 0) {
    $statusQuery = $con->query("
        SELECT order_status, COUNT(*) as count 
        FROM orders 
        GROUP BY order_status
        ORDER BY count DESC
    ");
    
    while($row = $statusQuery->fetch_assoc()) {
        $orderStatusLabels[] = ucfirst($row['order_status']);
        $orderStatusData[] = $row['count'];
    }
} else {
    $orderStatusLabels = ['No Orders'];
    $orderStatusData = [0];
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

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Order Status Chart -->
        <div class="col-xl-6">
            <div class="card chart-card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fa fa-chart-pie me-2 text-primary"></i>Order Status Distribution</h5>
                    <small class="text-muted">Current orders by status</small>
                </div>
                <div class="card-body">
                    <canvas id="orderStatusChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fa fa-shopping-bag me-2 text-success"></i>Recent Orders</h5>
                    <small class="text-muted">Latest 5 orders</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recentOrders = $con->query("
                                    SELECT o.id, o.final_amount, o.order_status, o.created_at, u.username 
                                    FROM orders o 
                                    LEFT JOIN users u ON o.user_id = u.id 
                                    ORDER BY o.created_at DESC 
                                    LIMIT 5
                                ");
                                
                                if ($recentOrders && $recentOrders->num_rows > 0) {
                                    while ($order = $recentOrders->fetch_assoc()) {
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'confirmed' => 'info',
                                            'processing' => 'primary',
                                            'packed' => 'secondary',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $badgeColor = $statusColors[$order['order_status']] ?? 'secondary';
                                        ?>
                                        <tr>
                                            <td><strong>#<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                                            <td><?= htmlspecialchars($order['username'] ?? 'Guest') ?></td>
                                            <td>₹<?= number_format($order['final_amount'], 2) ?></td>
                                            <td><span class="badge bg-<?= $badgeColor ?>"><?= ucfirst($order['order_status']) ?></span></td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="5" class="text-center text-muted py-4">No orders yet</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Dashboard Sections -->
    <div class="row g-3 mb-4">
        <!-- Top Selling Products -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fa fa-star me-2 text-warning"></i>Top Selling Products</h5>
                    <small class="text-muted">Best performers</small>
                </div>
                <div class="card-body">
                    <?php
                    $topProducts = $con->query("
                        SELECT p.title, p.price, SUM(oi.quantity) as total_sold
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        GROUP BY oi.product_id
                        ORDER BY total_sold DESC
                        LIMIT 5
                    ");
                    
                    if ($topProducts && $topProducts->num_rows > 0) {
                        while ($product = $topProducts->fetch_assoc()) {
                            ?>
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="me-3">
                                    <div style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-tshirt text-muted"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars(substr($product['title'], 0, 30)) ?><?= strlen($product['title']) > 30 ? '...' : '' ?></h6>
                                    <small class="text-muted">₹<?= number_format($product['price'], 2) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success"><?= $product['total_sold'] ?> sold</span>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="text-center text-muted py-4">No sales data available</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fa fa-exclamation-triangle me-2 text-danger"></i>Low Stock Alert</h5>
                    <small class="text-muted">Products running low</small>
                </div>
                <div class="card-body">
                    <?php
                    $lowStock = $con->query("
                        SELECT id, title, stock, price
                        FROM products
                        WHERE stock < 10 AND stock > 0
                        ORDER BY stock ASC
                        LIMIT 5
                    ");
                    
                    if ($lowStock && $lowStock->num_rows > 0) {
                        while ($product = $lowStock->fetch_assoc()) {
                            $stockLevel = $product['stock'] <= 3 ? 'danger' : 'warning';
                            ?>
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="me-3">
                                    <div style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-box text-muted"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars(substr($product['title'], 0, 30)) ?><?= strlen($product['title']) > 30 ? '...' : '' ?></h6>
                                    <small class="text-muted">₹<?= number_format($product['price'], 2) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?= $stockLevel ?>">
                                        <i class="fa fa-box me-1"></i><?= $product['stock'] ?> left
                                    </span>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="text-center text-muted py-4"><i class="fa fa-check-circle text-success me-2"></i>All products well stocked!</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- CHART JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
var statusLabels = <?php echo json_encode($orderStatusLabels); ?>;
var statusData = <?php echo json_encode($orderStatusData); ?>;

// Generate colors based on status
var backgroundColors = statusLabels.map(function(label) {
    var colorMap = {
        'Pending': '#ffc107',
        'Confirmed': '#17a2b8',
        'Processing': '#6f42c1',
        'Packed': '#fd7e14',
        'Shipped': '#007bff',
        'Delivered': '#28a745',
        'Cancelled': '#dc3545'
    };
    return colorMap[label] || '#6c757d';
});

// Order Status Pie Chart
new Chart(document.getElementById('orderStatusChart'), {
    type: 'pie',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusData,
            backgroundColor: backgroundColors,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    padding: 15,
                    font: {
                        size: 12
                    },
                    generateLabels: function(chart) {
                        const data = chart.data;
                        if (data.labels.length && data.datasets.length) {
                            return data.labels.map((label, i) => {
                                const value = data.datasets[0].data[i];
                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return {
                                    text: `${label}: ${value} (${percentage}%)`,
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    hidden: false,
                                    index: i
                                };
                            });
                        }
                        return [];
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.parsed || 0;
                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                        let percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} orders (${percentage}%)`;
                    }
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
