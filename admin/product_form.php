<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
// load series list (id, name)
$seriesOptions = [];
try {
  $hasSeries = $pdo->query("SHOW TABLES LIKE 'series'")->fetch();
  if ($hasSeries) {
    $seriesOptions = $pdo->query("SELECT id, name FROM series ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
  }
} catch (PDOException $e) { /* ignore if table missing */ }
if ($id > 0) {
  $stmt = $pdo->prepare("SELECT id, name, series_id, description, price, image_path, stock FROM products WHERE id = ?");
  $stmt->execute([$id]);
  $product = $stmt->fetch();
  if (!$product) { header('Location: /website-popmart/admin/products.php'); exit; }
}
?>
<?php $activePage = 'products'; require_once __DIR__ . '/includes/header.php'; ?>
  <style>
    .product-form-page { padding:20px; font-family: Arial, sans-serif; }
    .product-form-page .card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:16px; }
    .product-form-page .field { margin-bottom:12px; }
    .product-form-page label { display:block; font-weight:600; margin-bottom:6px; }
    .product-form-page input[type="text"],
    .product-form-page input[type="number"],
    .product-form-page textarea,
    .product-form-page select { width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px; }
    .product-form-page .btn { background:#111827; color:#fff; border:0; border-radius:8px; padding:8px 12px; cursor:pointer; text-decoration:none; display:inline-block; }
    .product-form-page .btn:hover { background:#0b0f1a; }
    .product-form-page .text-muted { color:#6b7280; font-size:12px; }
  </style>
  <div class="product-form-page">
    <h1><?php echo $id>0 ? 'Edit' : 'Add'; ?> Product</h1>

  <form class="card" method="post" action="/website-popmart/admin/product_save.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id>0 ? (int)$product['id'] : 0; ?>" />
    <div class="field">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" required value="<?php echo $id>0 ? htmlspecialchars($product['name']) : ''; ?>" />
    </div>
    <div class="field">
      <label for="series_id">Series</label>
      <select id="series_id" name="series_id" required>
        <option value="">-- Select Series --</option>
        <?php foreach ($seriesOptions as $opt): ?>
          <option value="<?php echo (int)$opt['id']; ?>" <?php echo ($id>0 && (int)$product['series_id'] === (int)$opt['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($opt['name']); ?>
          </option>
        <?php endforeach; ?>
        <option value="add_new">+ Add New Series</option>
      </select>
      <small class="text-muted">Series are managed in Admin → Series.</small>
    </div>
    <div class="field">
      <label for="price">Price</label>
      <input type="number" step="0.01" min="0" id="price" name="price" required value="<?php echo $id>0 ? htmlspecialchars($product['price']) : ''; ?>" />
    </div>
    <div class="field">
      <label for="stock">Initial Stock</label>
      <input type="number" step="1" min="0" id="stock" name="stock" required value="<?php echo $id>0 ? (int)$product['stock'] : 0; ?>" />
    </div>
    <div class="field">
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="5"><?php echo $id>0 ? htmlspecialchars($product['description']) : ''; ?></textarea>
    </div>
    <div class="field">
      <label for="image">Product Image <?php if ($id>0 && !empty($product['image_path'])): ?>(Choose File to Update)<?php endif; ?></label>
      <input type="file" id="image" name="image" accept="image/png, image/jpeg, image/webp" />
      <?php if ($id>0 && !empty($product['image_path'])): ?>
        <div style="margin-top:8px;">
          <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="" style="height:80px;" />
        </div>
      <?php endif; ?>
    </div>
    <div>
      <button class="btn" type="submit">Save</button>
      <a class="btn" href="/website-popmart/admin/products.php" style="background:#6b7280;">Cancel</a>
    </div>
  </form>
  <script>
    const sel = document.getElementById('series_id');
    if (sel) {
      sel.addEventListener('change', function() {
        if (this.value === 'add_new') {
          const ret = encodeURIComponent(window.location.pathname + window.location.search);
          window.location.href = '/website-popmart/admin/series.php?return=' + ret;
        }
      });
    }
  </script>
  </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
