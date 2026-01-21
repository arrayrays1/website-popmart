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
    'To Deliver' => 'Delivered'
  ];
  return $map[$current] ?? null;
}
?>
<?php $activePage = 'orders'; require_once __DIR__ . '/includes/header.php'; ?>

<?php
$stats = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$pendingCount = $stats['Pending'] ?? 0;
$toShipCount = $stats['To Ship'] ?? 0;
$toDeliverCount = $stats['To Deliver'] ?? 0;
$deliveredCount = $stats['Delivered'] ?? 0;
$totalOrders = $pendingCount + $toShipCount + $toDeliverCount + $deliveredCount;
?>

  <div class="row mb-4">
    <div class="col-12">
      <h1 class="page-title">Orders Management</h1>
      <p class="page-subtitle">Manage and track all customer orders • Total: <?php echo $totalOrders; ?> orders</p>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-warning">
        <div class="card-body text-center">
          <h3 class="mb-0 fw-bold text-warning"><?php echo $pendingCount; ?></h3>
          <small class="text-muted">Pending</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-info">
        <div class="card-body text-center">
          <h3 class="mb-0 fw-bold text-info"><?php echo $toShipCount; ?></h3>
          <small class="text-muted">To Ship</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-primary">
        <div class="card-body text-center">
          <h3 class="mb-0 fw-bold text-primary"><?php echo $toDeliverCount; ?></h3>
          <small class="text-muted">To Deliver</small>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-success">
        <div class="card-body text-center">
          <h3 class="mb-0 fw-bold text-success"><?php echo $deliveredCount; ?></h3>
          <small class="text-muted">Delivered</small>
        </div>
      </div>
    </div>
  </div>
  
  <div class="table-container">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>Order #</th>
          <th>Date</th>
          <th>Customer</th>
          <th>Products</th>
          <th>Total</th>
          <th>Status</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): $next = nextStatus($o['status']); ?>
        <tr>
          <td>#<?php echo date('mdy', strtotime($o['created_at'])) . '-' . str_pad((int)$o['id'], 4, '0', STR_PAD_LEFT);
  ?></td>
          <td><?php echo htmlspecialchars($o['created_at']); ?></td>
          <td><?php echo htmlspecialchars($o['customer']); ?></td>
          <td style="max-width:400px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
            <small class="text-muted"><?php echo htmlspecialchars($o['items']); ?></small>
          </td>
          <td class="fw-bold text-success">₱<?php echo number_format((float)$o['total'],2); ?></td>
          <td>
            <span class="badge <?php
              switch($o['status']) {
                case 'Pending': echo 'bg-warning text-dark'; break;
                case 'To Ship': echo 'bg-info'; break;
                case 'To Deliver': echo 'bg-primary'; break;
                case 'Delivered': echo 'bg-success'; break;
                default: echo 'bg-secondary';
              }
            ?>"><?php echo htmlspecialchars($o['status']); ?></span>
          </td>
          <td class="text-center">
            <?php if ($next): ?>
              <button data-id="<?php echo (int)$o['id']; ?>" data-next="<?php echo htmlspecialchars($next); ?>" class="btn btn-sm btn-outline-primary btn-update">
                <i class="bi bi-arrow-right-circle me-1"></i><?php echo htmlspecialchars($next); ?>
              </button>
            <?php else: ?>
              <span class="badge" style="background-color: #f80000;"><i class="bi bi-check-circle"></i> Complete</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
      <?php for($p=1;$p<=$pages;$p++): ?>
        <li class="page-item <?php echo $p===$page ? 'active' : ''; ?>">
          <a class="page-link" href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>

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