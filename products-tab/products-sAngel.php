<?php 
  $activePage = 'products';
  include '../includes/header.php';
  include '../includes/modals.php';
  include_once __DIR__ . '/../db/db_connect.php';

  $category = 'sonnyangel';
  // added "stock" so the actual stocks on the databse will reflect on the pop up on PRODUCTS page
  $stmt = $pdo->prepare("SELECT id, name, description, image_path, price, stock FROM products WHERE category = ? ORDER BY id ASC");
  $stmt->execute([$category]);
  $rows = $stmt->fetchAll();
?>

  <!-- BREADCRUMB -->
<nav aria-label="breadcrumb" class="custom-breadcrumb mt-5 pt-4">
  <ol class="breadcrumb container">
    <li class="breadcrumb-item"><a href="../products.php">Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sonny Angel</li>
  </ol>
</nav>
  <!-- PRODUCT SECTION -->
  <section class="py-1 custom-product-section">
    <div class="container">
      <h1 id="product-title" class="text-center mb-4 custom-h1-product-title">SONNY ANGEL</h1>
      <div class="row g-4">
        <?php if ($rows && count($rows) > 0): ?>
          <?php foreach ($rows as $row): ?>
            <div class="col-md-3">
              <div class="card h-100">
                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="card-img-top" alt="Product Image">
                <div class="card-body">
                  <h5 class="card-title-best-seller"><?php echo htmlspecialchars($row['name']); ?></h5>
                  <p class="card-text">Php <?php echo number_format((float)$row['price'], 2); ?></p>
                  <a href="#" class="btn btn-primary w-100 add-to-cart" data-product-id="<?php echo (int)$row['id']; ?>" data-price="<?php echo (float)$row['price']; ?>" data-stock="<?php echo (int)$row['stock']; ?>" role="button">Add to Cart</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info">No products found in this category.</div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  
<?php 
  include '../includes/footer.php'; 
?>