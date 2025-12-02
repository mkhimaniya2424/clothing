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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700&display=swap" rel="stylesheet">

<!-- Custom CSS -->
<style>
    :root {
        --brand-primary: #0f172a; /* Deep Navy */
        --brand-secondary: #e2e8f0; /* Light Gray */
        --brand-accent: #f59e0b; /* Gold/Amber */
        --brand-text: #334155; /* Slate 700 */
        --brand-bg: #f8fafc; /* Slate 50 */
        --brand-white: #ffffff;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--brand-bg);
        color: var(--brand-text);
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    h1, h2, h3, h4, h5, h6, .navbar-brand {
        font-family: 'Outfit', sans-serif;
        color: var(--brand-primary);
    }

    a { text-decoration: none; transition: all 0.3s ease; }

    /* Header & Navbar */
    .top-bar {
        background-color: var(--brand-primary);
        color: var(--brand-white);
        font-size: 0.875rem;
        padding: 0.5rem 0;
    }
    .top-bar a { color: var(--brand-secondary); }
    .top-bar a:hover { color: var(--brand-accent); }

    .navbar-custom {
        background-color: var(--brand-white);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 1rem 0;
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    
    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        letter-spacing: -0.5px;
    }

    .nav-link {
        color: var(--brand-primary);
        font-weight: 500;
        margin: 0 0.5rem;
        position: relative;
    }

    .nav-link:hover, .nav-link.active {
        color: var(--brand-accent);
    }

    .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 0;
        background-color: var(--brand-accent);
        transition: width 0.3s;
    }
    
    .nav-link:hover::after {
        width: 100%;
    }

    .btn-icon {
        color: var(--brand-primary);
        font-size: 1.25rem;
        margin-left: 1rem;
        position: relative;
    }
    
    .btn-icon:hover {
        color: var(--brand-accent);
        transform: translateY(-2px);
    }

    /* Cards & Content */
    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        background: var(--brand-white);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .btn-primary {
        background-color: var(--brand-primary);
        border-color: var(--brand-primary);
        padding: 0.6rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }
    
    .btn-primary:hover {
        background-color: #1e293b;
        border-color: #1e293b;
    }

    /* Footer */
    footer {
        background-color: var(--brand-primary);
        color: var(--brand-secondary);
        padding: 4rem 0 2rem;
        margin-top: auto;
    }
    
    footer h5 {
        color: var(--brand-white);
        margin-bottom: 1.5rem;
        font-weight: 600;
    }
    
    footer a {
        color: #94a3b8;
        display: block;
        margin-bottom: 0.75rem;
    }
    
    footer a:hover {
        color: var(--brand-accent);
        padding-left: 5px;
    }
    
    .social-links a {
        display: inline-block;
        margin-right: 1rem;
        font-size: 1.25rem;
    }

    /* Form Elements */
    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #cbd5e1;
        padding: 0.75rem 1rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--brand-primary);
        box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
    }
</style>

</head>
<body>


<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="home.php">
        <i class="fa-solid fa-shirt me-2 text-warning"></i>Clothing Brand
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="mainMenu">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="offers.php">Offers</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
      </ul>
      
      <div class="d-flex align-items-center">
        <a href="wishlist.php" class="btn-icon" title="Wishlist"><i class="fa-regular fa-heart"></i></a>
        <a href="cart.php" class="btn-icon" title="Cart"><i class="fa-solid fa-cart-shopping"></i></a>
        
        <!-- User Dropdown -->
        <div class="dropdown ms-3">
            <a href="#" class="btn-icon dropdown-toggle" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-regular fa-user"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" aria-labelledby="userMenu">
                <?php if(!isset($_SESSION['user'])): ?>
                    <li><a class="dropdown-item" href="register.php"><i class="fa fa-user-plus me-2"></i>Register</a></li>
                    <li><a class="dropdown-item" href="login.php"><i class="fa fa-sign-in-alt me-2"></i>Login</a></li>
                <?php else: ?>
                    <li class="dropdown-header fw-bold">Hello, <?= htmlspecialchars($_SESSION['user']['name']); ?></li>
                    <li><a class="dropdown-item" href="profile.php"><i class="fa fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="orders.php"><i class="fa fa-box me-2"></i>My Orders</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
      </div>
    </div>
  </div>
</nav>

<!-- ================= MAIN CONTENT ================= -->
<main>
    <div class="container my-5">
        <?php if(isset($title_page)): ?>
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold"><?= $title_page ?></h1>
                <div class="mx-auto" style="width: 60px; height: 3px; background-color: var(--brand-accent);"></div>
            </div>
        <?php endif; ?>
        
        <?php if(isset($content)) echo $content; ?>
    </div>
</main>

<!-- ================= FOOTER ================= -->
<footer>
  <div class="container">
      <div class="row gy-4">
          <div class="col-lg-4 col-md-6">
              <h5 class="d-flex align-items-center"><i class="fa-solid fa-shirt me-2 text-warning"></i>Clothing Brand</h5>
              <p class="text-secondary mb-4">Elevate your style with our premium collection. Trendy, elegant, and affordable fashion for everyone.</p>
              <div class="social-links">
                  <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                  <a href="#"><i class="fa-brands fa-instagram"></i></a>
                  <a href="#"><i class="fa-brands fa-twitter"></i></a>
                  <a href="#"><i class="fa-brands fa-pinterest"></i></a>
              </div>
          </div>
          
          <div class="col-lg-2 col-md-6">
              <h5>Quick Links</h5>
              <a href="home.php">Home</a>
              <a href="shop.php">Shop</a>
              <a href="offers.php">Offers</a>
              <a href="about.php">About Us</a>
              <a href="contact.php">Contact</a>
          </div>
          
          <div class="col-lg-2 col-md-6">
              <h5>Customer Care</h5>
              <a href="#">FAQ</a>
              <a href="#">Shipping Policy</a>
              <a href="#">Returns & Exchanges</a>
              <a href="#">Size Guide</a>
              <a href="#">Privacy Policy</a>
          </div>
          
          <div class="col-lg-4 col-md-6">
              <h5>Newsletter</h5>
              <p class="text-secondary">Subscribe to get special offers, free giveaways, and once-in-a-lifetime deals.</p>
              <form class="mt-3">
                  <div class="input-group">
                      <input type="email" class="form-control" placeholder="Enter your email">
                      <button class="btn btn-primary" type="button">Subscribe</button>
                  </div>
              </form>
          </div>
      </div>
      
      <hr class="border-secondary my-4 opacity-25">
      
      <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start text-secondary">
              &copy; <?= date("Y") ?> Clothing Brand. All Rights Reserved.
          </div>
          <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
              <img src="https://cdn-icons-png.flaticon.com/512/196/196578.png" alt="Visa" height="30" class="mx-1 opacity-75">
              <img src="https://cdn-icons-png.flaticon.com/512/196/196566.png" alt="Mastercard" height="30" class="mx-1 opacity-75">
              <img src="https://cdn-icons-png.flaticon.com/512/196/196565.png" alt="PayPal" height="30" class="mx-1 opacity-75">
          </div>
      </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
