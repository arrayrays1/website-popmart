<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

$rows = $pdo->query("SELECT p.id, p.name, s.name AS series_name, p.stock, p.updated_at
                     FROM products p
                     JOIN series s ON s.id = p.series_id
                     ORDER BY p.name ASC")->fetchAll();

function invStatus($q){ 
  if($q<=0) return 'Out of Stock'; 
  if($q<=10) return 'Low Stock'; 
  return 'In Stock'; 
}

$outOf = [];
$low = [];
foreach ($rows as $r) {
  $stk = (int)$r['stock'];
  if ($stk <= 0) { $outOf[] = $r; }
  else if ($stk <= 10) { $low[] = $r; }
}
?>
<?php $activePage = 'inventory'; require_once __DIR__ . '/includes/header.php'; ?>

  <div class="row mb-4">
    <div class="col-12">
      <h1 class="page-title">Inventory Management</h1>
      <p class="page-subtitle">Monitor and manage product stock levels</p>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card border-danger">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0 text-danger">Out of Stock (<?php echo count($outOf); ?>)</h5>
        </div>
        <div class="card-body">
          <?php if (empty($outOf)): ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
              <p class="mt-2 mb-0">All products are in stock</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:60px;">ID</th>
                    <th>Product Name</th>
                    <th style="width:120px;">Series</th>
                    <th style="width:180px;">Restock</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($outOf as $r): ?>
                    <tr>
                      <td><small style="color: #f80000;">#<?php echo (int)$r['id']; ?></small></td>
                      <td><?php echo htmlspecialchars($r['name']); ?></td>
                      <td><small><?php echo htmlspecialchars($r['series_name']); ?></small></td>
                      <td>
                        <form method="post" action="/website-popmart/admin/inventory_update.php" class="d-flex gap-2">
                          <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>" />
                          <input type="number" name="stock" min="1" step="1" value="50" class="form-control form-control-sm" style="width:80px;" />
                          <button class="btn btn-sm btn-success" type="submit"><i class="bi bi-plus-circle"></i></button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card border-warning">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0 text-warning">Low Stock ≤10 (<?php echo count($low); ?>)</h5>
        </div>
        <div class="card-body">
          <?php if (empty($low)): ?>
            <div class="text-center text-muted py-4">
              <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
              <p class="mt-2 mb-0">No low stock items</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:60px;">ID</th>
                    <th>Product Name</th>
                    <th style="width:80px;" class="text-center">Stock</th>
                    <th style="width:180px;">Update</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($low as $r): ?>
                    <tr>
                      <td><small class="text-muted">#<?php echo (int)$r['id']; ?></small></td>
                      <td><?php echo htmlspecialchars($r['name']); ?></td>
                      <td class="text-center"><span class="badge bg-warning text-dark"><?php echo (int)$r['stock']; ?></span></td>
                      <td>
                        <form method="post" action="/website-popmart/admin/inventory_update.php" class="d-flex gap-2">
                          <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>" />
                          <input type="number" name="stock" min="0" step="1" value="<?php echo (int)$r['stock']; ?>" class="form-control form-control-sm" style="width:80px;" />
                          <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-arrow-clockwise"></i></button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-white border-0">
      <h5 class="mb-0">All Products Inventory</h5>
    </div>
    <div class="card-body">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:80px;">ID</th>
            <th>Product Name</th>
            <th>Series</th>
            <th class="text-center">Current Stock</th>
            <th class="text-center">Status</th>
            <th style="width:200px;">Update Stock</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($rows as $r): $s=invStatus((int)$r['stock']); ?>
          <tr>
            <td><span class="badge" style="background-color: #f80000;">#<?php echo (int)$r['id']; ?></span></td>
            <td><?php echo htmlspecialchars($r['name']); ?></td>
            <td><small class="text-muted"><?php echo htmlspecialchars($r['series_name']); ?></small></td>
            <td class="text-center fw-bold"><?php echo (int)$r['stock']; ?></td>
            <td class="text-center">
              <?php if($s==='In Stock'): ?><span class="badge" style="background-color: #f80000;">In Stock</span><?php endif; ?>
              <?php if($s==='Low Stock'): ?><span class="badge bg-warning text-dark">Low Stock</span><?php endif; ?>
              <?php if($s==='Out of Stock'): ?><span class="badge bg-danger">Out of Stock</span><?php endif; ?>
            </td>
            <td>
              <form method="post" action="/website-popmart/admin/inventory_update.php" class="d-flex gap-2">
                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>" />
                <input type="number" name="stock" min="0" step="1" value="<?php echo (int)$r['stock']; ?>" class="form-control form-control-sm" style="width:90px;" />
                <button class="btn btn-sm btn-outline-primary" type="submit">
                  <i class="bi bi-save"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
