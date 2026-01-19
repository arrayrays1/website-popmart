<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

function getScalar($pdo, $sql, $params = []) {
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  return $stmt->fetchColumn();
}

// metrics
$totalSales = (float)(getScalar($pdo, "SELECT COALESCE(SUM(total),0) FROM orders WHERE status = 'Completed'"));
$totalUsers = (int)(getScalar($pdo, "SELECT COUNT(*) FROM users"));
$pendingCount = (int)(getScalar($pdo, "SELECT COUNT(*) FROM orders WHERE status = 'Pending'"));
$completedCount = (int)(getScalar($pdo, "SELECT COUNT(*) FROM orders WHERE status = 'Completed'"));

// sales last 7 days data (for sparkline; needs refinement)
$salesRows = $pdo->query("SELECT DATE(created_at) AS d, COALESCE(SUM(total),0) AS s
                          FROM orders
                          WHERE status='Completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                          GROUP BY DATE(created_at)
                          ORDER BY d ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
$sales7 = [];
for ($i = 6; $i >= 0; $i--) {
  $day = (new DateTime())->modify("-{$i} day")->format('Y-m-d');
  $sales7[] = (float)($salesRows[$day] ?? 0);
}
$maxBar = max($sales7) ?: 1;

// pending orders (last 10)
$sql = "
  SELECT o.id, o.user_id, o.total, o.status, o.created_at,
         CONCAT(u.first_name, ' ', u.last_name) AS customer,
         GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity) SEPARATOR ', ') AS items
  FROM orders o
  JOIN users u ON u.id = o.user_id
  JOIN order_items oi ON oi.order_id = o.id
  JOIN products p ON p.id = oi.product_id
  WHERE o.status = 'Pending'
  GROUP BY o.id
  ORDER BY o.created_at DESC
  LIMIT 10
