<?php
require_once __DIR__ . '/admin_middleware.php';
require_once __DIR__ . '/../db/db_connect.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pages = max(1, (int)ceil($totalOrders / $perPage));

$sql = "
  SELECT o.id, o.user_id, o.total, o.status, o.created_at,
         CONCAT(u.first_name, ' ', u.last_name) AS customer,
         GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity) SEPARATOR ', ') AS items
  FROM orders o
  JOIN users u ON u.id = o.user_id
  JOIN order_items oi ON oi.order_id = o.id
  JOIN products p ON p.id = oi.product_id
  GROUP BY o.id
  ORDER BY o.created_at DESC
  LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

function nextStatus($current) {
  $map = [
    'Pending' => 'To Ship',
    'To Ship' => 'To Deliver',
    'To Deliver' => 'Completed'
  ];
  return $map[$current] ?? null;
}
?>
<?php $activePage = 'orders'; require_once __DIR__ . '/includes/header.php'; ?>
  <h1>Orders</h1>
  <div style="overflow:auto;">
    <table class="table" style="width:100%; border-collapse: collapse; background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Date</th>
          <th>Customer</th>
          <th>Products</th>
          <th>Total</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): $next = nextStatus($o['status']); ?>
        <tr>
          <td>#<?php echo (int)$o['id']; ?></td>
          <td><?php echo htmlspecialchars($o['created_at']); ?></td>
          <td><?php echo htmlspecialchars($o['customer']); ?></td>
          <td style="max-width:520px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo htmlspecialchars($o['items']); ?></td>
          <td>₱<?php echo number_format((float)$o['total'],2); ?></td>
          <td><?php echo htmlspecialchars($o['status']); ?></td>
          <td>
            <?php if ($next): ?>
              <button data-id="<?php echo (int)$o['id']; ?>" data-next="<?php echo htmlspecialchars($next); ?>" class="btn btn-update">Mark as <?php echo htmlspecialchars($next); ?></button>
            <?php else: ?>
              <span>-</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="pagination" style="margin-top:12px;">
    <?php for($p=1;$p<=$pages;$p++): ?>
      <?php if ($p===$page): ?>
        <span class="current"><?php echo $p; ?></span>
      <?php else: ?>
        <a href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
      <?php endif; ?>
    <?php endfor; ?>
  </div>

  <script>
  document.addEventListener('click', async function(e){
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
      if (json.success) { location.reload(); } else { alert('Failed: ' + (json.message || 'Unknown')); }
    } catch (err) { alert('Network error'); }
    finally { btn.disabled = false; }
  });
  </script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>