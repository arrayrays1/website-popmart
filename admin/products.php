<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

$products = $pdo->query("SELECT p.id, p.name, s.name AS series_name, p.description, p.price, p.image_path, p.stock, p.created_at, p.updated_at
                         FROM products p
                         JOIN series s ON s.id = p.series_id
                         ORDER BY p.created_at DESC")->fetchAll();
// top 5 best sellers based on completed orders
$best = $pdo->query("SELECT p.id, p.name, s.name AS series_name,
                            COALESCE(SUM(CASE WHEN o.status='Completed' THEN oi.quantity ELSE 0 END),0) AS units_sold,
                            COALESCE(COUNT(DISTINCT CASE WHEN o.status='Completed' THEN o.id END),0) AS orders_count
                     FROM products p
                     JOIN series s ON s.id = p.series_id
                     LEFT JOIN order_items oi ON oi.product_id = p.id
                     LEFT JOIN orders o ON o.id = oi.order_id
                     GROUP BY p.id
                     ORDER BY units_sold DESC
                     LIMIT 5")->fetchAll();
?>
<?php $activePage = 'products'; require_once __DIR__ . '/includes/header.php'; ?>

  <div style="display:flex; justify-content:space-between; align-items:center; margin:16px 0;">
    <h1>Products</h1>
    <div style="display:flex; gap:8px; align-items:center;">

      <a class="btn" href="/website-popmart/admin/product_form.php" style="background:#90EE90;">Add Product</a>
    </div>
  </div>

  <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:12px; margin-bottom:16px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
      <h3 style="margin:0;">Top 5 Best Selling Products</h3>
    </div>
    <div style="overflow:auto;">
      <table class="table" style="width:100%; border-collapse: collapse;">
        <thead>
          <tr>
            <th style="text-align:left; padding:8px; border-bottom:1px solid #e5e7eb;">#</th>
            <th style="text-align:left; padding:8px; border-bottom:1px solid #e5e7eb;">Name</th>
            <th style="text-align:left; padding:8px; border-bottom:1px solid #e5e7eb;">Series</th>
            <th style="text-align:center; padding:8px; border-bottom:1px solid #e5e7eb;">Units Sold</th>
            <th style="text-align:center; padding:8px; border-bottom:1px solid #e5e7eb;">Orders</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($best)): ?>
            <tr><td colspan="6" style="padding:8px; color:#6b7280;">No sales yet.</td></tr>
          <?php else: $rank=1; foreach ($best as $b): ?>
            <tr>
              <td style="padding:8px;">&nbsp;<?php echo $rank++; ?></td>
              <td style="padding:8px; max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">&nbsp;<?php echo htmlspecialchars($b['name']); ?></td>
              <td style="padding:8px;">&nbsp;<?php echo htmlspecialchars($b['series_name']); ?></td>
              <td style="text-align:center; padding:8px; font-weight:700;">&nbsp;<?php echo (int)$b['units_sold']; ?></td>
              <td style="text-align:center;padding:8px;">&nbsp;<?php echo (int)$b['orders_count']; ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert" style="margin-bottom:12px; color:#b91c1c;"><?php echo htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['deleted']) || isset($_GET['blocked'])): ?>
    <div class="alert" style="margin-bottom:12px; color:#065f46;">
      <?php if (isset($_GET['deleted'])): ?>Deleted <?php echo (int)$_GET['deleted']; ?> product(s). <?php endif; ?>
      <?php if (isset($_GET['blocked'])): ?><span style="color:#92400e;">Blocked <?php echo (int)$_GET['blocked']; ?> product(s) in use.</span><?php endif; ?>
    </div>
  <?php endif; ?>
  <!-- bulk delete button -->
<button id="bulkDeleteBtn" class="btn" type="button" style="background:#dc2626;" disabled>Delete Selected</button>
  <div style="overflow:auto;">
    <form id="bulkForm" method="post" action="/website-popmart/admin/product_bulk_delete.php">
    <table class="table" style="width:100%; border-collapse: collapse; background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
      <thead>
        <tr>
          <th style="width:40px;"><input type="checkbox" id="selectAll"></th>
          <th>ID</th>
          <th>Name</th>
          <th>Series</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Image</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><input type="checkbox" class="row-check" name="ids[]" value="<?php echo (int)$p['id']; ?>"></td>
            <td>#<?php echo (int)$p['id']; ?></td>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td><?php echo htmlspecialchars($p['series_name']); ?></td>
            <td>₱<?php echo number_format((float)$p['price'],2); ?></td>
            <td><?php echo (int)$p['stock']; ?></td>
            <td><?php if (!empty($p['image_path'])): ?><img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="" style="height:40px;" /><?php endif; ?></td>
            <td>
              <a class="btn" href="/website-popmart/admin/product_form.php?id=<?php echo (int)$p['id']; ?>">Edit</a>
              <a class="btn" href="/website-popmart/admin/product_delete.php?id=<?php echo (int)$p['id']; ?>" onclick="return confirm('Delete this product?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </form>
  </div>
  <script>
    (function(){
      const selectAll = document.getElementById('selectAll');
      const checks = () => Array.from(document.querySelectorAll('.row-check'));
      const bulkBtn = document.getElementById('bulkDeleteBtn');
      const form = document.getElementById('bulkForm');
      function updateState(){
        const anyChecked = checks().some(c => c.checked);
        bulkBtn.disabled = !anyChecked;
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
          if (confirm('Delete ' + count + ' selected product(s)? This cannot be undone.')) {
            form.submit();
          }
        });
      }
      updateState();
    })();
  </script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
