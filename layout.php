<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($title_page)?$title_page:"Clothing Brand" ?></title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<!-- Custom CSS -->
<style>
    :root {
        --brand-primary: #2e2a2fff;
        --brand-secondary: #333;
        --brand-light: #f8f9fa;
    }

    body {
        font-family: 'Roboto', sans-serif;
        background-color: var(--brand-light);
    }

    a { text-decoration: none; }

    header {
        background-color: var(--brand-secondary);
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    header .logo { font-size: 1.5rem; font-weight: 700; color: white; }
    header .header-actions a { color: white; margin-left: 1rem; font-size: 1.2rem; }

    .navbar-custom {
        background-color: white;
        border-bottom: 1px solid #ddd;
    }
    .navbar-custom .nav-link { color: #333; font-weight: 500; margin: 0 0.5rem; }
    .navbar-custom .nav-link:hover { color: var(--brand-primary); }

    .hero-section .carousel-caption h1 { font-size: 3rem; font-weight: 700; text-shadow: 2px 2px 5px rgba(0,0,0,0.5); }
    .hero-section .carousel-caption p { font-size: 1.2rem; }

    .card { border-radius: 0.75rem; transition: transform 0.3s; }
    .card:hover { transform: translateY(-5px); }

    section { padding: 4rem 0; }

    footer { background-color: var(--brand-secondary); color: white; padding: 3rem 0; }
    footer a { color: #ddd; }
    footer a:hover { color: var(--brand-primary); }
</style>

</head>
<body>

<!-- ================= HEADER ================= -->
<header>
    <a href="home.php" class="logo"><i class="fa-solid fa-shirt me-2"></i>Clothing Brand</a>
   <!-- Header Actions -->
<div class="header-actions d-flex align-items-center">

    <a href="wishlist.php" class="me-3 text-white" title="Wishlist"><i class="fa-regular fa-heart fs-5"></i></a>
    <a href="cart.php" class="me-3 text-white" title="Cart"><i class="fa-solid fa-shopping-cart fs-5"></i></a>

    <!-- User Dropdown -->
    <div class="dropdown">
        <button class="btn btn-sm btn-dark dropdown-toggle d-flex align-items-center" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-user me-1"></i>
            <?php if(isset($_SESSION['user'])): ?>
                <?= htmlspecialchars($_SESSION['user']['name']) ?>
            <?php else: ?>
                Account
            <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
            <?php if(!isset($_SESSION['user'])): ?>
                <li><a class="dropdown-item" href="register.php"><i class="fa fa-user-plus me-2"></i>Register</a></li>
                <li><a class="dropdown-item" href="login.php"><i class="fa fa-sign-in-alt me-2"></i>Login</a></li>
            <?php else: ?>
                <li class="dropdown-header fw-bold">Hello, <?= htmlspecialchars($_SESSION['user']['name']); ?> 👋</li>
                <li><a class="dropdown-item" href="profile.php"><i class="fa fa-user me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="orders.php"><i class="fa fa-box me-2"></i>My Orders</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

</header>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-center" id="mainMenu">
      <ul class="navbar-nav fw-semibold">
        <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="offers.php">Offers</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
        <!-- <li class="nav-item"><a class="nav-link" href="faq.php">FAQ</a></li> -->
        <!-- <li class="nav-item"><a class="nav-link" href="blog/blog.php">Blog</a></li> -->
      </ul>
    </div>
  </div>
</nav>

<!-- ================= MAIN CONTENT ================= -->
<main>
    <div class="container my-5">
        <?php if(isset($title_page)): ?>
            <h1 class="text-center mb-5"><?= $title_page ?></h1>
        <?php endif; ?>
        <?php if(isset($content)) echo $content; ?>
    </div>
</main>

<!-- ================= FOOTER ================= -->
<footer>
  <div class="container">
      <div class="row">
          <div class="col-md-4 mb-3">
              <h5>Clothing Brand</h5>
              <p>Trendy • Elegant • Affordable</p>
          </div>
          <div class="col-md-4 mb-3">
              <h6>Quick Links</h6>
              <a href="home.php" class="d-block">Home</a>
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
      <div class="text-center">&copy; <?= date("Y") ?> Clothing Brand. All Rights Reserved.</div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
