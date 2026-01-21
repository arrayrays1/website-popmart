<?php 
  $activePage = 'home';
  include 'includes/header.php';
  include 'includes/modals.php';
?>

<!-- ================= HERO CAROUSEL ================= -->
<header>
  <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">

    <!-- indicators -->
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
    </div>

    <!-- carousel items -->
    <div class="carousel-inner">
      <!-- slide 1 -->
      <div class="carousel-item active" style="background:url('img/banner-1.png') center/cover no-repeat; height: 600px;">
        <div class="carousel-caption d-none d-md-block">
          <h1>Welcome to Popmart!</h1>
          <p>Discover collectibles</p>
        </div>
      </div>

      <!-- slide 2 -->
      <div class="carousel-item" style="background:url('img/banner-2.png') center/cover no-repeat; height: 600px;">
        <div class="carousel-caption d-none d-md-block">
          <h1>New Arrivals</h1>
          <p>Check out the latest collection</p>
        </div>
      </div>

      <!-- slide 3 -->
      <div class="carousel-item" style="background:url('img/banner-3.png') center/cover no-repeat; height: 600px;">
        <div class="carousel-caption d-none d-md-block">
          <h1>Exclusive Deals</h1>
          <p>Don’t miss our special offers</p>
        </div>
      </div>
    </div>

    <!-- carousel controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>
</header>

<!-- ================= NEW ARRIVALS ================= -->
<section class="py-5 new-arrivals">
  <div class="container">
    <h1 class="text-center mb-4 custom-h1">【NEW ARRIVALS】</h1>

    <div class="row g-4 justify-content-center">
      <!-- product 1 -->
      <div class="col-md-3">
        <div class="custom-card">
          <img src="img/new-arrival-1.png" alt="Product 1" class="custom-card-img">
          <div class="custom-card-details">
            <p class="text-title">SMISKI Birthday Series</p>
          </div>
          <button class="custom-card-button" onclick="window.location.href='products-tab/products-smiski.php'">View</button>
        </div>
      </div>

      <!-- product 2 -->
      <div class="col-md-3">
        <div class="custom-card">
          <img src="img/new-arrival-2.png" alt="Product 2" class="custom-card-img">
          <div class="custom-card-details">
            <p class="text-title">HIRONO The Pianist Figure</p>
          </div>
          <button class="custom-card-button" onclick="window.location.href='products-tab/products-hirono.php'">View</button>
        </div>
      </div>

      <!-- product 3 -->
      <div class="col-md-3">
        <div class="custom-card">
          <img src="img/new-arrival-3.png" alt="Product 3" class="custom-card-img">
          <div class="custom-card-details">
            <p class="text-title">POP BEAN Coffee Factory Series</p>
          </div>
          <button class="custom-card-button" onclick="window.location.href='products-tab/products-crybaby.php'">View</button>
        </div>
      </div>

      <!-- product 4 -->
      <div class="col-md-3">
        <div class="custom-card">
          <img src="img/new-arrival-4.png" alt="Product 4" class="custom-card-img">
          <div class="custom-card-details">
            <p class="text-title">SMISKI Touch Light Lamp</p>
          </div>
          <button class="custom-card-button" onclick="window.location.href='products-tab/products-smiski.php'">View</button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= BEST SELLERS ================= -->
