<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

$products = $pdo->query("SELECT p.id, p.name, s.name AS series_name, p.description, p.price, p.image_path, p.stock, p.created_at, p.updated_at
                         FROM products p
                         JOIN series s ON s.id = p.series_id
                         ORDER BY p.created_at DESC")->fetchAll();
// top 5 best sellers based on completed orders
$best = $pdo->query("SELECT p.id, p.name, s.name AS series_name,
                            COALESCE(SUM(CASE WHEN o.status='Delivered' THEN oi.quantity ELSE 0 END),0) AS units_sold,
                            COALESCE(COUNT(DISTINCT CASE WHEN o.status='Delivered' THEN o.id END),0) AS orders_count
                     FROM products p
                     JOIN series s ON s.id = p.series_id
                     LEFT JOIN order_items oi ON oi.product_id = p.id
                     LEFT JOIN orders o ON o.id = oi.order_id
                     GROUP BY p.id
                     ORDER BY units_sold DESC
                     LIMIT 5")->fetchAll();
?>
<?php $activePage = 'products'; require_once __DIR__ . '/includes/header.php'; ?>

  <div class="row mb-4">
    <div class="col-md-8">
      <h1 class="page-title">Products Management</h1>
      <p class="page-subtitle">Manage your product catalog and inventory</p>
    </div>
    <div class="col-md-4 text-end d-flex align-items-center justify-content-end">
      <a class="btn btn-success" href="/website-popmart/admin/product_form.php">
        <i class="bi bi-plus-circle me-1"></i>Add New Product
      </a>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header bg-white border-0">
      <h5 class="mb-0">Top 5 Best Selling Products</h5>
    </div>
    <div class="card-body">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Rank</th>
            <th>Product Name</th>
            <th>Series</th>
            <th class="text-center">Units Sold</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($best)): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No sales data yet.</td></tr>
          <?php else: $rank=1; foreach ($best as $b): 
            $badgeColor = '';
            if($rank==1) $badgeColor = '#ffc107'; // Gold/Warning
            elseif($rank==2) $badgeColor = '#6c757d'; // Silver/Secondary
            elseif($rank==3) $badgeColor = '#cd7f32'; // Bronze
            else $badgeColor = '#f80000'; // Red for rank 4+
          ?>
            <tr>
              <td>
                <?php if($rank==1): ?><i class="bi bi-trophy-fill text-warning"></i><?php endif; ?>
                <?php if($rank==2): ?><i class="bi bi-trophy text-secondary"></i><?php endif; ?>
                <?php if($rank==3): ?><i class="bi bi-trophy text-danger"></i><?php endif; ?>
                <?php if($rank>3): echo $rank; endif; $rank++; ?>
              </td>
              <td class="fw-bold"><?php echo htmlspecialchars($b['name']); ?></td>
              <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($b['series_name']); ?></span></td>
              <td class="text-center"><span class="badge" style="background-color: <?php echo $badgeColor; ?>;"><?php echo (int)$b['units_sold']; ?></span></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_GET['deleted']) || isset($_GET['blocked'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?php if (isset($_GET['deleted'])): ?>
        <i class="bi bi-check-circle me-2"></i>Deleted <?php echo (int)$_GET['deleted']; ?> product(s).
      <?php endif; ?>
      <?php if (isset($_GET['blocked'])): ?>
        <span class="text-warning ms-2"><i class="bi bi-exclamation-circle"></i> Blocked <?php echo (int)$_GET['blocked']; ?> product(s) in use.</span>
      <?php endif; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <form id="bulkForm" method="post" action="/website-popmart/admin/product_bulk_delete.php">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <button id="bulkDeleteBtn" class="btn btn-danger" type="button" disabled>
        <i class="bi bi-trash me-1"></i>Delete Selected
      </button>
      <span id="selectedCount" class="text-muted"></span>
    </div>
    
    <div class="table-container">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:40px;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
            <th>ID</th>
            <th>Product</th>
            <th>Series</th>
            <th>Price</th>
            <th>Stock</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td><input type="checkbox" class="row-check form-check-input" name="ids[]" value="<?php echo (int)$p['id']; ?>"></td>
              <td><span class="badge bg-secondary">#<?php echo (int)$p['id']; ?></span></td>
              <td>
                <div class="d-flex align-items-center">
                  <?php if (!empty($p['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="" class="me-2 rounded" style="width:50px; height:50px; object-fit:cover;" />
                  <?php endif; ?>
                  <div>
                    <div class="fw-bold"><?php echo htmlspecialchars($p['name']); ?></div>
                  </div>
                </div>
              </td>
              <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($p['series_name']); ?></span></td>
              <td class="fw-bold text-success">₱<?php echo number_format((float)$p['price'],2); ?></td>
              <td>
                <span class="badge" style="background-color: <?php echo $p['stock'] > 0 ? '#f80000' : '#dc3545'; ?>;">
                  <?php echo (int)$p['stock']; ?>
                </span>
              </td>
              <td class="text-center">
                <div class="btn-group btn-group-sm">
                  <a class="btn btn-outline-primary" href="/website-popmart/admin/product_form.php?id=<?php echo (int)$p['id']; ?>">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a class="btn btn-outline-danger" href="/website-popmart/admin/product_delete.php?id=<?php echo (int)$p['id']; ?>" onclick="return confirm('Delete this product?');">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </form>

  <script>
    (function(){
      const selectAll = document.getElementById('selectAll');
      const checks = () => Array.from(document.querySelectorAll('.row-check'));
      const bulkBtn = document.getElementById('bulkDeleteBtn');
      const selectedCount = document.getElementById('selectedCount');
      const form = document.getElementById('bulkForm');
      
      function updateState(){
        const checkedItems = checks().filter(c => c.checked);
        const count = checkedItems.length;
        bulkBtn.disabled = count === 0;
        selectedCount.textContent = count > 0 ? `${count} selected` : '';
      }
      
      if (selectAll) {
        selectAll.addEventListener('change', function(){
          checks().forEach(c => c.checked = selectAll.checked);
          updateState();
        });
      }
      
      checks().forEach(c => c.addEventListener('change', updateState));
      
      if (bulkBtn) {
        bulkBtn.addEventListener('click', function(){
          if (bulkBtn.disabled) return;
          const count = checks().filter(c => c.checked).length;
          if (count === 0) return;
          if (confirm(`Delete ${count} selected product(s)? This cannot be undone.`)) {
            form.submit();
          }
        });
      }
      
      updateState();
    })();
  </script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
