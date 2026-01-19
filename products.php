<?php 
  $activePage = 'products';
  include 'includes/header.php';
  include 'includes/modals.php';
  include_once __DIR__ . '/db/db_connect.php';

  $series = [];
  try {
    $rows = $pdo->query("SELECT id, name, image_path FROM series ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
      $img = $r['image_path'];
      if (!$img) {
        $stmt = $pdo->prepare("SELECT image_path FROM products WHERE series_id = ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$r['id']]);
        $img = $stmt->fetchColumn();
      }
      $series[] = [
        'id'    => (int)$r['id'],
        'name'  => $r['name'],
        'image' => $img ?: 'img/products-img-banner/' . strtolower(str_replace(' ','-', $r['name'])) . '.png'
      ];
    }
  } catch (PDOException $e) { /* ignore */ }
?>
<section class="py-5 custom-products">
  <div class="container">
    <h1 class="text-center mb-4 custom-h1-products">PRODUCTS</h1>
    <div class="row justify-content-center g-4">
      <?php if (!empty($series)): ?>
        <?php foreach ($series as $s): 
          $label = strtoupper($s['name']);
          $url   = 'products-tab/products-series.php?series_id=' . $s['id'];
        ?>
          <div class="col-md-4">
            <div class="custom-card">
              <img src="<?php echo htmlspecialchars($s['image']); ?>" alt="<?php echo htmlspecialchars($label); ?>" class="custom-card-img">
              <div class="custom-card-details">
                <p class="text-title"><?php echo htmlspecialchars($label); ?></p>
              </div>
              <button class="custom-card-button" onclick="window.location.href='<?php echo $url; ?>'">View</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-info">No series found yet. Please add one in Admin &gt; Series.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>