<?php
// Get top 3 best selling products based on actual sales data (matching admin dashboard)
$bestSellers = [];
try {
  include_once __DIR__ . '/db/db_connect.php';
  $bestSellers = $pdo->query("SELECT p.id, p.name, p.price, p.stock, p.image_path, s.name AS series_name,
                              COALESCE(SUM(CASE WHEN o.status='Delivered' THEN oi.quantity ELSE 0 END),0) AS units_sold
                       FROM products p
                       JOIN series s ON s.id = p.series_id
                       LEFT JOIN order_items oi ON oi.product_id = p.id
                       LEFT JOIN orders o ON o.id = oi.order_id
                       GROUP BY p.id
                       ORDER BY units_sold DESC, p.created_at DESC
                       LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  // Fallback to some default products if database query fails
  $bestSellers = [
    ['id' => 1, 'name' => 'SMISKI Museum Series', 'price' => 300.00, 'stock' => 50, 'image_path' => 'img/products-img-banner/products-smiski/smiski-1.png', 'series_name' => 'smiski'],
    ['id' => 2, 'name' => 'HIRONO Little Mischief Series', 'price' => 500.00, 'stock' => 25, 'image_path' => 'img/products-img-banner/products-hirono/hirono-3.png', 'series_name' => 'hirono'],
    ['id' => 3, 'name' => 'MOFUSAND Pastries', 'price' => 300.00, 'stock' => 40, 'image_path' => 'img/products-img-banner/products-mofusand/mofusand-1.png', 'series_name' => 'mofusand']
  ];
}
?>
<section class="py-5">
  <div class="container">
    <h1 class="text-center mb-4 custom-h1">【BEST SELLERS】</h1>

    <div class="row g-4">
      <?php if (!empty($bestSellers)): ?>
        <?php foreach ($bestSellers as $index => $product): ?>
          <?php $isOutOfStock = (int)($product['stock'] ?? 0) <= 0; ?>
          <div class="col-md-4">
            <div class="card h-100 position-relative">
              <?php if ($index < 3): ?>
                <div class="position-absolute top-0 start-0 badge bg-warning text-dark m-2" style="z-index: 10;">
                  #<?php echo $index + 1; ?>
                </div>
              <?php endif; ?>
              <?php if ($isOutOfStock): ?>
                <div class="position-absolute top-0 end-0 badge bg-danger m-2" style="z-index: 10;">
                  Out of Stock
                </div>
              <?php endif; ?>
              <img src="<?php echo htmlspecialchars($product['image_path'] ?? 'img/best-seller-' . ($index + 1) . '.png'); ?>"
                   class="card-img-top <?php echo $isOutOfStock ? 'opacity-50' : ''; ?>"
                   alt="<?php echo htmlspecialchars($product['name']); ?>"
                   style="height: 250px; object-fit: cover;">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title-best-seller"><?php echo htmlspecialchars($product['name']); ?></h5>
                <p class="card-text">₱<?php echo number_format((float)$product['price'], 2); ?></p>
                <div class="mt-auto">
                  <?php if ($isOutOfStock): ?>
                    <button class="btn btn-secondary w-100" disabled>
                      Out of Stock
                    </button>
                  <?php else: ?>
                    <button class="btn btn-primary w-100 add-to-cart"
                            data-product-id="<?php echo (int)$product['id']; ?>"
                            data-price="<?php echo (float)$product['price']; ?>"
                            data-stock="<?php echo (int)$product['stock']; ?>"
                            data-name="<?php echo htmlspecialchars($product['name']); ?>">
                      Add to Cart
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Fallback static products if no sales data -->
        <div class="col-md-4">
          <div class="card h-100">
            <img src="img/best-seller-1.png" class="card-img-top" alt="Product 1">
            <div class="card-body">
              <h5 class="card-title-best-seller">SMISKI Museum Series</h5>
              <p class="card-text">₱300.00</p>
              <button class="btn btn-primary w-100" onclick="window.location.href='products-tab/products-smiski.php'">View Series</button>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="img/best-seller-2.png" class="card-img-top" alt="Product 2">
            <div class="card-body">
              <h5 class="card-title-best-seller">HIRONO Little Mischief</h5>
              <p class="card-text">₱500.00</p>
              <button class="btn btn-primary w-100" onclick="window.location.href='products-tab/products-hirono.php'">View Series</button>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="img/best-seller-3.png" class="card-img-top" alt="Product 3">
            <div class="card-body">
              <h5 class="card-title-best-seller">MOFUSAND Pastries</h5>
              <p class="card-text">₱300.00</p>
              <button class="btn btn-primary w-100" onclick="window.location.href='products-tab/products-mofusand.php'">View Series</button>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>