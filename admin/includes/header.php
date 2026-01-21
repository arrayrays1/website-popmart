<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../admin_middleware.php';
// set $activePage in each page: dashboard, products, series, inventory, orders, queries
$activePage = $activePage ?? '';
function activeClass($key, $active){ return $key === $active ? ' active' : ''; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin - Pop Mart</title>
  <link rel="stylesheet" href="/website-popmart/dist/styles.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { 
      padding-top: 80px; 
      background-color: #f8f9fa;
    }
    .custom-navbar {
      background-color: #fff;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    .table-container {
      background: #fff;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .btn {
      border-radius: 8px;
      padding: 0.5rem 1rem;
      font-weight: 500;
      transition: all 0.2s;
    }
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .page-title {
      font-weight: 700;
      color: #1a1a1a;
      margin-bottom: 0.5rem;
    }
    .page-subtitle {
      color: #6c757d;
      font-size: 0.95rem;
    }
  </style>
</head>
<body>
  <nav id="mainNavbar" class="navbar navbar-expand-lg navbar-light custom-navbar fixed-top">
    <div class="container">
      <a class="navbar-brand" href="/website-popmart/admin/dashboard.php">
        <img src="/website-popmart/img/pop-mart-logo.png" alt="Pop Mart" width="150" height="40" class="d-inline-block align-text-top">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="adminNavbar">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link<?php echo activeClass('dashboard',$activePage); ?>" href="/website-popmart/admin/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link<?php echo activeClass('products',$activePage); ?>" href="/website-popmart/admin/products.php">Products</a></li>
          <li class="nav-item"><a class="nav-link<?php echo activeClass('series',$activePage); ?>" href="/website-popmart/admin/series.php">Series</a></li>
          <li class="nav-item"><a class="nav-link<?php echo activeClass('inventory',$activePage); ?>" href="/website-popmart/admin/inventory.php">Inventory</a></li>
          <li class="nav-item"><a class="nav-link<?php echo activeClass('orders',$activePage); ?>" href="/website-popmart/admin/orders.php">Orders</a></li>
          <li class="nav-item"><a class="nav-link<?php echo activeClass('queries',$activePage); ?>" href="/website-popmart/admin/queries.php">Customer Queries</a></li>
          <li class="nav-item"><a class="nav-link text-danger" href="/website-popmart/db/logout_process.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <main class="container mt-4">