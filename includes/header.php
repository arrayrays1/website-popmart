<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Popmart</title>
  <link rel="icon" type="image/x-icon" href="favicon.png">
  <!-- google fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  
  <!-- bootstrap icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- bootstrap css -->
  <link href="/website-popmart/dist/styles.css" rel="stylesheet">
</head>
<body>
<script>
  window.IS_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
<!-- NAVBAR -->
<nav id="mainNavbar" class="navbar navbar-expand-lg navbar-light custom-navbar fixed-top">
  <div class="container">
    <a class="navbar-brand" href="bootstrap.php">
      <img src="/website-popmart/img/pop-mart-logo.png" alt="E-Shop Logo" width="150" height="40" class="d-inline-block align-text-top">
    </a>

    <!-- hamburger -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto nav-links">
            <li class="nav-item"><a class="nav-link <?php if($activePage == 'home') echo 'active'; ?>" href="/website-popmart/index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link <?php if($activePage == 'products') echo 'active'; ?>" href="/website-popmart/products.php">Products</a></li>
            <li class="nav-item"><a class="nav-link <?php if($activePage == 'about') echo 'active'; ?>" href="/website-popmart/about.php">About</a></li>
            <li class="nav-item"><a class="nav-link <?php if($activePage == 'contact') echo 'active'; ?>" href="/website-popmart/contact.php">Contact</a></li>
            <li class="nav-item">
              <a class="nav-link <?php if($activePage == 'cart') echo 'active'; ?> position-relative" href="/website-popmart/cart.php">
                <i class="bi bi-cart"></i> Cart
                <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
              </a>
            </li>
            <li class="nav-item">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/website-popmart/orders.php"><i class="bi bi-bag"></i> My Orders</a></li>
                            <li><a class="dropdown-item" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="bi bi-person-circle"></i> Login</a>
                <?php endif; ?>
            </li>
            </ul>
    </div>
  </div>
</nav>