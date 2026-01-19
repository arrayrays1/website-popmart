<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

$rows = $pdo->query("SELECT p.id, p.name, s.name AS series_name, p.stock, p.updated_at
                     FROM products p
                     JOIN series s ON s.id = p.series_id
                     ORDER BY p.name ASC")->fetchAll();
function invStatus($q){ if($q<=0) return 'Out of Stock'; if($q<=10) return 'Low Stock'; return 'In Stock'; }
// build restock lists
$outOf = [];
$low = [];
foreach ($rows as $r) {
  $stk = (int)$r['stock'];
  if ($stk <= 0) { $outOf[] = $r; }
  else if ($stk <= 10) { $low[] = $r; }
}
?>
<?php $activePage = 'inventory'; require_once __DIR__ . '/includes/header.php'; ?>
  <style>
    .inventory-page .table th, .inventory-page .table td { padding:10px; border-bottom:1px solid #e5e7eb; text-align:left; }
    .inventory-page .badge { padding:3px 8px; border-radius:9999px; font-size:12px; display:inline-block; }
    .inventory-page .ok { background:#e6ffe6; color:#065f46; }
    .inventory-page .low { background:#fff7ed; color:#92400e; }
    .inventory-page .out { background:#fee2e2; color:#991b1b; }
 .inventory-page input[type=number]{ width:70px; padding:6px; border:1px solid #e5e7eb; border-radius:8px; }
    .inventory-page .btn { background:#111827; color:#fff; border:0; border-radius:8px; padding:6px 10px; cursor:pointer; }
    .inventory-page .btn:hover { background:#0b0f1a; }
  </style>
  <div class="inventory-page" style="padding:20px 20px; font-family: Arial, sans-serif;">
    <h1>Inventory</h1>
    <style>
      .inventory-page .alerts-grid { display:grid; grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap:12px; margin:12px 0 20px; }
      @media (max-width: 992px) {
        .inventory-page { padding:16px !important; }
        .inventory-page .alerts-grid { grid-template-columns: 1fr; }
      }
    </style>
    <div class="alerts-grid">
      <div style="background:#fff; border:1px solid #ebe5e5ff; border-radius:10px; padding:12px;">
        <h3 style="margin:0 0 8px 0;">Out of Stock</h3>
        <div>
          <table class="table" style="width:100%; border-collapse: collapse; table-layout:fixed;">
            <thead>
            <tr>
                <th style="width:70px;">ID</th>
                <th style="width:45%;">Name</th>
                <th style="width:25%; text-align:center;">Series</th>
                <th style="width:90px; text-align:center;">Stock</th>
                <th style="width:140px;">Update</th>
            </tr>
            </thead>
            <tbody>
              <?php if (empty($outOf)): ?>
                <tr><td colspan="5" style="padding:8px; color:#6b7280;">All good — no out of stock items.</td></tr>
              <?php else: foreach ($outOf as $r): ?>
                <tr>
                  <td>#<?php echo (int)$r['id']; ?></td>
                <td style="max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">&nbsp;<?php echo htmlspecialchars($r['name']); ?></td>
                  <td>&nbsp;<?php echo htmlspecialchars($r['series_name']); ?></td>
                  <td style="text-align:center;"><span class="badge out">0</span></td>
                  <td>
                    <form method="post" action="/website-popmart/admin/inventory_update.php" style="display:flex; gap:8px; align-items:center;">
                      <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>" />
                      <input type="number" name="stock" min="0" step="1" value="0" />
                      <button class="btn" type="submit">Save</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:12px;">
        <h3 style="margin:0 0 8px 0;">Low Stock (≤ 10)</h3>
        <div>
        <table class="table" style="width:100%; border-collapse: collapse; table-layout:fixed;">
            <thead>
            <tr>
                <th style="width:70px;">ID</th>
                <th style="width:45%;">Name</th>
                <th style="width:25%; text-align:center;">Series</th>
                <th style="width:90px; text-align:center;">Stock</th>
                <th style="width:160px;">Update</th>
            </tr>
            </thead>
            <tbody>
              <?php if (empty($low)): ?>
                <tr><td colspan="5" style="padding:8px; color:#6b7280;">No low stock items.</td></tr>
              <?php else: foreach ($low as $r): ?>
                <tr>
                  <td>#<?php echo (int)$r['id']; ?></td>
                  <td style="max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">&nbsp;<?php echo htmlspecialchars($r['name']); ?></td>
                  <td>&nbsp;<?php echo htmlspecialchars($r['series_name']); ?></td>
                  <td style="text-align:center;"><span class="badge low"><?php echo (int)$r['stock']; ?></span></td>
                  <td>
                    <form method="post" action="/website-popmart/admin/inventory_update.php" style="display:flex; gap:8px; align-items:center;">
                      <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>" />
                      <input type="number" name="stock" min="0" step="1" value="<?php echo (int)$r['stock']; ?>" />
                      <button class="btn" type="submit">Save</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div style="overflow:auto;">
      <table class="table" style="width:100%; border-collapse: collapse; background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Series</th>
          <th>Current Stock</th>
          <th>Status</th>
          <th>Update</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($rows as $r): $s=invStatus((int)$r['stock']); ?>
        <tr>
          <td>#<?php echo (int)$r['id']; ?></td>
          <td><?php echo htmlspecialchars($r['name']); ?></td>
          <td><?php echo htmlspecialchars($r['series_name']); ?></td>
          <td><?php echo (int)$r['stock']; ?></td>
          <td>
            <?php if($s==='In Stock'): ?><span class="badge ok">In Stock</span><?php endif; ?>
            <?php if($s==='Low Stock'): ?><span class="badge low">Low Stock</span><?php endif; ?>
            <?php if($s==='Out of Stock'): ?><span class="badge out">Out of Stock</span><?php endif; ?>
          </td>
          <td>
            <form method="post" action="/website-popmart/admin/inventory_update.php" style="display:flex; gap:8px; align-items:center;">
              <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>" />
              <input type="number" name="stock" min="0" step="1" value="<?php echo (int)$r['stock']; ?>" />
              <button class="btn" type="submit">Save</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      </table>
    </div>
  </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
