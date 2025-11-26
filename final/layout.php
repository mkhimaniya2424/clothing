<?php
session_start();
// $_SESSION['user'] = ['name'=>'Meghana']; // demo
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clothing Brand</title>

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/jquery.validate.js"></script>
    <script src="js/additional-methods.js"> </script>
    <script src="js/validate.js"> </script>
    <link rel="stylesheet" href="fontawesome/css/all.min.css">
<!-- Bootstrap 
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

Font Awesome
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->

<!-- ✅ Custom CSS -->
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ✅ HEADER -->
<header>
    <a href="home.php" class="logo">
        <i class="fa-solid fa-shirt me-2"></i>Clothing Brand
    </a>

    <div class="header-actions">
        <!-- Wishlist & Cart always visible -->
        <a href="user/wishlist.php" title="Wishlist"><i class="fa-regular fa-heart"></i></a>
        <a href="user/cart.php" title="Cart"><i class="fa-solid fa-shopping-cart"></i></a>

        <!-- Profile Dropdown -->
        <div class="dropdown">
            <a href="#" class="text-white text-decoration-none dropdown-toggle" id="userMenu"
               data-bs-toggle="dropdown" aria-expanded="false">
               <i class="fa-regular fa-user"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                <?php if(!isset($_SESSION['user'])) { ?>
                    <li><a class="dropdown-item" href="user/register.php"><i class="fa fa-user-plus me-2"></i>Register</a></li>
                    <li><a class="dropdown-item" href="user/login.php"><i class="fa fa-sign-in-alt me-2"></i>Login</a></li>
                <?php } else { ?>
                    <li><a class="dropdown-item" href="user/profile.php"><i class="fa fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="user/orders.php"><i class="fa fa-box me-2"></i>My Orders</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="user/logout.php"><i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</header>

<!-- ✅ NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-center" id="mainMenu">
      <ul class="navbar-nav fw-semibold">
        <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop/shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="offers/offers.php">Offers</a></li>
        <li class="nav-item"><a class="nav-link" href="about/about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="contact/contact.php">Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="faq/faq.php">FAQ</a></li>
        <li class="nav-item"><a class="nav-link" href="blog/blog.php">Blog</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- ✅ CONTENT AREA -->
<div class="main-content container">



        <?php if (isset($title_page)): ?>
            <h2 class="mb-4"><?= $title_page ?></h2>
        <?php endif; ?>

        <?php if (isset($content)): ?>
            <div><?= $content ?></div>
        <?php endif; ?>


</div>

<!-- ✅ FOOTER -->
<footer>
  <div class="container">
    <div class="row text-start">
      <div class="col-md-4 mb-3">
        <h5>Clothing Brand</h5>
        <p>Trendy • Elegant • Affordable</p>
      </div>
      <div class="col-md-4 mb-3">
        <h6>Quick Links</h6>
        <a href="index.php" class="d-block">Home</a>
        <a href="shop/shop.php" class="d-block">Shop</a>
        <a href="offers/offers.php" class="d-block">Offers</a>
        <a href="about/about.php" class="d-block">About</a>
      </div>
      <div class="col-md-4 mb-3">
        <h6>Contact</h6>
        <p><i class="fa fa-phone me-2"></i>+91 98765 43210</p>
        <p><i class="fa fa-envelope me-2"></i>support@clothingbrand.com</p>
        <div>
          <a href="#" class="me-2"><i class="fa-brands fa-facebook"></i></a>
          <a href="#" class="me-2"><i class="fa-brands fa-instagram"></i></a>
          <a href="#"><i class="fa-brands fa-twitter"></i></a>
        </div>
      </div>
    </div>
    <hr class="border-light">
    <div class="text-center">&copy; <?php echo date("Y"); ?> Clothing Brand. All Rights Reserved.</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
