<?php
ob_start();
include_once("db_connect.php");

// SAFE COUNT FUNCTION (returns 0 if table does not exist)
function safeCount($con, $table) {
    $sql = "SHOW TABLES LIKE '$table'";
    $check = $con->query($sql);

    if ($check->num_rows == 0) {
        return 0; 
    }

    $count = $con->query("SELECT COUNT(*) AS total FROM $table")->fetch_assoc();
    return $count['total'] ?? 0;
}

// Fetch counts safely
$products = safeCount($con, "products");
$orders   = safeCount($con, "orders");
$users    = safeCount($con, "users");
$visitors = 0; 

// Fetch Monthly Revenue (if orders table exists)
$monthlyRevenue = [];
$monthlyOrders = [];

$sql = "SHOW TABLES LIKE 'orders'";
$checkOrdersTable = $con->query($sql);

if($checkOrdersTable->num_rows > 0) {
    $revQuery = $con->query("
        SELECT DATE_FORMAT(created_at, '%b') AS month, SUM(total_amount) AS revenue, COUNT(*) AS order_count
        FROM orders
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY created_at ASC
        LIMIT 12
    ");

    while($row = $revQuery->fetch_assoc()) {
        $monthlyRevenue[] = $row['revenue'];
        $monthlyOrders[] = $row['order_count'];
        $months[] = $row['month'];
    }
} else {
    // Fallback if table missing
    $monthlyRevenue = [0,0,0,0,0,0];
    $monthlyOrders = [0,0,0,0,0,0];
    $months = ["Jan","Feb","Mar","Apr","May","Jun"];
}
?>

<div class="container-fluid py-4">

    <div class="row g-4">

        <!-- PRODUCTS -->
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-primary h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fa fa-box fa-3x"></i>
                    </div>
                    <div>
                        <h5>Total Products</h5>
                        <h3><?php echo $products; ?></h3>
                    </div>
                </div>
                <a href="manage-products.php" class="card-footer text-white">More info</a>
            </div>
        </div>

        <!-- ORDERS -->
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-success h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fa fa-shopping-cart fa-3x"></i>
                    </div>
                    <div>
                        <h5>Total Orders</h5>
                        <h3><?php echo $orders; ?></h3>
                    </div>
                </div>
                <a href="manage-orders.php" class="card-footer text-white">More info</a>
            </div>
        </div>

        <!-- USERS -->
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-warning h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fa fa-users fa-3x"></i>
                    </div>
                    <div>
                        <h5>Registered Users</h5>
                        <h3><?php echo $users; ?></h3>
                    </div>
                </div>
                <a href="manage-users.php" class="card-footer text-white">More info</a>
            </div>
        </div>

        <!-- VISITORS -->
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-danger h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fa fa-chart-line fa-3x"></i>
                    </div>
                    <div>
                        <h5>Unique Visitors</h5>
                        <h3><?php echo $visitors; ?></h3>
                    </div>
                </div>
                <a href="#" class="card-footer text-white">More info</a>
            </div>
        </div>

    </div>


    <!-- ========================= CHARTS ======================== -->
    <div class="row mt-5">
        
        <!-- Revenue Chart -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5>Monthly Revenue</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- Orders Chart -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5>Monthly Orders</h5>
                </div>
                <div class="card-body">
                    <canvas id="ordersChart" height="120"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- CHART JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
var months = <?php echo json_encode($months); ?>;
var revenue = <?php echo json_encode($monthlyRevenue); ?>;
var orders = <?php echo json_encode($monthlyOrders); ?>;

// Revenue Chart
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Revenue (₱)',
            data: revenue,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0,123,255,0.2)',
            fill: true,
            tension: 0.4
        }]
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
            backgroundColor: '#28a745'
        }]
    }
});
</script>

<?php
$content = ob_get_clean();
include_once("admin_layout.php");
?>
