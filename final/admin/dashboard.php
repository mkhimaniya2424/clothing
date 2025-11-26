<?php
ob_start();
$title_page = "dashboard";
?>

<div class="container-fluid py-4">

    <div class="row g-4">

        <!-- Total Products Info Box -->
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-primary h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fa fa-box fa-3x"></i>
                    </div>
                    <div>
                        <h5 class="card-title">Total Products</h5>
                        <h3>120</h3>
                    </div>
                </div>
                <a href="manage-products.php" class="card-footer text-white text-decoration-none">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Total Orders Info Box -->
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-success h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fa fa-shopping-cart fa-3x"></i>
                    </div>
                    <div>
                        <h5 class="card-title">Total Orders</h5>
                        <h3>85</h3>
                    </div>
                </div>
                <a href="manage-orders.php" class="card-footer text-white text-decoration-none">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Registered Users Info Box -->
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-warning h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fa fa-users fa-3x"></i>
                    </div>
                    <div>
                        <h5 class="card-title">Registered Users</h5>
                        <h3>300</h3>
                    </div>
                </div>
                <a href="manage-users.php" class="card-footer text-white text-decoration-none">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Unique Visitors Info Box -->
        <div class="col-md-3 col-sm-6">
            <div class="card text-white bg-danger h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <i class="fa fa-chart-line fa-3x"></i>
                    </div>
                    <div>
                        <h5 class="card-title">Unique Visitors</h5>
                        <h3>65</h3>
                    </div>
                </div>
                <a href="#" class="card-footer text-white text-decoration-none">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
include_once("layout1.php");
?>