";
$pendingOrders = $pdo->query($sql)->fetchAll();
// recent sign-ups (last 5)
$recentUsers = [];
try {
  $recentUsers = $pdo->query("SELECT first_name, last_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* ignore */ }
// recent customer queries (last 5)
$recentQueries = [];
try {
  $recentQueries = $pdo->query("SELECT name, email, subject, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { /* ignore */ }

function nextStatus($current) {
  $map = [
    'Pending' => 'To Ship',
    'To Ship' => 'To Deliver',
    'To Deliver' => 'Completed'
  ];
  return $map[$current] ?? null;
}
?>
<?php $activePage = 'dashboard'; require_once __DIR__ . '/includes/header.php'; ?>
<style>
    .metric-cards { display:flex; gap:16px; flex-wrap: wrap; margin: 16px 0; }
    .metric-card { border:1px solid #e5e7eb; padding:12px; border-radius:10px; min-width:220px; background:#fff; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
    .metric-title { color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing: .04em; }
    .metric-value { font-size:22px; font-weight:800; color:#111827; }
</style>
    <h1>Admin Dashboard</h1>
  <div class="metric-cards">
    <div class="metric-card">
      <div class="metric-title">Total Sales</div>
      <div class="metric-value">₱<?php echo number_format($totalSales, 2); ?></div>
    </div>
    <div class="metric-card">
      <div class="metric-title">Total Users</div>
      <div class="metric-value"><?php echo $totalUsers; ?></div>
    </div>
    <div class="metric-card">
      <div class="metric-title">Pending Orders</div>
      <div class="metric-value"><?php echo $pendingCount; ?></div>
    </div>
    <div class="metric-card">
      <div class="metric-title">Completed Orders</div>
      <div class="metric-value"><?php echo $completedCount; ?></div>
    </div>
  </div>

  <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
    <h2 style="margin: 16px 0;">Pending Orders (last 10)</h2>
    <a href="/website-popmart/admin/orders.php" class="btn" style="background:#111827; color:#fff; border-radius:8px; padding:8px 12px; text-decoration:none;">View Orders</a>
  </div>
  <div style="overflow:auto;">
    <table class="table" style="width:100%; border-collapse: collapse; background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
      <thead>
        <tr>
          <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Order ID</th>
          <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Customer</th>
          <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Products</th>
          <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Total</th>
          <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Status</th>
          <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pendingOrders as $row): ?>
          <tr>
            <td style="padding:8px;">#<?php echo htmlspecialchars($row['id']); ?></td>
            <td style="padding:8px;">&nbsp;<?php echo htmlspecialchars($row['customer']); ?></td>
            <td style="padding:8px; max-width:520px;">&nbsp;<?php echo htmlspecialchars($row['items']); ?></td>
            <td style="padding:8px;">₱<?php echo number_format((float)$row['total'], 2); ?></td>
            <td style="padding:8px; "><span><?php echo htmlspecialchars($row['status']); ?></span></td>
            <td style="padding:8px;">
              <?php $next = nextStatus($row['status']); ?>
              <?php if ($next): ?>
                <button data-id="<?php echo (int)$row['id']; ?>" data-next="<?php echo htmlspecialchars($next); ?>" class="btn-update" style="padding:6px 10px;">Mark as <?php echo htmlspecialchars($next); ?></button>
              <?php else: ?>
                <span>-</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:20px;">
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:12px;">
      <div style="display:flex; align-items:center; justify-content:space-between;">
        <h3 style="margin:0;">Recent Sign-ups</h3>
        <a href="/website-popmart/admin/queries.php" style="visibility:hidden;">&nbsp;</a>
      </div>
      <div style="overflow:auto;">
        <table class="table" style="width:100%; border-collapse: collapse;">
          <thead>
            <tr>
              <th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Name</th>
              <th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Email</th>
              <th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Joined</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentUsers as $u): ?>
              <tr>
                <td style="padding:8px;"><?php echo htmlspecialchars(($u['first_name'] ?? '').' '.($u['last_name'] ?? '')); ?></td>
                <td style="padding:8px;"><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                <td style="padding:8px;"><?php echo htmlspecialchars($u['created_at'] ?? ''); ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($recentUsers)): ?>
              <tr><td colspan="3" style="padding:8px; color:#6b7280;">No data</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:12px;">
      <div style="display:flex; align-items:center; justify-content:space-between;">
        <h3 style="margin:0;">Customer Queries</h3>
        <a href="/website-popmart/admin/queries.php" class="btn" style="background:#111827; color:#fff; border-radius:8px; padding:6px 10px; text-decoration:none;">View Queries</a>
      </div>
      <div style="overflow:auto;">
        <table class="table" style="width:100%; border-collapse: collapse;">
          <thead>
            <tr>
              <th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">From</th>
              <th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Subject</th>
              <th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Received</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentQueries as $q): ?>
              <tr>
                <td style="padding:8px;"><?php echo htmlspecialchars(($q['name'] ?? '').' <'.($q['email'] ?? '').'>'); ?></td>
                <td style="padding:8px; max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">&nbsp;<?php echo htmlspecialchars($q['subject'] ?? ''); ?></td>
                <td style="padding:8px;">&nbsp;<?php echo htmlspecialchars($q['created_at'] ?? ''); ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($recentQueries)): ?>
              <tr><td colspan="3" style="padding:8px; color:#6b7280;">No data</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('click', async function(e) {
      const btn = e.target.closest('.btn-update');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      const next = btn.getAttribute('data-next');
      btn.disabled = true;
      try {
        const res = await fetch('/website-popmart/admin/update_order_status.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ order_id: id, next_status: next })
        });
        const json = await res.json();
        if (json.success) {
          location.reload();
        } else {
          alert('Failed: ' + (json.message || 'Unknown error'));
        }
      } catch (err) {
        alert('Network error.');
      } finally {
        btn.disabled = false;
      }
    });
  </script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
